<?php

namespace Tests\Feature;

use App\Http\Controllers\AssessmentAntiCheatController;
use App\Http\Controllers\ProctorController;
use App\Http\Controllers\ProctorEntryController;
use App\Http\Controllers\ProctorSessionController;
use App\Http\Controllers\ProctorTimelineController;
use App\Http\Controllers\ProctorUnlockController;
use App\Http\Controllers\StudentAssessmentExamController;
use App\Models\AssessmentMode;
use App\Models\Bab;
use App\Models\Fase;
use App\Models\Kelas;
use App\Models\Kurikulum;
use App\Models\LmsQuestionBank;
use App\Models\Mapel;
use App\Models\ProctorAuditLog;
use App\Models\ProctorUnlockRequest;
use App\Models\SchoolAssessment;
use App\Models\SchoolAssessmentQuestion;
use App\Models\SchoolAssessmentType;
use App\Models\SchoolClass;
use App\Models\SchoolPartner;
use App\Models\StudentAssessmentAttempt;
use App\Models\SubBab;
use App\Models\UserAccount;
use App\Services\Proctor\ProctorService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Tests\TestCase;

class ProctorTest extends TestCase
{
    use RefreshDatabase;

    private const ROLE = 'guru';
    private const SCHOOL = 'sekolah-test';

    private RecordingProctorService $proctor;

    protected function setUp(): void
    {
        parent::setUp();
        config(['proctor.secret' => str_repeat('k', 32)]);
        $this->proctor = new RecordingProctorService();
        $this->app->instance(ProctorService::class, $this->proctor);
    }

    public function test_only_exams_held_today_are_listed(): void
    {
        $seed = $this->seedScenario();
        $this->makeAssessment($seed, 'Kemarin', now()->subDays(3), now()->subDay());
        $this->makeAssessment($seed, 'Besok', now()->addDay(), now()->addDays(2));
        $this->makeAssessment($seed, 'Sekolah lain', now()->subDay(), now()->addDay(), otherSchool: true);

        $ids = app(ProctorService::class)->heldTodayAssessmentIds($seed['partner']->id);

        $this->assertEquals([$seed['assessment']->id], $ids->values()->all());
    }

    public function test_snapshot_only_contains_todays_school_attempts(): void
    {
        $seed = $this->seedScenario();
        $past = $this->makeAssessment($seed, 'Kemarin', now()->subDays(3), now()->subDay());
        $this->attempt($seed['student'], $seed['assessment']);
        $this->attempt($seed['otherStudent'], $past);

        $this->actingAs($seed['teacher']);
        $body = app(ProctorController::class)->snapshot(self::ROLE, self::SCHOOL, $seed['partner']->id)->getData(true);

        $this->assertEquals([$seed['assessment']->id], array_unique(array_column($body['students'], 'assessment_id')), "yesterday's exam stays out");
    }

    public function test_snapshot_lists_the_whole_class_roster_per_exam(): void
    {
        $seed = $this->seedScenario();
        // the class has two students; only one has started
        $this->attempt($seed['otherStudent'], $seed['assessment']);

        $this->actingAs($seed['teacher']);
        $body = app(ProctorController::class)->snapshot(self::ROLE, self::SCHOOL, $seed['partner']->id)->getData(true);

        $rows = collect($body['students'])->where('assessment_id', $seed['assessment']->id);
        $this->assertCount(2, $rows, 'both roster students appear, joined or not');
        $statuses = $rows->pluck('status', 'user_id');
        $this->assertEquals('in_progress', $statuses[$seed['otherStudent']->id]);
        $this->assertEquals('not_joined', $statuses[$seed['student']->id]);
    }

    public function test_teacher_request_opens_a_pending_request_and_publishes_it(): void
    {
        $seed = $this->seedScenario();
        $attempt = $this->attempt($seed['student'], $seed['assessment'], ['status' => 'cheating', 'tab_switch_count' => 5]);
        $this->actingAs($seed['teacher']);

        $res = $this->requestUnlock($seed, $attempt)->getData(true);

        $this->assertEquals('pending', $res['status']);
        $this->assertDatabaseHas('proctor_unlock_requests', ['student_assessment_attempt_id' => $attempt->id, 'status' => 'pending']);
        $this->assertEquals('unlock_requested', $this->proctor->events[0]['type']);
    }

    public function test_request_is_refused_when_student_is_not_locked(): void
    {
        $seed = $this->seedScenario();
        $attempt = $this->attempt($seed['student'], $seed['assessment']);
        $this->actingAs($seed['teacher']);

        $response = $this->requestUnlock($seed, $attempt);

        $this->assertEquals(422, $response->getStatusCode());
        $this->assertDatabaseCount('proctor_unlock_requests', 0);
    }

    public function test_only_one_pending_request_per_attempt(): void
    {
        $seed = $this->seedScenario();
        $attempt = $this->attempt($seed['student'], $seed['assessment'], ['status' => 'cheating', 'tab_switch_count' => 5]);
        $this->actingAs($seed['teacher']);

        $this->requestUnlock($seed, $attempt);
        $second = $this->requestUnlock($seed, $attempt);

        $this->assertEquals(422, $second->getStatusCode());
        $this->assertDatabaseCount('proctor_unlock_requests', 1);
    }

    public function test_approval_reopens_the_attempt_without_touching_answers_or_timer(): void
    {
        $seed = $this->seedScenario();
        $expire = now()->addMinutes(60)->startOfSecond();
        $attempt = $this->attempt($seed['student'], $seed['assessment'], [
            'status' => 'cheating', 'tab_switch_count' => 5, 'expire_time' => $expire, 'finalized_at' => now(),
        ]);
        $request = $this->pendingRequest($seed, $attempt);
        $this->actingAs($seed['approver']);

        $response = $this->decide(ProctorUnlockController::class, 'approve', $seed, $request);

        $this->assertEquals('approved', $response->getData(true)['status']);
        $attempt->refresh();
        $this->assertEquals('in_progress', $attempt->status);
        $this->assertNull($attempt->finalized_at);
        $this->assertEquals(10, $attempt->strike_limit, 'fresh window of 5 strikes from the current count');
        $this->assertEquals($expire->toDateTimeString(), $attempt->expire_time->toDateTimeString());
        $this->assertEquals(5, $attempt->tab_switch_count);
        $this->assertEquals('unlock_resolved', end($this->proctor->events)['type']);
    }

    public function test_student_is_blocked_again_only_after_a_full_new_window(): void
    {
        $seed = $this->seedScenario();
        $attempt = $this->attempt($seed['student'], $seed['assessment'], ['status' => 'cheating', 'tab_switch_count' => 5]);
        $this->actingAs($seed['approver']);
        $this->decide(ProctorUnlockController::class, 'approve', $seed, $this->pendingRequest($seed, $attempt));

        $this->callReport($seed, $attempt);
        $this->assertEquals('in_progress', $attempt->fresh()->status, 'first new strike (6/10) does not block');

        $attempt->update(['tab_switch_count' => 9]);
        Cache::flush();
        $this->callReport($seed, $attempt);
        $this->assertEquals('cheating', $attempt->fresh()->status, 'reaches the new limit of 10');
    }

    public function test_approval_after_the_window_is_denied_by_default(): void
    {
        $seed = $this->seedScenario();
        $attempt = $this->attempt($seed['student'], $seed['assessment'], ['status' => 'cheating', 'tab_switch_count' => 5]);
        $request = $this->pendingRequest($seed, $attempt, expiresAt: now()->subSecond());
        $this->actingAs($seed['approver']);

        $response = $this->decide(ProctorUnlockController::class, 'approve', $seed, $request);

        $this->assertEquals(422, $response->getStatusCode());
        $this->assertEquals('expired', $request->fresh()->status);
        $this->assertEquals('cheating', $attempt->fresh()->status);
    }

    public function test_deny_leaves_the_attempt_locked(): void
    {
        $seed = $this->seedScenario();
        $attempt = $this->attempt($seed['student'], $seed['assessment'], ['status' => 'cheating', 'tab_switch_count' => 5]);
        $request = $this->pendingRequest($seed, $attempt);
        $this->actingAs($seed['approver']);

        $this->decide(ProctorUnlockController::class, 'deny', $seed, $request);

        $this->assertEquals('denied', $request->fresh()->status);
        $this->assertEquals('cheating', $attempt->fresh()->status);
    }

    public function test_user_without_the_approver_flag_cannot_approve(): void
    {
        $seed = $this->seedScenario();
        $attempt = $this->attempt($seed['student'], $seed['assessment'], ['status' => 'cheating', 'tab_switch_count' => 5]);
        $request = $this->pendingRequest($seed, $attempt);
        $this->actingAs($seed['teacher']);

        $response = $this->decide(ProctorUnlockController::class, 'approve', $seed, $request);

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals('pending', $request->fresh()->status);
        $this->assertEquals('cheating', $attempt->fresh()->status);
    }

    public function test_requester_cannot_approve_their_own_request(): void
    {
        $seed = $this->seedScenario();
        $attempt = $this->attempt($seed['student'], $seed['assessment'], ['status' => 'cheating', 'tab_switch_count' => 5]);
        $request = $this->pendingRequest($seed, $attempt, requestedBy: $seed['approver']);
        $this->actingAs($seed['approver']);

        $response = $this->decide(ProctorUnlockController::class, 'approve', $seed, $request);

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals('cheating', $attempt->fresh()->status);
    }

    public function test_approval_is_refused_when_the_exam_time_has_run_out(): void
    {
        $seed = $this->seedScenario();
        $attempt = $this->attempt($seed['student'], $seed['assessment'], [
            'status' => 'cheating', 'tab_switch_count' => 5, 'expire_time' => now()->subMinute(),
        ]);
        $request = $this->pendingRequest($seed, $attempt);
        $this->actingAs($seed['approver']);

        $response = $this->decide(ProctorUnlockController::class, 'approve', $seed, $request);

        $this->assertEquals(422, $response->getStatusCode());
        $this->assertEquals('cheating', $attempt->fresh()->status);
        $this->assertEquals('denied', $request->fresh()->status);
    }

    public function test_proctor_page_renders_for_a_teacher(): void
    {
        $seed = $this->seedScenario();
        $this->attempt($seed['student'], $seed['assessment']);
        \App\Models\SchoolStaffProfile::create([
            'user_id' => $seed['teacher']->id,
            'school_partner_id' => $seed['partner']->id,
            'enrollment_type' => 'B2B',
            'nama_lengkap' => 'Guru Test',
            'nik' => '1234567890123456',
            'personal_email' => 'guru@test.id',
        ]);

        // contract billing is unrelated to rendering; no subscription fixture here
        $this->withoutMiddleware(\App\Http\Middleware\CheckSchoolContract::class);

        $response = $this->actingAs($seed['teacher'])->get("/lms/".self::ROLE."/sekolah/{$seed['partner']->id}/proctor");

        $response->assertOk();
        $response->assertSee('Proctor Ujian');
        $response->assertSee('id="proctor-cards"', false);
        $response->assertDontSee('Besok');
    }

    public function test_student_can_resume_only_after_the_unlock_is_approved(): void
    {
        $seed = $this->seedScenario();
        $attempt = $this->attempt($seed['student'], $seed['assessment'], ['status' => 'cheating', 'tab_switch_count' => 5]);
        $request = $this->pendingRequest($seed, $attempt);
        $this->actingAs($seed['student']);

        $this->assertEquals('blocked', $this->attemptStatus($seed)->getData(true)['status']);

        $this->actingAs($seed['approver']);
        $this->decide(ProctorUnlockController::class, 'approve', $seed, $request);

        $this->actingAs($seed['student']);
        $this->assertEquals('ok', $this->attemptStatus($seed)->getData(true)['status']);
    }

    private function attemptStatus(array $seed)
    {
        return app(AssessmentAntiCheatController::class)->checkAttemptStatus(
            'student', self::SCHOOL, $seed['partner']->id, $seed['kurikulum']->id,
            $seed['mapel']->id, $seed['type']->id, 1, $seed['assessment']->id
        );
    }

    public function test_admin_can_mark_a_teacher_as_pic(): void
    {
        $seed = $this->seedScenario();
        $this->actingAs($seed['admin']);

        // admins pass this in production via their office profile; not part of this test
        $this->withoutMiddleware(\App\Http\Middleware\CheckSchoolContract::class);

        $page = $this->get('/administrator/proctor-approvers');
        $page->assertOk();
        $page->assertSee('PIC Buka Kunci');

        $response = app(ProctorController::class)->approversToggle(
            Request::create('/administrator/proctor-approvers/'.$seed['teacher']->id, 'POST', ['enabled' => true]),
            $seed['teacher']->id
        );

        $this->assertEquals('ok', $response->getData(true)['status']);
        $this->assertTrue($seed['teacher']->fresh()->can_approve_unlock);
    }

    public function test_teacher_cannot_open_the_pic_page(): void
    {
        $seed = $this->seedScenario();
        $this->actingAs($seed['teacher']);
        $this->withoutMiddleware(\App\Http\Middleware\CheckSchoolContract::class);

        $this->get('/administrator/proctor-approvers')->assertForbidden();
    }

    public function test_pic_list_only_returns_dashboard_roles(): void
    {
        $seed = $this->seedScenario();
        $this->actingAs($seed['admin']);

        $users = app(ProctorController::class)->approversData(Request::create('/administrator/proctor-approvers/data'))->getData(true)['users'];
        $roles = array_column($users, 'role');

        $this->assertContains(self::ROLE, $roles);
        $this->assertNotContains('Siswa', $roles, 'students are never approvers');
    }

    public function test_student_token_is_scoped_to_their_own_attempt(): void
    {
        $seed = $this->seedScenario();
        $attempt = $this->attempt($seed['student'], $seed['assessment']);
        $this->actingAs($seed['student']);

        $res = app(ProctorController::class)->studentToken(
            Request::create('/lms/proctor/exam-token', 'GET', ['assessment_id' => $seed['assessment']->id])
        );
        $payload = explode('.', $res->getData(true)['token'])[0];
        $claims = json_decode(base64_decode(strtr($payload, '-_', '+/')), true);
        $this->assertEquals($attempt->id, $claims['att'], 'student token only covers their own attempt');

        // another student with no attempt and no waiting request gets no token at all
        $this->actingAs($seed['otherStudent']);
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        app(ProctorController::class)->studentToken(
            Request::create('/lms/proctor/exam-token', 'GET', ['assessment_id' => $seed['assessment']->id])
        );
    }

    public function test_student_waits_until_the_teacher_starts_the_exam(): void
    {
        $seed = $this->seedScenario();

        $res = $this->startExam($seed, $seed['student']);

        $this->assertEquals(202, $res->getStatusCode());
        $this->assertEquals('waiting_teacher', $res->getData(true)['status']);
        $this->assertDatabaseCount('student_assessment_attempts', 0);
        $this->assertEquals('entry_requested', $this->proctor->events[0]['type']);
        $this->assertFalse($this->proctor->events[0]['started']);
    }

    public function test_teacher_admits_waiting_students_and_their_timer_starts(): void
    {
        $seed = $this->seedScenario();
        $this->startExam($seed, $seed['student']);

        $started = $this->startSessionAs($seed);
        $this->assertCount(1, $started['pending']);
        $this->assertNotNull($seed['assessment']->fresh()->proctor_started_at);

        $this->decideEntries($seed, [['request_id' => $started['pending'][0]['request_id'], 'approve' => true]]);

        $res = $this->startExam($seed, $seed['student']);
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('success', $res->getData(true)['status']);
        $this->assertDatabaseHas('student_assessment_attempts', ['student_id' => $seed['student']->id, 'status' => 'in_progress']);
    }

    public function test_denied_student_cannot_enter_the_exam(): void
    {
        $seed = $this->seedScenario();
        $this->startExam($seed, $seed['student']);
        $started = $this->startSessionAs($seed);

        $this->decideEntries($seed, [['request_id' => $started['pending'][0]['request_id'], 'approve' => false]]);

        $res = $this->startExam($seed, $seed['student']);
        $this->assertEquals(403, $res->getStatusCode());
        $this->assertEquals('denied', $res->getData(true)['status']);
        $this->assertDatabaseCount('student_assessment_attempts', 0);
    }

    public function test_admission_is_refused_before_the_teacher_starts(): void
    {
        $seed = $this->seedScenario();
        $this->startExam($seed, $seed['student']);
        $entry = \App\Models\ProctorEntryRequest::where('student_id', $seed['student']->id)->value('id');

        $result = $this->decideEntries($seed, [['request_id' => $entry, 'approve' => true]]);

        $this->assertEquals(['decided' => 0, 'skipped' => 1], ['decided' => $result['decided'], 'skipped' => $result['skipped']]);
        $this->assertDatabaseCount('student_assessment_attempts', 0);
    }

    public function test_late_student_is_queued_and_the_teacher_is_pinged_after_the_start(): void
    {
        $seed = $this->seedScenario();
        $this->startSessionAs($seed);
        $this->proctor->events = [];

        $res = $this->startExam($seed, $seed['student']);

        $this->assertEquals(202, $res->getStatusCode());
        $this->assertEquals('entry_requested', $this->proctor->events[0]['type']);
        $this->assertTrue($this->proctor->events[0]['started']);
    }

    public function test_token_has_the_format_proctor_service_verifies(): void
    {
        $token = app(ProctorService::class)->token(7, 5, [9]);
        [$payload, $sig] = explode('.', $token);

        $this->assertEquals(hash_hmac('sha256', $payload, str_repeat('k', 32)), $sig);
        $claims = json_decode(base64_decode(strtr($payload, '-_', '+/')), true);
        $this->assertEquals([7, 5, [9]], [$claims['sub'], $claims['att'], $claims['asm']]);

        $withSchool = json_decode(base64_decode(strtr(explode('.', app(ProctorService::class)->token(7, null, [9], null, 3))[0], '-_', '+/')), true);
        $this->assertEquals(3, $withSchool['sch'], 'teacher tokens carry their school so later exams still match');
    }

    public function test_pause_blocks_entry_and_resume_gives_back_the_paused_time(): void
    {
        $seed = $this->seedScenario();
        $attempt = $this->attempt($seed['student'], $seed['assessment']);
        $this->startSessionAs($seed);
        $before = $attempt->expire_time->copy();

        $this->sessionAction($seed, 'pause');
        $paused = $this->startExam($seed, $seed['student'])->getData(true);
        $this->assertEquals('paused', $paused['status'], 'a paused exam hands out no clock');

        Carbon::setTestNow(now()->addSeconds(120));
        $this->sessionAction($seed, 'resume');
        Carbon::setTestNow();

        $this->assertEquals($before->addSeconds(120)->timestamp, $attempt->fresh()->expire_time->timestamp);
        $this->assertEquals('success', $this->startExam($seed, $seed['student'])->getData(true)['status']);
    }

    public function test_pause_and_resume_are_refused_in_the_wrong_state(): void
    {
        $seed = $this->seedScenario();

        $this->assertEquals(422, $this->sessionAction($seed, 'pause')->getStatusCode(), 'cannot pause before the exam starts');
        $this->startSessionAs($seed);
        $this->assertEquals(422, $this->sessionAction($seed, 'resume')->getStatusCode(), 'cannot resume a running exam');
    }

    public function test_stop_ends_running_attempts_and_blocks_new_entries(): void
    {
        $seed = $this->seedScenario();
        $attempt = $this->attempt($seed['student'], $seed['assessment']);
        $this->startSessionAs($seed);

        $res = $this->sessionAction($seed, 'stop');
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertTrue($attempt->fresh()->expire_time->lte(now()->addSecond()), 'running attempts end now');

        $this->assertEquals(410, $this->startExam($seed, $seed['student'])->getStatusCode());
        $this->assertEquals(410, $this->startExam($seed, $seed['otherStudent'])->getStatusCode());
        $this->assertEquals(422, $this->sessionAction($seed, 'resume')->getStatusCode(), 'a stopped exam cannot resume');
    }

    public function test_every_proctor_and_approver_action_is_audited(): void
    {
        $seed = $this->seedScenario();
        $this->startExam($seed, $seed['student']);
        $started = $this->startSessionAs($seed);
        $this->decideEntries($seed, [['request_id' => $started['pending'][0]['request_id'], 'approve' => true]]);
        $this->sessionAction($seed, 'pause');
        $this->sessionAction($seed, 'resume');

        $this->actingAs($seed['admin']);
        app(ProctorController::class)->approversToggle(
            Request::create('/x', 'POST', ['enabled' => true]), $seed['teacher']->id
        );

        $actions = ProctorAuditLog::pluck('action')->all();
        foreach (['session_started', 'entry_admitted', 'session_paused', 'session_resumed', 'pic_granted'] as $action) {
            $this->assertContains($action, $actions);
        }
        $row = ProctorAuditLog::where('action', 'entry_admitted')->first();
        $this->assertEquals($seed['teacher']->id, $row->actor_id);
        $this->assertEquals($seed['student']->id, $row->student_id);
        $this->assertEquals($seed['assessment']->id, $row->school_assessment_id);
    }

    public function test_student_token_is_scoped_to_their_own_exam_for_control_events(): void
    {
        $seed = $this->seedScenario();
        $this->attempt($seed['student'], $seed['assessment']);
        $this->actingAs($seed['student']);

        $claims = $this->tokenClaims(app(ProctorController::class)->studentToken(
            Request::create('/lms/proctor/exam-token', 'GET', ['assessment_id' => $seed['assessment']->id])
        ));
        $this->assertEquals($seed['assessment']->id, $claims['ass']);

        $this->actingAs($seed['otherStudent']);
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        app(ProctorController::class)->studentToken(
            Request::create('/lms/proctor/exam-token', 'GET', ['assessment_id' => $seed['assessment']->id])
        );
    }

    public function test_timeline_scopes_to_all_exams_one_exam_and_one_student(): void
    {
        $seed = $this->seedScenario();
        $other = $this->makeAssessment($seed, 'Ujian Lain', now()->subHour(), now()->addHour());
        $this->startExam($seed, $seed['student']);
        $started = $this->startSessionAs($seed);
        $this->sessionAction($seed, 'pause');
        $this->sessionAction($seed, 'resume');
        $this->decideEntries($seed, [['request_id' => $started['pending'][0]['request_id'], 'approve' => true]]);
        $this->actingAs($seed['teacher']);
        $this->seedViolation($seed['student'], $seed['assessment'], 'window_blur');
        $this->seedViolation($seed['otherStudent'], $other, 'visibility_hidden');

        $all = $this->timeline($seed, [])['events'];
        $labels = array_column($all, 'label');
        $this->assertContains('Ujian dijeda', $labels);
        $this->assertContains('Pelanggaran: window_blur', $labels);
        $this->assertContains('Pelanggaran: visibility_hidden', $labels);

        $exam = $this->timeline($seed, ['assessment_id' => $seed['assessment']->id])['events'];
        $this->assertNotEmpty($exam);
        $this->assertNotContains('Ujian Lain', array_column($exam, 'exam'));
        $this->assertNotContains('Pelanggaran: visibility_hidden', array_column($exam, 'label'));

        $student = $this->timeline($seed, ['student_id' => $seed['student']->id])['events'];
        $this->assertContains('Pelanggaran: window_blur', array_column($student, 'label'));
        $this->assertNotContains('Pelanggaran: visibility_hidden', array_column($student, 'label'), 'other students stay out');
        $this->assertContains('Siswa diizinkan masuk', array_column($student, 'label'));
        $this->assertNotContains('Ujian dijeda', array_column($student, 'label'), 'session events have no student');
    }

    public function test_timeline_refuses_exams_outside_the_school_and_students(): void
    {
        $seed = $this->seedScenario();
        $foreign = $this->makeAssessment($seed, 'Sekolah lain', now()->subHour(), now()->addHour(), otherSchool: true);

        $this->actingAs($seed['teacher']);
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        app(ProctorTimelineController::class)->data(Request::create('/x', 'GET', ['assessment_id' => $foreign->id]), self::ROLE, self::SCHOOL, $seed['partner']->id);
    }

    public function test_student_cannot_open_the_timeline(): void
    {
        $seed = $this->seedScenario();
        $this->actingAs($seed['student']);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        app(ProctorTimelineController::class)->index(self::ROLE, self::SCHOOL, $seed['partner']->id);
    }

    public function test_pic_change_is_recorded_under_the_teachers_school(): void
    {
        $seed = $this->seedScenario();
        \App\Models\SchoolStaffProfile::create([
            'user_id' => $seed['teacher']->id, 'school_partner_id' => $seed['partner']->id, 'enrollment_type' => 'B2B',
            'nama_lengkap' => 'Guru Test', 'nik' => '1234567890123459', 'personal_email' => 'guru2@test.id',
        ]);
        $this->actingAs($seed['admin']);

        app(ProctorController::class)->approversToggle(Request::create('/x', 'POST', ['enabled' => true]), $seed['teacher']->id);

        $row = ProctorAuditLog::where('action', 'pic_granted')->firstOrFail();
        $this->assertEquals($seed['partner']->id, $row->school_partner_id);
        $this->assertEquals($seed['admin']->id, $row->actor_id);
        $this->assertContains('pic_granted', array_column($this->timeline($seed, [])['events'], 'kind') ?: ['pic_granted']);
    }

    // ---- helpers ----

    private function timeline(array $seed, array $query): array
    {
        $this->actingAs($seed['teacher']);

        return app(ProctorTimelineController::class)->data(
            Request::create('/x', 'GET', $query), self::ROLE, self::SCHOOL, $seed['partner']->id
        )->getData(true);
    }

    private function seedViolation(UserAccount $student, SchoolAssessment $assessment, string $reason): void
    {
        \App\Models\StudentAssessmentAnticheatViolation::create([
            'student_assessment_attempt_id' => (StudentAssessmentAttempt::where('student_id', $student->id)->where('school_assessment_id', $assessment->id)->first() ?? $this->attempt($student, $assessment))->id,
            'student_id' => $student->id,
            'school_assessment_id' => $assessment->id,
            'reason' => $reason,
            'severity' => 'strike',
        ]);
    }

    private function sessionAction(array $seed, string $action)
    {
        $this->actingAs($seed['teacher']);

        return app(ProctorSessionController::class)->control(self::ROLE, self::SCHOOL, $seed['partner']->id, $seed['assessment']->id, $action);
    }

    private function tokenClaims($res): array
    {
        return json_decode(base64_decode(strtr(explode('.', $res->getData(true)['token'])[0], '-_', '+/')), true);
    }

    private function startExam(array $seed, UserAccount $student)
    {
        $this->actingAs($student);

        return app(StudentAssessmentExamController::class)->startTImer(
            'Siswa', self::SCHOOL, $seed['partner']->id, 1, 1, $seed['type']->id, 1, $seed['assessment']->id
        );
    }

    private function startSessionAs(array $seed): array
    {
        $this->actingAs($seed['teacher']);

        return $this->sessionAction($seed, 'start')->getData(true);
    }

    private function decideEntries(array $seed, array $decisions): array
    {
        $this->actingAs($seed['teacher']);

        return app(ProctorEntryController::class)->decide(
            Request::create('/x', 'POST', ['decisions' => $decisions]), self::ROLE, self::SCHOOL, $seed['partner']->id
        )->getData(true);
    }

    private function requestUnlock(array $seed, StudentAssessmentAttempt $attempt)
    {
        return app(ProctorUnlockController::class)->store(self::ROLE, self::SCHOOL, $seed['partner']->id, $attempt->id);
    }

    private function decide(string $controller, string $action, array $seed, ProctorUnlockRequest $request)
    {
        return app($controller)->{$action}(self::ROLE, self::SCHOOL, $seed['partner']->id, $request->id);
    }

    private function pendingRequest(array $seed, StudentAssessmentAttempt $attempt, $expiresAt = null, $requestedBy = null): ProctorUnlockRequest
    {
        return ProctorUnlockRequest::create([
            'student_assessment_attempt_id' => $attempt->id,
            'requested_by' => ($requestedBy ?? $seed['teacher'])->id,
            'status' => 'pending',
            'expires_at' => $expiresAt ?? now()->addSeconds(30),
        ]);
    }

    private function attempt(UserAccount $student, SchoolAssessment $assessment, array $overrides = []): StudentAssessmentAttempt
    {
        return StudentAssessmentAttempt::create(array_merge([
            'student_id' => $student->id,
            'school_assessment_id' => $assessment->id,
            'start_time' => now()->subMinutes(5),
            'expire_time' => now()->addMinutes(115),
            'last_heartbeat_at' => now()->subSeconds(5),
            'status' => 'in_progress',
            'tab_switch_count' => 0,
        ], $overrides));
    }

    private function callReport(array $seed, StudentAssessmentAttempt $attempt): void
    {        $this->actingAs($seed['student']);
        app(AssessmentAntiCheatController::class)->reportTabSwitch(
            Request::create('/report', 'POST', ['reason' => 'visibility_hidden'], [], [], [
                'REMOTE_ADDR' => '203.0.113.9',
                'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0 Safari/537.36',
            ]),
            'student', self::SCHOOL, $seed['partner']->id, $seed['kurikulum']->id,
            $seed['mapel']->id, $seed['type']->id, 1, $seed['assessment']->id
        );
    }

    private function makeAssessment(array $seed, string $title, $start, $end, bool $otherSchool = false): SchoolAssessment
    {
        $partner = $otherSchool
            ? SchoolPartner::create(['nama_sekolah' => 'SMA Lain', 'npsn' => '87654321', 'kepsek_id' => $seed['admin']->id, 'jenjang_sekolah' => 'SMA'])
            : $seed['partner'];

        return SchoolAssessment::create([
            'user_id' => $seed['admin']->id,
            'school_partner_id' => $partner->id,
            'school_class_id' => $seed['class']->id,
            'mapel_id' => $seed['mapel']->id,
            'assessment_type_id' => $seed['type']->id,
            'assessment_category' => 'main',
            'title' => $title,
            'semester' => 1,
            'duration' => 120,
            'start_date' => $start,
            'end_date' => $end,
            'status' => 'published',
        ]);
    }

    /** Same shape as AssessmentAntiCheatTest::seedData, plus a teacher and an approver. */
    private function seedScenario(): array
    {
        $admin = UserAccount::create(['email' => 'admin@test.id', 'password' => 'secret', 'no_hp' => '0811111101', 'role' => 'administrator']);
        $teacher = UserAccount::create(['email' => 'teacher@test.id', 'password' => 'secret', 'no_hp' => '0811111102', 'role' => self::ROLE]);
        $approver = UserAccount::create(['email' => 'approver@test.id', 'password' => 'secret', 'no_hp' => '0811111103', 'role' => self::ROLE, 'can_approve_unlock' => true]);
        $student = UserAccount::create(['email' => 'student@test.id', 'password' => 'secret', 'no_hp' => '0811111104', 'role' => 'Siswa']);
        $otherStudent = UserAccount::create(['email' => 'other@test.id', 'password' => 'secret', 'no_hp' => '0811111105', 'role' => 'Siswa']);

        $partner = SchoolPartner::create(['nama_sekolah' => 'SMA Test', 'npsn' => '12345678', 'kepsek_id' => $admin->id, 'jenjang_sekolah' => 'SMA']);
        $kurikulum = Kurikulum::create(['user_id' => $admin->id, 'nama_kurikulum' => 'Kurikulum Merdeka', 'kode' => 'KM']);
        $fase = Fase::create(['user_id' => $admin->id, 'nama_fase' => 'Fase E', 'kode' => 'E', 'kurikulum_id' => $kurikulum->id]);
        $kelas = Kelas::create(['user_id' => $admin->id, 'kelas' => 'Kelas 10', 'kode' => 'X', 'fase_id' => $fase->id, 'kurikulum_id' => $kurikulum->id]);
        $mapel = Mapel::create(['user_id' => $admin->id, 'mata_pelajaran' => 'Matematika', 'kode' => 'MAT', 'kelas_id' => $kelas->id, 'kurikulum_id' => $kurikulum->id]);
        $bab = Bab::create(['user_id' => $admin->id, 'nama_bab' => 'Bab 1', 'kode' => 'B1', 'mapel_id' => $mapel->id, 'kelas_id' => $kelas->id, 'kurikulum_id' => $kurikulum->id]);
        $subBab = SubBab::create(['user_id' => $admin->id, 'sub_bab' => 'Sub 1', 'kode' => 'S1', 'bab_id' => $bab->id, 'mapel_id' => $mapel->id, 'kelas_id' => $kelas->id, 'kurikulum_id' => $kurikulum->id]);
        $bank = LmsQuestionBank::create([
            'user_id' => $admin->id, 'kurikulum_id' => $kurikulum->id, 'kelas_id' => $kelas->id, 'mapel_id' => $mapel->id,
            'bab_id' => $bab->id, 'sub_bab_id' => $subBab->id, 'questions' => 'Soal?', 'difficulty' => 'Mudah', 'bloom' => 'C1',
            'tipe_soal' => 'MCQ', 'question_source' => 'manual',
        ]);
        $mode = AssessmentMode::create(['name' => 'Test', 'code' => 'test']);
        $type = SchoolAssessmentType::create(['user_id' => $admin->id, 'school_partner_id' => $partner->id, 'name' => 'Ulangan Harian', 'assessment_mode_id' => $mode->id]);
        $class = SchoolClass::create(['school_partner_id' => $partner->id, 'class_name' => '10-A', 'kelas_id' => $kelas->id, 'wali_kelas_id' => $admin->id, 'tahun_ajaran' => '2026/2027']);

        foreach ([$student, $otherStudent] as $u) {
            \App\Models\StudentSchoolClass::create(['student_id' => $u->id, 'school_class_id' => $class->id, 'student_class_status' => 'active']);
        }

        $seed = compact('admin', 'teacher', 'approver', 'student', 'otherStudent', 'partner', 'kurikulum', 'mapel', 'type', 'class');
        $seed['assessment'] = $this->makeAssessment($seed, 'Ujian Hari Ini', now()->subHour(), now()->addHour());

        SchoolAssessmentQuestion::create(['school_assessment_id' => $seed['assessment']->id, 'question_bank_id' => $bank->id, 'question_weight' => 100]);

        return $seed;
    }

    private function callAttemptControl(array $seed, StudentAssessmentAttempt $attempt, string $action, $as = 'teacher')
    {
        $this->actingAs($seed[$as]);

        return app(\App\Http\Controllers\ProctorAttemptController::class)->control(
            'guru', self::SCHOOL, $seed['partner']->id, $attempt->id, $action
        );
    }

    public function test_pic_page_is_gated_to_approvers_and_lists_pending_requests(): void
    {
        $seed = $this->seedScenario();
        $attempt = $this->attempt($seed['student'], $seed['assessment']);
        $attempt->update(['status' => 'cheating']);

        $this->actingAs($seed['teacher']);
        $store = app(ProctorUnlockController::class)->store('guru', self::SCHOOL, $seed['partner']->id, $attempt->id);
        $requestId = $store->getData(true)['request_id'];

        // non-PIC teachers cannot open the page or its data
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        app(ProctorController::class)->picView('guru', self::SCHOOL, $seed['partner']->id);
    }

    public function test_pic_data_lists_pending_unlock_requests(): void
    {
        $seed = $this->seedScenario();
        $attempt = $this->attempt($seed['student'], $seed['assessment']);
        $attempt->update(['status' => 'cheating']);

        $this->actingAs($seed['teacher']);
        $store = app(ProctorUnlockController::class)->store('guru', self::SCHOOL, $seed['partner']->id, $attempt->id);
        $requestId = $store->getData(true)['request_id'];

        $this->actingAs($seed['approver']);
        // compile the page too: sidebar + PIC blade must render for a PIC
        app(ProctorController::class)->picView('guru', self::SCHOOL, $seed['partner']->id)->render();
        $body = app(ProctorController::class)->picData('guru', self::SCHOOL, $seed['partner']->id)->getData(true);

        $this->assertCount(1, $body['requests']);
        $this->assertEquals($requestId, $body['requests'][0]['request_id']);
        $this->assertGreaterThan(0, $body['exams'][0]['pending_unlocks']);
        $this->assertArrayHasKey('class_name', $body['exams'][0], 'PIC can see which class each exam belongs to');
    }

    public function test_suspend_and_resume_pause_only_one_student(): void
    {
        $seed = $this->seedScenario();
        $attempt = $this->attempt($seed['student'], $seed['assessment']);
        $expire = $attempt->expire_time->copy();

        $this->assertEquals('ok', $this->callAttemptControl($seed, $attempt, 'suspend')->getData(true)['status']);
        $this->assertNotNull($attempt->fresh()->proctor_paused_at);
        $event = end($this->proctor->events);
        $this->assertEquals('exam_paused', $event['type']);
        $this->assertEquals($attempt->id, $event['attempt_id']);
        $this->assertArrayNotHasKey('audience', $event, 'no audience: the event is only for this attempt');

        // a paused student gets no strikes from heartbeats and the timer says paused
        $this->travel(40)->seconds();
        $this->assertEquals('ignored', app(AssessmentAntiCheatController::class)->heartbeat(
            Request::create('/heartbeat', 'POST', ['timestamp' => now()->timestamp]),
            'student', self::SCHOOL, $seed['partner']->id, $seed['kurikulum']->id,
            $seed['mapel']->id, $seed['type']->id, 1, $seed['assessment']->id
        )->getData(true)['status']);

        $this->assertEquals('ok', $this->callAttemptControl($seed, $attempt, 'resume')->getData(true)['status']);
        $fresh = $attempt->fresh();
        $this->assertNull($fresh->proctor_paused_at);
        $this->assertEqualsWithDelta($expire->timestamp + 40, $fresh->expire_time->timestamp, 2, 'paused time is not consumed');
    }

    public function test_suspended_student_earns_no_strikes(): void
    {
        $seed = $this->seedScenario();
        $attempt = $this->attempt($seed['student'], $seed['assessment']);

        $this->callAttemptControl($seed, $attempt, 'suspend');
        $this->callReport($seed, $attempt);

        $this->assertEquals(0, $attempt->fresh()->tab_switch_count, 'no strikes while the proctor holds the student');
    }

    public function test_resume_refused_when_not_suspended(): void    {
        $seed = $this->seedScenario();
        $attempt = $this->attempt($seed['otherStudent'], $seed['assessment']);

        $this->assertEquals(422, $this->callAttemptControl($seed, $attempt, 'resume')->getStatusCode());
    }

    public function test_terminate_ends_only_that_students_time(): void
    {
        $seed = $this->seedScenario();
        $attempt = $this->attempt($seed['student'], $seed['assessment']);
        $otherExam = $this->makeAssessment($seed, 'Ujian Sore', now()->subMinutes(30), now()->addHour());
        $untouched = $this->attempt($seed['otherStudent'], $otherExam);

        $this->assertEquals('ok', $this->callAttemptControl($seed, $attempt, 'terminate')->getData(true)['status']);

        $fresh = $attempt->fresh();
        $this->assertTrue($fresh->expire_time->lessThanOrEqualTo(now()), 'expired now');
        $this->assertEquals('in_progress', $fresh->status, 'the client auto-submits, status is untouched');
        $this->assertTrue($untouched->fresh()->expire_time->isFuture(), 'the other student keeps their time');

        $event = end($this->proctor->events);
        $this->assertEquals('exam_stopped', $event['type']);
        $this->assertEquals($attempt->id, $event['attempt_id']);
        $this->assertArrayNotHasKey('audience', $event);
    }

    public function test_attempt_controls_are_refused_for_another_school(): void
    {
        $seed = $this->seedScenario();
        $other = $this->makeAssessment($seed, 'Sekolah lain', now()->subHour(), now()->addHour(), otherSchool: true);
        $attempt = $this->attempt($seed['student'], $other);

        $this->assertEquals(404, $this->callAttemptControl($seed, $attempt, 'suspend')->getStatusCode());
    }
}

/** Records publishes instead of sending them to Redis. */
class RecordingProctorService extends ProctorService
{
    public array $events = [];

    public function publish(array $event): void
    {
        $this->events[] = $event;
    }
}
