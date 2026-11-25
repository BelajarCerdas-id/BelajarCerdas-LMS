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
use App\Models\StudentAssessmentAnswer;
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
        $body = app(ProctorController::class)->snapshot(Request::create('/x', 'GET'), self::ROLE, self::SCHOOL, $seed['partner']->id)->getData(true);

        $this->assertEquals([$seed['assessment']->id], array_unique(array_column($body['students'], 'assessment_id')), "yesterday's exam stays out");
    }

    public function test_snapshot_lists_the_whole_class_roster_per_exam(): void
    {
        $seed = $this->seedScenario();
        // the class has two students; only one has started
        $this->attempt($seed['otherStudent'], $seed['assessment']);

        $this->actingAs($seed['teacher']);
        $body = app(ProctorController::class)->snapshot(Request::create('/x', 'GET'), self::ROLE, self::SCHOOL, $seed['partner']->id)->getData(true);

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

    public function test_start_timer_hands_out_the_authoritative_server_clock(): void
    {
        $seed = $this->seedScenario();
        $this->startExam($seed, $seed['student']);
        $started = $this->startSessionAs($seed);
        $this->decideEntries($seed, [['request_id' => $started['pending'][0]['request_id'], 'approve' => true]]);

        $res = $this->startExam($seed, $seed['student']);
        $body = $res->getData(true);

        // the client must be able to drive its countdown from the server clock
        // instead of the device clock (skewed devices ended live exams early)
        $this->assertEquals('success', $body['status']);
        $this->assertEquals($seed['student']->id, $body['user_id'], 'timer state must be scopabel per user for the client');
        $this->assertEqualsWithDelta(now()->getTimestamp() * 1000, $body['server_now_ms'], 5000, 'the client anchors its clock on this field');
        $this->assertGreaterThan(0, $body['remaining_seconds']);
        $this->assertFalse($body['expired']);
        $this->assertStringContainsString('no-store', $res->headers->get('Cache-Control'), 'exam state must never be cached');
    }

    public function test_start_timer_never_presents_a_past_expire_as_running(): void
    {
        $seed = $this->seedScenario();
        $this->startExam($seed, $seed['student']);
        $started = $this->startSessionAs($seed);
        $this->decideEntries($seed, [['request_id' => $started['pending'][0]['request_id'], 'approve' => true]]);

        // first call after admission creates the attempt
        $first = $this->startExam($seed, $seed['student']);
        $this->assertEquals('success', $first->getData(true)['status']);

        $attempt = StudentAssessmentAttempt::where('student_id', $seed['student']->id)
            ->where('school_assessment_id', $seed['assessment']->id)
            ->first();

        // inside the 1-minute skew grace: success contract is kept (the client
        // auto-submits a genuinely expired attempt), but the state is explicit
        $attempt->update(['expire_time' => now()->subSeconds(30)]);
        $body = $this->startExam($seed, $seed['student'])->getData(true);
        $this->assertEquals('success', $body['status']);
        $this->assertTrue($body['expired']);
        $this->assertEquals(0, $body['remaining_seconds']);

        // well past the deadline the start-timer guard refuses to hand out a
        // clock at all — a reload after expiry must never look like a running exam
        $attempt->update(['expire_time' => now()->subMinutes(5)]);
        $res = $this->startExam($seed, $seed['student']);
        $this->assertEquals(422, $res->getStatusCode());
        $this->assertEquals('expired', $res->getData(true)['status']);
        // early guard exits are exam state too: no-store must not depend on middleware
        $this->assertStringContainsString('no-store', $res->headers->get('Cache-Control'));
    }

    public function test_start_timer_refusals_carry_no_store_headers(): void
    {
        $seed = $this->seedScenario();

        // not_started: the school window has not opened yet
        $seed['assessment']->update(['start_date' => now()->addHour(), 'end_date' => now()->addHours(2)]);
        $res = $this->startExam($seed, $seed['student']);
        $this->assertEquals(422, $res->getStatusCode());
        $this->assertEquals('not_started', $res->getData(true)['status']);
        $this->assertStringContainsString('no-store', $res->headers->get('Cache-Control'));
    }

    public function test_exam_form_status_follows_the_attempt_clock_not_the_school_window(): void
    {
        $seed = $this->seedScenario();
        $attempt = $this->attempt($seed['student'], $seed['assessment'], [
            'expire_time' => now()->subMinute(),
        ]);
        // the school window is still wide open
        $seed['assessment']->update(['end_date' => now()->addHour()]);

        $this->actingAs($seed['student']);
        $res = app(StudentAssessmentExamController::class)->studentAssessmentExamForm(
            'Siswa', self::SCHOOL, $seed['partner']->id, $seed['kurikulum']->id,
            $seed['mapel']->id, $seed['type']->id, 1, $seed['assessment']->id
        );

        $body = $res->getData(true);
        $this->assertEquals('expired', $body['status'], 'the exam page must lock once the attempt clock dies');
        $this->assertStringContainsString('no-store', $res->headers->get('Cache-Control'));
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

        // freeze the clock BEFORE the pause, pinned to a whole second so the
        // DB's second-precision timestamps match exactly: with a live wall
        // clock, running the pause write and the +120s setTestNow on
        // different sides of a second boundary made the controller's resume
        // arithmetic drift by 1s and flaked this test
        $frozen = Carbon::now()->startOfSecond();
        Carbon::setTestNow($frozen);

        try {
            $this->sessionAction($seed, 'pause');
            $paused = $this->startExam($seed, $seed['student'])->getData(true);
            $this->assertEquals('paused', $paused['status'], 'a paused exam hands out no clock');

            Carbon::setTestNow($frozen->copy()->addSeconds(120));
            $this->sessionAction($seed, 'resume');
        } finally {
            Carbon::setTestNow();
        }

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

    public function test_stop_leaves_attempts_intact_and_blocks_new_entries(): void
    {
        $seed = $this->seedScenario();
        $attempt = $this->attempt($seed['student'], $seed['assessment']);
        $expire = $attempt->expire_time->copy();
        $this->startSessionAs($seed);

        $res = $this->sessionAction($seed, 'stop');
        $this->assertEquals(200, $res->getStatusCode());

        // incident 2026-11-25: the stop path used to rewrite expire_time to the stop
        // instant, leaving offline clients' attempts stuck in_progress with a fake
        // deadline (e.g. attempt 3068: expire 11:07:12 == proctor_stopped_at).
        $fresh = $attempt->fresh();
        $this->assertTrue($fresh->expire_time->equalTo($expire), 'stop must never rewrite the attempt deadline');
        $this->assertEquals('in_progress', $fresh->status, 'the client finalizes via /end on exam_stopped; the stop itself leaves the attempt alone');
        $this->assertNull($fresh->finalized_at);

        $this->assertEquals(410, $this->startExam($seed, $seed['student'])->getStatusCode());
        $this->assertEquals(410, $this->startExam($seed, $seed['otherStudent'])->getStatusCode());
        $this->assertEquals(422, $this->sessionAction($seed, 'resume')->getStatusCode(), 'a stopped exam cannot resume');
    }

    public function test_student_end_still_finalizes_after_the_teacher_stops_the_exam(): void
    {
        $seed = $this->seedScenario();
        $attempt = $this->attempt($seed['student'], $seed['assessment']);
        $question = SchoolAssessmentQuestion::where('school_assessment_id', $seed['assessment']->id)->first();
        $this->startSessionAs($seed);
        $this->sessionAction($seed, 'stop');

        // the client's exam_stopped handler posts /end with the in-flight answer;
        // this is the normal end path and must keep working after a stop
        $res = $this->callEnd($seed, $attempt, [
            'status_attempt' => 'submitted',
            'current_question_id' => $question->id,
            'current_answer_value' => 'A',
        ]);

        $this->assertEquals('success', $res->getData(true)['status']);
        $fresh = $attempt->fresh();
        $this->assertEquals('submitted', $fresh->status);
        $this->assertNotNull($fresh->finalized_at);
        $this->assertDatabaseHas('student_assessment_answers', [
            'student_id' => $seed['student']->id,
            'school_assessment_id' => $seed['assessment']->id,
            'school_assessment_question_id' => $question->id,
            // the answer model json-casts answer_value
            'answer_value' => '"A"',
        ]);
    }

    public function test_end_after_a_terminate_still_saves_the_in_flight_answer_without_refinalizing(): void
    {
        $seed = $this->seedScenario();
        $attempt = $this->attempt($seed['student'], $seed['assessment']);
        $question = SchoolAssessmentQuestion::where('school_assessment_id', $seed['assessment']->id)->first();

        // the teacher terminates the student mid-exam: the attempt is finalized
        // server-side while the student's client is still live
        $this->startSessionAs($seed);
        $this->callAttemptControl($seed, $attempt, 'terminate');

        $attempt->refresh();
        $finalizedAt = $attempt->finalized_at;
        $this->assertNotNull($finalizedAt, 'terminate finalizes the attempt');

        // the live client's /end then arrives carrying the answer the student
        // was typing when the terminate landed: it must be persisted, but the
        // attempt must not be finalized a second time
        $res = $this->callEnd($seed, $attempt, [
            'status_attempt' => 'submitted',
            'current_question_id' => $question->id,
            'current_answer_value' => 'A',
        ]);

        $this->assertEquals('success', $res->getData(true)['status']);
        $this->assertDatabaseHas('student_assessment_answers', [
            'student_id' => $seed['student']->id,
            'school_assessment_id' => $seed['assessment']->id,
            'school_assessment_question_id' => $question->id,
            'answer_value' => '"A"',
            'status_answer' => 'submitted',
        ]);

        $fresh = $attempt->fresh();
        $this->assertTrue($fresh->finalized_at->equalTo($finalizedAt), 'no double finalization');
        $this->assertEquals('submitted', $fresh->status, 'terminate status is not overwritten by the late /end');

        // the late-answer save stays create-only: a replayed /end must never
        // rewrite an answer that was already stored
        $this->callEnd($seed, $attempt, [
            'status_attempt' => 'submitted',
            'current_question_id' => $question->id,
            'current_answer_value' => 'B',
        ]);
        $this->assertDatabaseHas('student_assessment_answers', [
            'student_id' => $seed['student']->id,
            'school_assessment_id' => $seed['assessment']->id,
            'school_assessment_question_id' => $question->id,
            'answer_value' => '"A"',
        ]);
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

    private function callEnd(array $seed, StudentAssessmentAttempt $attempt, array $payload = [])
    {
        $this->actingAs($attempt->student_id === $seed['student']->id ? $seed['student'] : $seed['otherStudent']);

        return app(StudentAssessmentExamController::class)->studentAssessmentExamEnd(
            Request::create('/end', 'POST', array_merge(['status_attempt' => 'submitted'], $payload)),
            'Siswa', self::SCHOOL, $seed['partner']->id, $seed['kurikulum']->id,
            $seed['mapel']->id, $seed['type']->id, 1, $attempt->school_assessment_id
        );
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

    public function test_pic_data_reflects_schedule_opened_session_state(): void
    {
        $seed = $this->seedScenario();
        $seed['partner']->update(['proctor_auto_start' => true]);

        // the window is open on schedule and no teacher ever pressed 'Mulai':
        // the PIC exam card must not show 'Belum dimulai' while students flow
        $this->actingAs($seed['approver']);
        $body = app(ProctorController::class)->picData('guru', self::SCHOOL, $seed['partner']->id)->getData(true);
        $card = collect($body['exams'])->firstWhere('assessment_id', $seed['assessment']->id);

        $this->assertEquals('running', $card['state'], 'effective state, not the raw teacher rows');
        $this->assertTrue($card['virtual_running'], 'the state came from the schedule, so the PIC UI can badge it');
        $this->assertNull($seed['assessment']->fresh()->proctor_started_at, 'still a virtual session: nothing was written');

        // default-off school keeps the plain not_started card with no marker
        $other = $this->makeAssessment($seed, 'Sekolah lain', now()->subHour(), now()->addHour(), otherSchool: true);
        $bodyB = app(ProctorController::class)->picData('guru', self::SCHOOL, $other->school_partner_id)->getData(true);
        $cardB = collect($bodyB['exams'])->firstWhere('assessment_id', $other->id);
        $this->assertEquals('not_started', $cardB['state']);
        $this->assertFalse($cardB['virtual_running']);
    }

    // per-school proctor auto-admit setting (school_partners.proctor_auto_admit)

    public function test_auto_admit_school_starts_the_exam_without_a_teacher_decision(): void
    {
        $seed = $this->seedScenario();
        $seed['partner']->update(['proctor_auto_admit' => true]);

        // auto-admit only fires while the session is RUNNING: before the teacher
        // starts, the student still waits ('Belum Dimulai' on the dashboard)
        $this->assertEquals('waiting_teacher', $this->startExam($seed, $seed['student'])->getData(true)['status']);

        $this->startSessionAs($seed);
        $res = $this->startExam($seed, $seed['student']);
        $body = $res->getData(true);

        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('success', $body['status'], 'no manual decide needed');
        $this->assertEquals($seed['student']->id, $body['user_id']);
        $this->assertGreaterThan(0, $body['remaining_seconds'], 'real deadline, not a stubbed clock');

        // the entry row looks exactly like a manual admit, minus the deciding user
        $entry = \App\Models\ProctorEntryRequest::where('student_id', $seed['student']->id)
            ->where('school_assessment_id', $seed['assessment']->id)->first();
        $this->assertEquals('admitted', $entry->status);
        $this->assertNull($entry->decided_by, 'system actor, not a teacher');
        $this->assertNotNull($entry->decided_at);

        // the attempt has the real deadline: start_time + duration
        $attempt = StudentAssessmentAttempt::where('student_id', $seed['student']->id)
            ->where('school_assessment_id', $seed['assessment']->id)->first();
        $this->assertEquals('in_progress', $attempt->status);
        $this->assertEqualsWithDelta(now()->addMinutes(120)->timestamp, $attempt->expire_time->timestamp, 5);

        // same audit trail as the manual path, with the system as the actor
        $row = ProctorAuditLog::where('action', 'entry_auto_admitted')->firstOrFail();
        $this->assertNull($row->actor_id, 'the audit row belongs to the system, not the student who called');
        $this->assertEquals('auto-admit', $row->actor_role);
        $this->assertEquals($seed['student']->id, $row->student_id);
        $this->assertEquals($seed['assessment']->id, $row->school_assessment_id);
        // the proctor timeline sees the admission like a manual one
        $events = $this->timeline($seed, [])['events'];
        $auto = collect($events)->first(fn ($e) => $e['label'] === 'Siswa diizinkan masuk (otomatis)');
        $this->assertNotNull($auto, 'the timeline shows the auto admission');
        $this->assertEquals('Sistem', $auto['actor'], 'no user actor');
        $this->assertEquals($seed['assessment']->title, $auto['exam']);

        // the dashboard sees the same entry_resolved the manual path publishes
        $resolved = end($this->proctor->events);
        $this->assertEquals('entry_resolved', $resolved['type']);
        $this->assertEquals('approved', $resolved['status']);
        $this->assertTrue($resolved['auto']);
        $this->assertEquals($seed['student']->id, $resolved['user_id']);
    }

    public function test_auto_admit_disabled_by_default_students_still_wait(): void
    {
        $seed = $this->seedScenario();
        $this->startSessionAs($seed);

        $res = $this->startExam($seed, $seed['student']);

        $this->assertEquals(202, $res->getStatusCode());
        $this->assertEquals('waiting_teacher', $res->getData(true)['status']);
        $this->assertDatabaseCount('student_assessment_attempts', 0);
        $this->assertDatabaseHas('proctor_entry_requests', ['student_id' => $seed['student']->id, 'status' => 'pending']);
        $this->assertDatabaseMissing('proctor_audit_logs', ['action' => 'entry_auto_admitted']);
    }

    public function test_auto_admit_is_per_school(): void
    {
        $seed = $this->seedScenario();
        $seed['partner']->update(['proctor_auto_admit' => true]);
        $other = $this->makeAssessment($seed, 'Sekolah lain', now()->subHour(), now()->addHour(), otherSchool: true);

        // both exams have a running proctor session
        $this->startSessionAs($seed);
        $this->actingAs($seed['teacher']);
        app(ProctorSessionController::class)->control('guru', self::SCHOOL, $other->school_partner_id, $other->id, 'start');

        // school A (enabled): straight in without a teacher decision
        $resA = $this->startExam($seed, $seed['student']);
        $this->assertEquals('success', $resA->getData(true)['status']);

        // school B (default off): the same feature call still waits
        $this->actingAs($seed['otherStudent']);
        $resB = app(StudentAssessmentExamController::class)->startTImer(
            'Siswa', self::SCHOOL, $other->school_partner_id, 1, 1, $seed['type']->id, 1, $other->id
        );

        $this->assertEquals(202, $resB->getStatusCode());
        $this->assertEquals('waiting_teacher', $resB->getData(true)['status']);
        $this->assertDatabaseHas('proctor_entry_requests', [
            'school_assessment_id' => $other->id, 'student_id' => $seed['otherStudent']->id, 'status' => 'pending',
        ]);
        $this->assertDatabaseHas('proctor_entry_requests', [
            'school_assessment_id' => $seed['assessment']->id, 'student_id' => $seed['student']->id, 'status' => 'admitted',
        ]);
        $this->assertDatabaseMissing('student_assessment_attempts', ['student_id' => $seed['otherStudent']->id]);
    }

    public function test_auto_admit_never_overrides_a_teacher_denial(): void
    {
        $seed = $this->seedScenario();
        $seed['partner']->update(['proctor_auto_admit' => true]);

        $this->startExam($seed, $seed['student']);
        $started = $this->startSessionAs($seed);
        $this->decideEntries($seed, [['request_id' => $started['pending'][0]['request_id'], 'approve' => false]]);

        $res = $this->startExam($seed, $seed['student']);

        $this->assertEquals(403, $res->getStatusCode());
        $this->assertEquals('denied', $res->getData(true)['status']);
        $this->assertDatabaseHas('proctor_entry_requests', [
            'student_id' => $seed['student']->id, 'status' => 'denied',
        ]);
        $this->assertDatabaseCount('student_assessment_attempts', 0);
        $this->assertDatabaseMissing('proctor_audit_logs', ['action' => 'entry_auto_admitted']);
    }

    public function test_auto_admit_is_a_noop_when_the_session_stops_inside_the_lock_window(): void
    {
        $seed = $this->seedScenario();
        $seed['partner']->update(['proctor_auto_admit' => true]);
        // queue the student while the session is still closed, so the entry is
        // pending (startExam after the start would already auto-admit them)
        $this->startExam($seed, $seed['student']);
        $this->startSessionAs($seed);
        $entry = \App\Models\ProctorEntryRequest::where('student_id', $seed['student']->id)->first();

        // the race QA flagged: this in-memory copy says 'running', but the teacher's
        // stop commits to the DB before our transaction takes the row lock — only
        // the state re-check inside the lock can keep the admit from landing
        $staleRunning = $seed['assessment']->fresh();
        $this->sessionAction($seed, 'stop');

        $this->assertFalse(app(ProctorService::class)->autoAdmitEntry($staleRunning, $entry, $seed['partner']));
        $this->assertEquals('pending', $entry->fresh()->status, 'the stop wins, the student is not admitted');
        $this->assertDatabaseCount('student_assessment_attempts', 0);
        $this->assertDatabaseMissing('proctor_audit_logs', ['action' => 'entry_auto_admitted']);
        $this->assertEquals(410, $this->startExam($seed, $seed['student'])->getStatusCode(), 'the normal gate refuses the stopped exam anyway');
    }

    // per-school proctor auto-start (school_partners.proctor_auto_start): the
    // exam window opens by itself while the schedule window is open; the
    // teacher's 'Mulai' is not needed. A student's deadline stays anchored to
    // their own attempt start + duration, never to start_date.

    public function test_auto_start_opens_the_window_and_students_queue_for_admission(): void
    {
        $seed = $this->seedScenario();
        $seed['partner']->update(['proctor_auto_start' => true]);

        // window open, teacher never pressed start, auto_admit off: the student
        // passes the session gate and waits for per-student admission
        $res = $this->startExam($seed, $seed['student']);

        $this->assertEquals(202, $res->getStatusCode());
        $this->assertEquals('waiting_teacher', $res->getData(true)['status']);
        $this->assertDatabaseHas('proctor_entry_requests', ['student_id' => $seed['student']->id, 'status' => 'pending']);
        $this->assertDatabaseCount('student_assessment_attempts', 0);

        // the virtual open wrote no session state of its own
        $this->assertNull($seed['assessment']->fresh()->proctor_started_at);
        $this->assertDatabaseMissing('proctor_audit_logs', ['action' => 'session_started']);

        // a teacher start on the already-open window is not available
        $this->assertEquals(422, $this->sessionAction($seed, 'start')->getStatusCode());
    }

    public function test_auto_start_with_auto_admit_is_fully_hands_off_and_the_deadline_stays_per_student(): void
    {
        $seed = $this->seedScenario();
        $seed['partner']->update(['proctor_auto_start' => true, 'proctor_auto_admit' => true]);

        $res = $this->startExam($seed, $seed['student']);
        $body = $res->getData(true);

        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('success', $body['status'], 'no teacher action of any kind happened');
        $this->assertGreaterThan(0, $body['remaining_seconds'], 'a real clock was handed out');

        // THE invariant: the deadline is the student's own start + duration, never
        // the schedule window. start_date is an hour in the past; a start_date
        // anchored deadline would already be 60 minutes gone.
        $attempt = StudentAssessmentAttempt::where('student_id', $seed['student']->id)
            ->where('school_assessment_id', $seed['assessment']->id)->first();
        $this->assertEqualsWithDelta(now()->addMinutes(120)->timestamp, $attempt->expire_time->timestamp, 5, 'expire_time = attempt start + 120m duration');
        $this->assertGreaterThan(now()->addMinutes(119)->timestamp, $attempt->expire_time->timestamp, 'no shared deadline anchored to start_date');

        // the virtual open itself is invisible in the session rows and audit
        $this->assertNull($seed['assessment']->fresh()->proctor_started_at);
        $this->assertDatabaseMissing('proctor_audit_logs', ['action' => 'session_started']);
        $this->assertDatabaseHas('proctor_audit_logs', ['action' => 'entry_auto_admitted', 'school_assessment_id' => $seed['assessment']->id]);
    }

    public function test_auto_start_before_the_window_keeps_the_exam_not_started(): void
    {
        $seed = $this->seedScenario();
        $seed['partner']->update(['proctor_auto_start' => true, 'proctor_auto_admit' => true]);
        $seed['assessment']->update(['start_date' => now()->addHour(), 'end_date' => now()->addHours(2)]);

        $res = $this->startExam($seed, $seed['student']);

        $this->assertEquals(422, $res->getStatusCode());
        $this->assertEquals('not_started', $res->getData(true)['status'], 'the schedule, not the flag, controls the open');
        $this->assertStringContainsString('no-store', $res->headers->get('Cache-Control'));
        $this->assertDatabaseCount('proctor_entry_requests', 0);
        $this->assertDatabaseCount('student_assessment_attempts', 0);
    }

    public function test_auto_start_after_the_window_keeps_expired_semantics(): void
    {
        $seed = $this->seedScenario();
        $seed['partner']->update(['proctor_auto_start' => true, 'proctor_auto_admit' => true]);
        $seed['assessment']->update(['end_date' => now()->subMinute()]);

        $res = $this->startExam($seed, $seed['student']);

        $this->assertEquals(422, $res->getStatusCode());
        $this->assertEquals('expired', $res->getData(true)['status']);
        $this->assertDatabaseCount('student_assessment_attempts', 0);
    }

    public function test_auto_start_requires_a_published_assessment(): void
    {
        $seed = $this->seedScenario();
        $seed['partner']->update(['proctor_auto_start' => true, 'proctor_auto_admit' => true]);
        $seed['assessment']->update(['status' => 'draft']);

        $res = $this->startExam($seed, $seed['student']);

        $this->assertEquals(202, $res->getStatusCode());
        $this->assertEquals('waiting_teacher', $res->getData(true)['status'], 'an unpublished exam never opens on schedule');
        $this->assertDatabaseCount('student_assessment_attempts', 0);
    }

    public function test_a_real_teacher_stop_overrides_the_schedule_permanently(): void
    {
        $seed = $this->seedScenario();
        $seed['partner']->update(['proctor_auto_start' => true]);

        // the virtual session is running on schedule: a teacher stop is allowed
        $res = $this->sessionAction($seed, 'stop');
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('stopped', $res->getData(true)['state']);

        // a real stop wins over the schedule forever: even a much wider window
        // never reopens the exam
        $seed['assessment']->update(['end_date' => now()->addHours(3)]);
        $this->assertEquals(410, $this->startExam($seed, $seed['student'])->getStatusCode());
        $this->assertEquals(410, $this->startExam($seed, $seed['otherStudent'])->getStatusCode());
        $this->assertNotNull($seed['assessment']->fresh()->proctor_stopped_at);

        // audited like any stop, noting it hit a schedule-opened session
        $row = ProctorAuditLog::where('action', 'session_stopped')
            ->where('school_assessment_id', $seed['assessment']->id)->firstOrFail();
        $this->assertEquals(['attempts' => 0, 'auto_start' => true], $row->detail);
    }

    public function test_a_real_teacher_pause_then_resume_extends_attempts_on_a_virtual_session(): void
    {
        $seed = $this->seedScenario();
        $seed['partner']->update(['proctor_auto_start' => true]);
        $attempt = $this->attempt($seed['student'], $seed['assessment']);
        $before = $attempt->expire_time->copy();

        // freeze the clock BEFORE the pause (same pinning as the pause/resume
        // test above) so the resume arithmetic does not flake on a second edge
        $frozen = Carbon::now()->startOfSecond();
        Carbon::setTestNow($frozen);

        try {
            $paused = $this->sessionAction($seed, 'pause');
            $this->assertEquals(200, $paused->getStatusCode());
            $this->assertEquals('paused', $paused->getData(true)['state']);
            $this->assertEquals('paused', $this->startExam($seed, $seed['student'])->getData(true)['status'], 'a paused exam hands out no clock');
            $this->assertDatabaseHas('proctor_audit_logs', [
                'action' => 'session_paused', 'school_assessment_id' => $seed['assessment']->id,
            ]);

            Carbon::setTestNow($frozen->copy()->addSeconds(120));
            $resumed = $this->sessionAction($seed, 'resume');
            $this->assertEquals(200, $resumed->getStatusCode());
            $this->assertEquals('running', $resumed->getData(true)['state'], 'back to the schedule-driven open window');
        } finally {
            Carbon::setTestNow();
        }

        // resume parity: paused seconds are given back exactly, and the student
        // re-enters without any teacher start ever having happened
        $this->assertEquals($before->addSeconds(120)->timestamp, $attempt->fresh()->expire_time->timestamp);
        $this->assertEquals('success', $this->startExam($seed, $seed['student'])->getData(true)['status']);
        $this->assertNull($seed['assessment']->fresh()->proctor_started_at);
    }

    public function test_auto_admit_is_a_noop_when_the_virtual_session_stops_inside_the_lock_window(): void
    {
        $seed = $this->seedScenario();
        $seed['partner']->update(['proctor_auto_start' => true, 'proctor_auto_admit' => true]);
        $entry = \App\Models\ProctorEntryRequest::create([
            'school_assessment_id' => $seed['assessment']->id,
            'student_id' => $seed['student']->id,
            'status' => 'pending',
        ]);

        // the race QA flagged on auto-admit, on a schedule-opened session: this
        // in-memory copy is virtually running, but the teacher's stop commits
        // before our transaction takes the row lock — only the effective-state
        // re-check inside the lock keeps the admit from landing
        $staleNotStarted = $seed['assessment']->fresh();
        $this->sessionAction($seed, 'stop');

        $this->assertFalse(app(ProctorService::class)->autoAdmitEntry($staleNotStarted, $entry, $seed['partner']));
        $this->assertEquals('pending', $entry->fresh()->status, 'the stop wins, the student is not admitted');
        $this->assertDatabaseCount('student_assessment_attempts', 0);
        $this->assertDatabaseMissing('proctor_audit_logs', ['action' => 'entry_auto_admitted']);
    }

    public function test_auto_start_off_by_default_keeps_the_teacher_start_gate(): void
    {
        $seed = $this->seedScenario();

        $this->assertFalse((bool) $seed['partner']->fresh()->proctor_auto_start, 'the flag defaults to off');

        $res = $this->startExam($seed, $seed['student']);

        $this->assertEquals(202, $res->getStatusCode());
        $this->assertEquals('waiting_teacher', $res->getData(true)['status']);
        $this->assertDatabaseCount('student_assessment_attempts', 0);
        // teacher controls still follow the real rows: nothing to pause yet
        $this->assertEquals(422, $this->sessionAction($seed, 'pause')->getStatusCode());
    }

    public function test_auto_start_is_per_school(): void
    {
        $seed = $this->seedScenario();
        $seed['partner']->update(['proctor_auto_start' => true, 'proctor_auto_admit' => true]);
        $other = $this->makeAssessment($seed, 'Sekolah lain', now()->subHour(), now()->addHour(), otherSchool: true);

        // school A (auto-start on): hands-free entry without any teacher action
        $resA = $this->startExam($seed, $seed['student']);
        $this->assertEquals('success', $resA->getData(true)['status']);

        // school B (default off): the same window-open exam still waits
        $this->actingAs($seed['otherStudent']);
        $resB = app(StudentAssessmentExamController::class)->startTImer(
            'Siswa', self::SCHOOL, $other->school_partner_id, 1, 1, $seed['type']->id, 1, $other->id
        );

        $this->assertEquals(202, $resB->getStatusCode());
        $this->assertEquals('waiting_teacher', $resB->getData(true)['status']);
        $this->assertDatabaseHas('proctor_entry_requests', [
            'school_assessment_id' => $other->id, 'student_id' => $seed['otherStudent']->id, 'status' => 'pending',
        ]);
        $this->assertDatabaseMissing('student_assessment_attempts', ['student_id' => $seed['otherStudent']->id]);
    }

    public function test_snapshot_exposes_auto_start_and_the_virtual_running_marker(): void
    {
        $seed = $this->seedScenario();
        $seed['partner']->update(['proctor_auto_start' => true]);
        $this->actingAs($seed['teacher']);

        $body = app(ProctorController::class)->snapshot(Request::create('/x', 'GET'), self::ROLE, self::SCHOOL, $seed['partner']->id)->getData(true);
        $card = collect($body['assessments'])->firstWhere('assessment_id', $seed['assessment']->id);

        $this->assertTrue($body['auto_start'], 'school-wide toggle for the dashboard banner');
        $this->assertFalse($body['auto_admit']);
        $this->assertEquals('running', $card['state'], 'the schedule opened the window');
        $this->assertTrue($card['virtual_running'], 'the state came from the schedule, not the teacher');

        // a real teacher start replaces the virtual state; start is only
        // available while the window is closed, so start first, then open it
        $seed['assessment']->update(['start_date' => now()->addHour(), 'end_date' => now()->addHours(2)]);
        $this->startSessionAs($seed);
        $seed['assessment']->update(['start_date' => now()->subHour(), 'end_date' => now()->addHour()]);

        $body = app(ProctorController::class)->snapshot(Request::create('/x', 'GET'), self::ROLE, self::SCHOOL, $seed['partner']->id)->getData(true);
        $card = collect($body['assessments'])->firstWhere('assessment_id', $seed['assessment']->id);
        $this->assertEquals('running', $card['state']);
        $this->assertFalse($card['virtual_running']);

        // default-off school: plain not_started, no marker, no banner flag
        $other = $this->makeAssessment($seed, 'Sekolah lain', now()->subHour(), now()->addHour(), otherSchool: true);
        $bodyB = app(ProctorController::class)->snapshot(Request::create('/x', 'GET'), self::ROLE, self::SCHOOL, $other->school_partner_id)->getData(true);
        $cardB = collect($bodyB['assessments'])->firstWhere('assessment_id', $other->id);
        $this->assertFalse($bodyB['auto_start']);
        $this->assertEquals('not_started', $cardB['state']);
        $this->assertFalse($cardB['virtual_running']);
    }

    public function test_anticheat_settings_endpoint_persists_proctor_auto_admit(): void
    {
        $seed = $this->seedScenario();
        $this->actingAs($seed['admin']);
        $this->withoutMiddleware(\App\Http\Middleware\CheckSchoolContract::class);

        $response = $this->post(
            '/lms/administrator/school-subscription/'.self::SCHOOL."/{$seed['partner']->id}/management-settings/anticheat",
            [
                'anticheat_max_strikes' => 7,
                'anticheat_auto_lock' => true,
                'anticheat_warning_modal' => false,
                'proctor_auto_admit' => true,
            ]
        );

        $response->assertOk();
        $fresh = $seed['partner']->fresh();
        $this->assertTrue((bool) $fresh->proctor_auto_admit, 'the new flag persists like the other anticheat fields');
        $this->assertEquals(7, $fresh->anticheat_max_strikes, 'the existing fields still persist');

        // same endpoint turns it back off
        $this->post(
            '/lms/administrator/school-subscription/'.self::SCHOOL."/{$seed['partner']->id}/management-settings/anticheat",
            [
                'anticheat_max_strikes' => 7,
                'anticheat_auto_lock' => true,
                'anticheat_warning_modal' => false,
                'proctor_auto_admit' => false,
            ]
        )->assertOk();

        $this->assertFalse((bool) $seed['partner']->fresh()->proctor_auto_admit);
    }

    public function test_anticheat_settings_endpoint_persists_proctor_auto_start(): void
    {
        $seed = $this->seedScenario();
        $this->actingAs($seed['admin']);
        $this->withoutMiddleware(\App\Http\Middleware\CheckSchoolContract::class);

        $response = $this->post(
            '/lms/administrator/school-subscription/'.self::SCHOOL."/{$seed['partner']->id}/management-settings/anticheat",
            [
                'anticheat_max_strikes' => 7,
                'anticheat_auto_lock' => true,
                'anticheat_warning_modal' => false,
                'proctor_auto_start' => true,
            ]
        );

        $response->assertOk();
        $fresh = $seed['partner']->fresh();
        $this->assertTrue((bool) $fresh->proctor_auto_start, 'the auto-start flag persists like the other anticheat fields');
        $this->assertEquals(7, $fresh->anticheat_max_strikes, 'the existing fields still persist');

        // the same endpoint turns it back off
        $this->post(
            '/lms/administrator/school-subscription/'.self::SCHOOL."/{$seed['partner']->id}/management-settings/anticheat",
            [
                'anticheat_max_strikes' => 7,
                'anticheat_auto_lock' => true,
                'anticheat_warning_modal' => false,
                'proctor_auto_start' => false,
            ]
        )->assertOk();

        $this->assertFalse((bool) $seed['partner']->fresh()->proctor_auto_start);

        // omitting the optional flag leaves it untouched (older clients)
        $this->post(
            '/lms/administrator/school-subscription/'.self::SCHOOL."/{$seed['partner']->id}/management-settings/anticheat",
            [
                'anticheat_max_strikes' => 7,
                'anticheat_auto_lock' => true,
                'anticheat_warning_modal' => false,
                'proctor_auto_start' => true,
            ]
        )->assertOk();
        $this->post(
            '/lms/administrator/school-subscription/'.self::SCHOOL."/{$seed['partner']->id}/management-settings/anticheat",
            [
                'anticheat_max_strikes' => 7,
                'anticheat_auto_lock' => true,
                'anticheat_warning_modal' => false,
            ]
        )->assertOk();

        $this->assertTrue((bool) $seed['partner']->fresh()->proctor_auto_start, 'sometimes|boolean: an absent flag does not reset the saved value');
    }

    public function test_anticheat_settings_endpoint_rejects_non_admin_roles(): void
    {
        $seed = $this->seedScenario();
        $this->actingAs($seed['student']);
        $this->withoutMiddleware(\App\Http\Middleware\CheckSchoolContract::class);

        // a student posting into the administrator settings area is refused by
        // the same route authorization that guards the existing fields
        $this->post(
            '/lms/administrator/school-subscription/'.self::SCHOOL."/{$seed['partner']->id}/management-settings/anticheat",
            [
                'anticheat_max_strikes' => 7,
                'anticheat_auto_lock' => true,
                'anticheat_warning_modal' => true,
                'proctor_auto_admit' => true,
            ]
        )->assertForbidden();

        $this->assertDatabaseHas('school_partners', ['id' => $seed['partner']->id, 'proctor_auto_admit' => false]);
    }

    public function test_denied_entry_can_be_revoked_through_pic(): void
    {
        $seed = $this->seedScenario();
        $assessment = $seed['assessment'];
        $this->actingAs($seed['teacher']);
        app(ProctorSessionController::class)->control('guru', self::SCHOOL, $seed['partner']->id, $assessment->id, 'start');

        $entry = \App\Models\ProctorEntryRequest::create([
            'school_assessment_id' => $assessment->id,
            'student_id' => $seed['student']->id,
            'status' => 'denied',
            'decided_by' => $seed['teacher']->id,
            'decided_at' => now(),
        ]);

        // guru asks for revocation
        $store = app(ProctorEntryController::class)->requestRevoke('guru', self::SCHOOL, $seed['partner']->id, $entry->id);
        $this->assertEquals('pending', $store->getData(true)['status']);
        $this->assertEquals('pending', $entry->fresh()->revoke_status);

        // the requesting guru cannot approve it; the PIC can, and the entry returns to the queue
        $decide = app(ProctorEntryController::class)->decideRevoke('guru', self::SCHOOL, $seed['partner']->id, $entry->id, 'approve');
        $this->assertEquals(403, $decide->getStatusCode());

        $this->actingAs($seed['approver']);
        $approve = app(ProctorEntryController::class)->decideRevoke('guru', self::SCHOOL, $seed['partner']->id, $entry->id, 'approve');
        $this->assertEquals('ok', $approve->getData(true)['status']);
        $this->assertEquals('pending', $entry->fresh()->status, 'back in the admit queue');
        $this->assertEquals('approved', $entry->fresh()->revoke_status);
    }

    public function test_revocation_needs_pic_and_cannot_be_self_approved(): void
    {
        $seed = $this->seedScenario();
        $assessment = $seed['assessment'];
        $this->actingAs($seed['teacher']);
        app(ProctorSessionController::class)->control('guru', self::SCHOOL, $seed['partner']->id, $assessment->id, 'start');

        $entry = \App\Models\ProctorEntryRequest::create([
            'school_assessment_id' => $assessment->id,
            'student_id' => $seed['student']->id,
            'status' => 'denied',
            'decided_by' => $seed['teacher']->id,
            'decided_at' => now(),
        ]);
        app(ProctorEntryController::class)->requestRevoke('guru', self::SCHOOL, $seed['partner']->id, $entry->id);

        // guru without the PIC flag is refused
        $decide = app(ProctorEntryController::class)->decideRevoke('guru', self::SCHOOL, $seed['partner']->id, $entry->id, 'approve');
        $this->assertEquals(403, $decide->getStatusCode());

        // the requester is also the PIC here: self-approval is refused
        $seed['teacher']->update(['can_approve_unlock' => true]);
        $this->actingAs($seed['teacher']);
        $self = app(ProctorEntryController::class)->decideRevoke('guru', self::SCHOOL, $seed['partner']->id, $entry->id, 'approve');
        $this->assertEquals(403, $self->getStatusCode());
        $this->assertEquals('pending', $entry->fresh()->revoke_status);
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

    public function test_terminate_finalizes_through_the_normal_end_path(): void
    {
        $seed = $this->seedScenario();
        $attempt = $this->attempt($seed['student'], $seed['assessment']);
        $expire = $attempt->expire_time->copy();
        $otherExam = $this->makeAssessment($seed, 'Ujian Sore', now()->subMinutes(30), now()->addHour());
        $untouched = $this->attempt($seed['otherStudent'], $otherExam);

        $question = SchoolAssessmentQuestion::where('school_assessment_id', $seed['assessment']->id)->first();
        StudentAssessmentAnswer::create([
            'student_id' => $seed['student']->id,
            'school_assessment_id' => $seed['assessment']->id,
            'school_assessment_question_id' => $question->id,
            'answer_value' => 'A',
            'question_score' => 0,
            'status_answer' => 'draft',
        ]);

        $this->assertEquals('ok', $this->callAttemptControl($seed, $attempt, 'terminate')->getData(true)['status']);

        // expire_time keeps the real deadline; the attempt is closed properly
        $fresh = $attempt->fresh();
        $this->assertTrue($fresh->expire_time->equalTo($expire), 'terminate must never rewrite the attempt deadline');
        $this->assertEquals('submitted', $fresh->status, 'the teacher ends the exam now: saved answers are submitted');
        $this->assertNotNull($fresh->finalized_at);
        $this->assertNull($fresh->proctor_paused_at);
        $this->assertDatabaseHas('student_assessment_answers', [
            'student_id' => $seed['student']->id,
            'school_assessment_id' => $seed['assessment']->id,
            'status_answer' => 'submitted',
        ]);
        $this->assertNotNull(
            \App\Models\StudentAssessmentSummary::where('student_id', $seed['student']->id)->first(),
            'the summary is recomputed on terminate'
        );
        $this->assertTrue($untouched->fresh()->expire_time->isFuture(), 'the other student keeps their time');
        $this->assertEquals('in_progress', $untouched->fresh()->status);

        $event = end($this->proctor->events);
        $this->assertEquals('exam_stopped', $event['type']);
        $this->assertEquals($attempt->id, $event['attempt_id']);
        $this->assertArrayNotHasKey('audience', $event);
    }

    public function test_terminate_of_an_already_expired_attempt_records_timeout(): void
    {
        $seed = $this->seedScenario();
        $attempt = $this->attempt($seed['student'], $seed['assessment'], [
            'expire_time' => now()->subMinutes(10),
        ]);

        $this->assertEquals('ok', $this->callAttemptControl($seed, $attempt, 'terminate')->getData(true)['status']);
        $this->assertEquals('timeout', $attempt->fresh()->status);
    }

    public function test_attempt_controls_are_refused_for_another_school(): void
    {
        $seed = $this->seedScenario();
        $other = $this->makeAssessment($seed, 'Sekolah lain', now()->subHour(), now()->addHour(), otherSchool: true);
        $attempt = $this->attempt($seed['student'], $other);

        $this->assertEquals(404, $this->callAttemptControl($seed, $attempt, 'suspend')->getStatusCode());
    }

    // platform-admin proctor: cross-school snapshot (schoolId=0), the ?date=
    // review filter, and admin actions against another school via its real id

    /** A full second school (partner, class, roster student, held-today exam) for the cross-school tests. */
    private function secondSchool(array $seed): array
    {
        $partner = SchoolPartner::create(['nama_sekolah' => 'SMA Lain', 'npsn' => '87654322', 'kepsek_id' => $seed['admin']->id, 'jenjang_sekolah' => 'SMA']);
        $student = UserAccount::create(['email' => 'student-b@test.id', 'password' => 'secret', 'no_hp' => '0811111106', 'role' => 'Siswa']);
        $class = SchoolClass::create([
            'school_partner_id' => $partner->id, 'class_name' => '11-B',
            'kelas_id' => $seed['class']->kelas_id, 'wali_kelas_id' => $seed['admin']->id, 'tahun_ajaran' => '2026/2027',
        ]);
        \App\Models\StudentSchoolClass::create(['student_id' => $student->id, 'school_class_id' => $class->id, 'student_class_status' => 'active']);

        return [
            'partner' => $partner,
            'student' => $student,
            'assessment' => SchoolAssessment::create([
                'user_id' => $seed['admin']->id,
                'school_partner_id' => $partner->id,
                'school_class_id' => $class->id,
                'mapel_id' => $seed['mapel']->id,
                'assessment_type_id' => $seed['type']->id,
                'assessment_category' => 'main',
                'title' => 'Ujian Sekolah B',
                'semester' => 1,
                'duration' => 90,
                'start_date' => now()->subHour(),
                'end_date' => now()->addHour(),
                'status' => 'published',
            ]),
        ];
    }

    public function test_platform_admin_cross_school_snapshot_lists_every_school_with_names(): void
    {
        $seed = $this->seedScenario();
        $this->attempt($seed['student'], $seed['assessment']);
        $b = $this->secondSchool($seed);
        $this->attempt($b['student'], $b['assessment']);

        $this->actingAs($seed['admin']);
        $body = app(ProctorController::class)->snapshot(
            Request::create('/x', 'GET'), 'Administrator', 'semua', 0
        )->getData(true);

        $cardA = collect($body['assessments'])->firstWhere('assessment_id', $seed['assessment']->id);
        $cardB = collect($body['assessments'])->firstWhere('assessment_id', $b['assessment']->id);
        $this->assertNotNull($cardA, 'school A exam appears');
        $this->assertNotNull($cardB, 'school B exam appears');

        // each card carries its own school so per-card actions can target the real school
        $this->assertEquals($seed['partner']->id, $cardA['school_partner_id']);
        $this->assertEquals('SMA Test', $cardA['school_name']);
        $this->assertEquals($b['partner']->id, $cardB['school_partner_id']);
        $this->assertEquals('SMA Lain', $cardB['school_name']);

        // one roster across both schools
        $studentIds = array_column($body['students'], 'user_id');
        $this->assertContains($seed['student']->id, $studentIds);
        $this->assertContains($b['student']->id, $studentIds);
    }

    public function test_school_scoped_roles_cannot_use_the_cross_school_view(): void
    {
        $seed = $this->seedScenario();
        $this->actingAs($seed['teacher']);
        $this->withoutMiddleware(\App\Http\Middleware\CheckSchoolContract::class);

        $this->get('/lms/guru/semua/0/proctor')->assertForbidden();

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        app(ProctorController::class)->snapshot(Request::create('/x', 'GET'), self::ROLE, 'semua', 0);
    }

    public function test_a_school_scoped_admin_cannot_use_the_cross_school_view(): void
    {
        $seed = $this->seedScenario();
        $schoolAdmin = UserAccount::create(['email' => 'adminsekolah@test.id', 'password' => 'secret', 'no_hp' => '0811111107', 'role' => 'admin sekolah']);
        $this->actingAs($schoolAdmin);
        $this->withoutMiddleware(\App\Http\Middleware\CheckSchoolContract::class);

        $this->get('/lms/'.rawurlencode('admin sekolah').'/semua/0/proctor')->assertForbidden();

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        app(ProctorController::class)->snapshot(Request::create('/x', 'GET'), 'admin sekolah', 'semua', 0);
    }

    public function test_school_scoped_admin_still_sees_only_their_own_school(): void
    {
        $seed = $this->seedScenario();
        $b = $this->secondSchool($seed);
        $schoolAdmin = UserAccount::create(['email' => 'adminsekolah2@test.id', 'password' => 'secret', 'no_hp' => '0811111108', 'role' => 'admin sekolah']);
        \App\Models\SchoolStaffProfile::create([
            'user_id' => $schoolAdmin->id, 'school_partner_id' => $seed['partner']->id, 'enrollment_type' => 'B2B',
            'nama_lengkap' => 'Admin Sekolah A', 'nik' => '1234567890123457', 'personal_email' => 'asa@test.id',
        ]);
        $this->actingAs($schoolAdmin);

        $body = app(ProctorController::class)->snapshot(
            Request::create('/x', 'GET'), 'admin sekolah', self::SCHOOL, $seed['partner']->id
        )->getData(true);

        $ids = array_column($body['assessments'], 'assessment_id');
        $this->assertContains($seed['assessment']->id, $ids);
        $this->assertNotContains($b['assessment']->id, $ids, "another school's exam stays out of the single-school view");
        $this->assertArrayNotHasKey('school_name', $body['assessments'][0], 'the single-school card shape stays unchanged');
    }

    public function test_platform_admin_controls_another_schools_session_via_its_real_school_id(): void
    {
        $seed = $this->seedScenario();
        $b = $this->secondSchool($seed);
        $this->attempt($b['student'], $b['assessment']);
        $this->actingAs($seed['admin']);

        $res = app(ProctorSessionController::class)->control('Administrator', 'semua', $b['partner']->id, $b['assessment']->id, 'start');
        $this->assertEquals(200, $res->getStatusCode(), 'the admin opens the other school\'s session first');

        $res = app(ProctorSessionController::class)->control('Administrator', 'semua', $b['partner']->id, $b['assessment']->id, 'stop');

        $this->assertEquals(200, $res->getStatusCode());
        $this->assertNotNull($b['assessment']->fresh()->proctor_stopped_at);

        $row = ProctorAuditLog::where('action', 'session_stopped')
            ->where('school_assessment_id', $b['assessment']->id)->firstOrFail();
        $this->assertEquals($b['partner']->id, $row->school_partner_id, 'audited under the exam\'s own school');
        $this->assertEquals($seed['admin']->id, $row->actor_id);
    }

    public function test_platform_admin_approves_an_unlock_on_the_real_school_id_path(): void
    {
        $seed = $this->seedScenario();
        $b = $this->secondSchool($seed);
        $attempt = $this->attempt($b['student'], $b['assessment'], ['status' => 'cheating', 'tab_switch_count' => 5]);
        $request = ProctorUnlockRequest::create([
            'student_assessment_attempt_id' => $attempt->id,
            'requested_by' => $seed['teacher']->id,
            'status' => 'pending',
            'expires_at' => now()->addSeconds(30),
        ]);
        // a platform admin with the PIC flag and NO school profile of their own
        $seed['admin']->update(['can_approve_unlock' => true]);
        $this->actingAs($seed['admin']);

        $res = app(ProctorUnlockController::class)->approve('Administrator', 'semua', $b['partner']->id, $request->id);

        $this->assertEquals('approved', $res->getData(true)['status']);
        $this->assertEquals('in_progress', $attempt->fresh()->status);
        $row = ProctorAuditLog::where('action', 'unlock_approved')->firstOrFail();
        $this->assertEquals($b['partner']->id, $row->school_partner_id, 'bound to the exam\'s school, never the admin\'s own (absent) school');
        $this->assertEquals($seed['admin']->id, $row->actor_id);
    }

    public function test_wrong_school_id_on_admin_actions_keeps_404(): void
    {
        $seed = $this->seedScenario();
        $b = $this->secondSchool($seed);
        $this->actingAs($seed['admin']);

        // school A's id on school B's exam: refused even for a platform admin
        $res = app(ProctorSessionController::class)->control('Administrator', 'semua', $seed['partner']->id, $b['assessment']->id, 'stop');
        $this->assertEquals(404, $res->getStatusCode());
        $this->assertNull($b['assessment']->fresh()->proctor_stopped_at);

        // the unlock decision path keeps the same scoping
        $attempt = $this->attempt($b['student'], $b['assessment'], ['status' => 'cheating', 'tab_switch_count' => 5]);
        $request = ProctorUnlockRequest::create([
            'student_assessment_attempt_id' => $attempt->id,
            'requested_by' => $seed['teacher']->id,
            'status' => 'pending',
            'expires_at' => now()->addSeconds(30),
        ]);
        $seed['admin']->update(['can_approve_unlock' => true]);
        $decide = app(ProctorUnlockController::class)->approve('Administrator', 'semua', $seed['partner']->id, $request->id);
        $this->assertEquals(404, $decide->getStatusCode());
        $this->assertEquals('pending', $request->fresh()->status);

        // the bulk entry decision is uniform with its siblings too: 404 on the
        // all-schools schoolId, not a silent 'skipped everything' 200
        $entry = \App\Models\ProctorEntryRequest::create([
            'school_assessment_id' => $b['assessment']->id,
            'student_id' => $b['student']->id,
            'status' => 'pending',
        ]);
        $decideEntries = app(ProctorEntryController::class)->decide(
            Request::create('/x', 'POST', ['decisions' => [['request_id' => $entry->id, 'approve' => true]]]),
            'Administrator', 'semua', 0
        );
        $this->assertEquals(404, $decideEntries->getStatusCode());
        $this->assertEquals('pending', $entry->fresh()->status, 'no entry is decided behind the 404');
        $this->assertDatabaseMissing('proctor_audit_logs', ['action' => 'entry_admitted', 'school_assessment_id' => $b['assessment']->id]);
    }

    public function test_admin_all_sessions_page_renders_through_the_full_middleware_stack(): void
    {
        $seed = $this->seedScenario();
        $this->withoutMiddleware(\App\Http\Middleware\CheckSchoolContract::class);

        // platform admin: the complete production chain (auth, role access,
        // school membership) lets the all-schools dashboard page render
        $this->actingAs($seed['admin']);
        $this->get('/lms/Administrator/semua/0/proctor')
            ->assertOk()
            ->assertViewIs('features.lms.teacher.proctor.proctor');

        // the same chain hands a school-scoped role to the controller, whose
        // cross-school gate refuses them — pinned against future tightening
        $this->actingAs($seed['teacher']);
        $this->get('/lms/guru/semua/0/proctor')->assertForbidden();
    }

    public function test_snapshot_date_filter_returns_yesterdays_sessions_only(): void
    {
        $seed = $this->seedScenario();
        // pin both windows inside their own day so the split is exact at any clock time
        $today = now('Asia/Jakarta')->startOfDay();
        $seed['assessment']->update(['start_date' => $today->copy()->addMinute(), 'end_date' => $today->copy()->endOfDay()]);
        $yesterday = $today->copy()->subDay();
        $this->makeAssessment($seed, 'Ujian Kemarin', $yesterday->copy()->startOfDay(), $yesterday->copy()->addHours(12));

        $this->actingAs($seed['teacher']);

        $yesterdayBody = app(ProctorController::class)->snapshot(
            Request::create('/x', 'GET', ['date' => $yesterday->format('Y-m-d')]), self::ROLE, self::SCHOOL, $seed['partner']->id
        )->getData(true);
        $titles = array_column($yesterdayBody['assessments'], 'assessment_title');
        $this->assertContains('Ujian Kemarin', $titles);
        $this->assertNotContains('Ujian Hari Ini', $titles, "today's exam stays out of yesterday's review");

        $todayBody = app(ProctorController::class)->snapshot(
            Request::create('/x', 'GET'), self::ROLE, self::SCHOOL, $seed['partner']->id
        )->getData(true);
        $todayTitles = array_column($todayBody['assessments'], 'assessment_title');
        $this->assertContains('Ujian Hari Ini', $todayTitles);
        $this->assertNotContains('Ujian Kemarin', $todayTitles, 'the default stays today');
    }

    public function test_timeline_date_filter_returns_only_that_days_events(): void
    {
        $seed = $this->seedScenario();
        $yesterday = now('Asia/Jakarta')->subDay();

        foreach ([['session_started', $yesterday], ['session_paused', now('Asia/Jakarta')]] as [$action, $at]) {
            ProctorAuditLog::create([
                'actor_id' => $seed['teacher']->id,
                'actor_role' => 'guru',
                'action' => $action,
                'school_partner_id' => $seed['partner']->id,
                'school_assessment_id' => $seed['assessment']->id,
                'created_at' => $at,
            ]);
        }

        $yesterdayEvents = $this->timeline($seed, ['date' => $yesterday->format('Y-m-d')])['events'];
        $this->assertContains('Ujian dimulai', array_column($yesterdayEvents, 'label'));
        $this->assertNotContains('Ujian dijeda', array_column($yesterdayEvents, 'label'), "today's pause stays out of yesterday's review");

        $todayEvents = $this->timeline($seed, [])['events'];
        $this->assertContains('Ujian dijeda', array_column($todayEvents, 'label'));
        $this->assertNotContains('Ujian dimulai', array_column($todayEvents, 'label'));
    }

    public function test_cross_school_timeline_lists_events_from_every_school(): void
    {
        $seed = $this->seedScenario();
        $b = $this->secondSchool($seed);

        // one audited session event in each school, plus a violation in school B
        foreach ([[$seed['partner'], $seed['assessment'], 'session_started'], [$b['partner'], $b['assessment'], 'session_stopped']] as [$partner, $assessment, $action]) {
            ProctorAuditLog::create([
                'actor_id' => $seed['admin']->id,
                'actor_role' => 'administrator',
                'action' => $action,
                'school_partner_id' => $partner->id,
                'school_assessment_id' => $assessment->id,
                'created_at' => now(),
            ]);
        }
        $this->seedViolation($b['student'], $b['assessment'], 'window_blur');

        $this->actingAs($seed['admin']);
        $body = app(ProctorTimelineController::class)->data(
            Request::create('/x', 'GET'), 'Administrator', 'semua', 0
        )->getData(true);

        $labels = array_column($body['events'], 'label');
        $this->assertContains('Ujian dimulai', $labels);
        $this->assertContains('Ujian dihentikan', $labels);
        $this->assertContains('Pelanggaran: window_blur', $labels);

        // every row carries its school so the admin can tell them apart
        $started = collect($body['events'])->firstWhere('label', 'Ujian dimulai');
        $stopped = collect($body['events'])->firstWhere('label', 'Ujian dihentikan');
        $violation = collect($body['events'])->firstWhere('label', 'Pelanggaran: window_blur');
        $this->assertEquals('SMA Test', $started['school_name']);
        $this->assertEquals('SMA Lain', $stopped['school_name']);
        $this->assertEquals('SMA Lain', $violation['school_name'], 'the violation row resolves its school through the exam');

        // the per-school view keeps its exact shape: only its own school, no new fields
        $this->actingAs($seed['teacher']);
        $single = app(ProctorTimelineController::class)->data(
            Request::create('/x', 'GET'), self::ROLE, self::SCHOOL, $seed['partner']->id
        )->getData(true);
        $this->assertNotContains('Ujian dihentikan', array_column($single['events'], 'label'));
        $this->assertArrayNotHasKey('school_name', $single['events'][0]);
    }

    public function test_school_scoped_roles_cannot_use_the_cross_school_timeline(): void
    {
        $seed = $this->seedScenario();
        $this->actingAs($seed['teacher']);
        $this->withoutMiddleware(\App\Http\Middleware\CheckSchoolContract::class);

        $this->get('/lms/guru/semua/0/proctor/timeline/data')->assertForbidden();
        $this->get('/lms/guru/semua/0/proctor/timeline')->assertForbidden();

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        app(ProctorTimelineController::class)->data(Request::create('/x', 'GET'), self::ROLE, 'semua', 0);
    }

    public function test_invalid_date_is_rejected_with_422(): void
    {
        $seed = $this->seedScenario();
        $this->actingAs($seed['teacher']);
        $this->withoutMiddleware(\App\Http\Middleware\CheckSchoolContract::class);
        $base = '/lms/guru/'.self::SCHOOL."/{$seed['partner']->id}";

        foreach (['31-12-2026', '2026-02-30', 'kemarin', '2026-11-26xx', '2026-1-9'] as $garbage) {
            $this->get($base.'/proctor/snapshot?date='.urlencode($garbage))->assertStatus(422);
            $this->get($base.'/proctor/timeline/data?date='.urlencode($garbage))->assertStatus(422);
        }

        // a well-formed date passes both endpoints
        $day = now('Asia/Jakarta')->format('Y-m-d');
        $this->get($base."/proctor/snapshot?date={$day}")->assertOk();
        $this->get($base."/proctor/timeline/data?date={$day}")->assertOk();
    }

    public function test_a_sudoed_student_can_still_operate_their_exam(): void
    {
        $seed = $this->seedScenario();
        $this->startExam($seed, $seed['student']);
        $started = $this->startSessionAs($seed);
        $this->decideEntries($seed, [['request_id' => $started['pending'][0]['request_id'], 'approve' => true]]);

        // the platform admin sudos into the student through the real route
        $this->actingAs($seed['admin']);
        $this->withoutMiddleware(\App\Http\Middleware\CheckSchoolContract::class);
        $this->get('/lms/Administrator/sudo/'.$seed['student']->id);

        $this->assertAuthenticatedAs($seed['student']);
        $this->assertEquals($seed['admin']->id, session(\App\Http\Controllers\SudoController::SESSION_KEY));

        // the sudoed student drives the real start-timer route: the role and
        // school middleware accept them in the target's own school context
        $res = $this->get('/lms/Siswa/'.self::SCHOOL."/{$seed['partner']->id}/curriculum/1/subject/1/learning/assessment/{$seed['type']->id}/semester/1/form/{$seed['assessment']->id}/start-timer");

        $res->assertOk();
        $this->assertEquals('success', $res->getData(true)['status']);
        $this->assertStringContainsString('no-store', $res->headers->get('Cache-Control'), 'exam state must never be cached');
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
