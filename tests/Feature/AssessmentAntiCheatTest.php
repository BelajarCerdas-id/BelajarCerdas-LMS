<?php

namespace Tests\Feature;

use App\Models\AssessmentMode;
use App\Models\Bab;
use App\Models\Fase;
use App\Models\Kelas;
use App\Models\Kurikulum;
use App\Models\LmsQuestionBank;
use App\Models\Mapel;
use App\Models\SchoolAssessment;
use App\Models\SchoolAssessmentQuestion;
use App\Models\SchoolAssessmentType;
use App\Models\SchoolPartner;
use App\Models\StudentAssessmentAnswer;
use App\Models\StudentAssessmentAttempt;
use App\Models\StudentAssessmentSummary;
use App\Models\SubBab;
use App\Models\UserAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AssessmentAntiCheatTest extends TestCase
{
    use RefreshDatabase;

    private function seedData(): array
    {
        $creator = UserAccount::create([
            'email' => 'creator@test.id',
            'password' => 'secret',
            'no_hp' => '0811111111',
            'role' => 'administrator',
        ]);

        $student = UserAccount::create([
            'email' => 'student@test.id',
            'password' => 'secret',
            'no_hp' => '0811111112',
            'role' => 'Siswa',
        ]);

        $otherStudent = UserAccount::create([
            'email' => 'other-student@test.id',
            'password' => 'secret',
            'no_hp' => '0811111113',
            'role' => 'Siswa',
        ]);

        $partner = SchoolPartner::create([
            'nama_sekolah' => 'SMA Test',
            'npsn' => '12345678',
            'kepsek_id' => $creator->id,
            'jenjang_sekolah' => 'SMA',
        ]);

        $kurikulum = Kurikulum::create([
            'user_id' => $creator->id,
            'nama_kurikulum' => 'Kurikulum Merdeka',
            'kode' => 'KM',
        ]);

        $fase = Fase::create([
            'user_id' => $creator->id,
            'nama_fase' => 'Fase E',
            'kode' => 'E',
            'kurikulum_id' => $kurikulum->id,
        ]);

        $kelas = Kelas::create([
            'user_id' => $creator->id,
            'kelas' => 'Kelas 10',
            'kode' => 'X',
            'fase_id' => $fase->id,
            'kurikulum_id' => $kurikulum->id,
        ]);

        $mapel = Mapel::create([
            'user_id' => $creator->id,
            'mata_pelajaran' => 'Matematika',
            'kode' => 'MAT',
            'kelas_id' => $kelas->id,
            'kurikulum_id' => $kurikulum->id,
        ]);

        $bab = Bab::create([
            'user_id' => $creator->id,
            'nama_bab' => 'Bab 1',
            'kode' => 'B1',
            'mapel_id' => $mapel->id,
            'kelas_id' => $kelas->id,
            'kurikulum_id' => $kurikulum->id,
        ]);

        $subBab = SubBab::create([
            'user_id' => $creator->id,
            'sub_bab' => 'Sub 1',
            'kode' => 'S1',
            'bab_id' => $bab->id,
            'mapel_id' => $mapel->id,
            'kelas_id' => $kelas->id,
            'kurikulum_id' => $kurikulum->id,
        ]);

        $bank = LmsQuestionBank::create([
            'user_id' => $creator->id,
            'kurikulum_id' => $kurikulum->id,
            'kelas_id' => $kelas->id,
            'mapel_id' => $mapel->id,
            'bab_id' => $bab->id,
            'sub_bab_id' => $subBab->id,
            'questions' => 'Soal?',
            'difficulty' => 'Mudah',
            'bloom' => 'C1',
            'tipe_soal' => 'MCQ',
            'question_source' => 'manual',
        ]);

        $mode = AssessmentMode::create([
            'name' => 'Test',
            'code' => 'test',
        ]);

        $type = SchoolAssessmentType::create([
            'user_id' => $creator->id,
            'school_partner_id' => $partner->id,
            'name' => 'Ulangan Harian',
            'assessment_mode_id' => $mode->id,
        ]);

        $class = \App\Models\SchoolClass::create([
            'school_partner_id' => $partner->id,
            'class_name' => '10-A',
            'kelas_id' => $kelas->id,
            'wali_kelas_id' => $creator->id,
            'tahun_ajaran' => '2026/2027',
        ]);

        $assessment = SchoolAssessment::create([
            'user_id' => $creator->id,
            'school_partner_id' => $partner->id,
            'school_class_id' => $class->id,
            'mapel_id' => $mapel->id,
            'assessment_type_id' => $type->id,
            'assessment_category' => 'main',
            'title' => 'Ujian',
            'semester' => 1,
            'duration' => 120,
            'start_date' => now()->subDays(2),
            'end_date' => now()->addDays(2),
        ]);

        $question = SchoolAssessmentQuestion::create([
            'school_assessment_id' => $assessment->id,
            'question_bank_id' => $bank->id,
            'question_weight' => 100,
        ]);

        return compact('student', 'otherStudent', 'partner', 'kurikulum', 'mapel', 'type', 'assessment', 'question');
    }

    private function startAttempt(array $seed, $user = null, array $overrides = []): StudentAssessmentAttempt
    {
        return StudentAssessmentAttempt::create(array_merge([
            'student_id' => ($user ?? $seed['student'])->id,
            'school_assessment_id' => $seed['assessment']->id,
            'start_time' => now()->subMinutes(5),
            'expire_time' => now()->addMinutes(115),
            'last_heartbeat_at' => now()->subSeconds(5),
            'status' => 'in_progress',
            'tab_switch_count' => 0,
        ], $overrides));
    }

    private function callReport(array $seed, string $reason = 'visibility_hidden', bool $force = false)
    {
        return app(\App\Http\Controllers\AssessmentAntiCheatController::class)
            ->reportTabSwitch(
                Request::create('/report', 'POST', ['reason' => $reason, 'force' => $force], [], [], [
                    'REMOTE_ADDR' => '172.70.143.163',
                    'HTTP_CF_CONNECTING_IP' => '203.0.113.9',
                    'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                ]),
                'student', 'sekolah-test', $seed['partner']->id, $seed['kurikulum']->id,
                $seed['mapel']->id, $seed['type']->id, 1, $seed['assessment']->id
            );
    }

    private function callHeartbeat(array $seed, bool $isSelectingFile = false)
    {
        return app(\App\Http\Controllers\AssessmentAntiCheatController::class)
            ->heartbeat(
                Request::create('/heartbeat', 'POST', [
                    'timestamp' => now()->timestamp,
                    'is_selecting_file' => $isSelectingFile,
                ], [], [], [
                    'REMOTE_ADDR' => '203.0.113.9',
                    'HTTP_USER_AGENT' => 'Mozilla/5.0 (Linux; Android 13; SM-G991B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Mobile Safari/537.36',
                ]),
                'student', 'sekolah-test', $seed['partner']->id, $seed['kurikulum']->id,
                $seed['mapel']->id, $seed['type']->id, 1, $seed['assessment']->id
            );
    }

    private function callStatus(array $seed)
    {
        return app(\App\Http\Controllers\AssessmentAntiCheatController::class)
            ->checkAttemptStatus(
                'student', 'sekolah-test', $seed['partner']->id, $seed['kurikulum']->id,
                $seed['mapel']->id, $seed['type']->id, 1, $seed['assessment']->id
            );
    }

    private function callAnswer(array $seed, array $extra = [])
    {
        $payload = array_merge([
            'school_assessment_question_id' => $seed['question']->id,
            'answer_value' => 'A',
            'status_answer' => 'draft',
        ], $extra);

        return app(\App\Http\Controllers\StudentAssessmentExamController::class)
            ->studentAssessmentExamAnswer(
                Request::create('/answer', 'POST', $payload),
                'student', 'sekolah-test', $seed['partner']->id, $seed['kurikulum']->id,
                $seed['mapel']->id, $seed['type']->id, 1, $seed['assessment']->id
            );
    }

    private function callEnd(array $seed, array $payload = [])
    {
        return app(\App\Http\Controllers\StudentAssessmentExamController::class)
            ->studentAssessmentExamEnd(
                Request::create('/end', 'POST', array_merge([
                    'status_attempt' => 'submitted',
                ], $payload)),
                'student', 'sekolah-test', $seed['partner']->id, $seed['kurikulum']->id,
                $seed['mapel']->id, $seed['type']->id, 1, $seed['assessment']->id
            );
    }

    private function clearCooldown(array $seed): void
    {
        $attempt = StudentAssessmentAttempt::where('school_assessment_id', $seed['assessment']->id)->first();
        Cache::forget("tab_switch_cooldown_{$attempt->id}");
    }

    public function test_tab_switch_counts_strike_and_cooldowns_duplicates(): void
    {
        $seed = $seed = $this->seedData();
        $this->actingAs($seed['student']);
        $attempt = $this->startAttempt($seed);

        $res1 = $this->callReport($seed);
        $this->assertEquals('warning', $res1->getData(true)['status']);
        $this->assertEquals(1, $res1->getData(true)['count']);

        // duplicate within the 10s cooldown must not increment
        $res2 = $this->callReport($seed);
        $this->assertEquals(1, $res2->getData(true)['count']);

        // after the cooldown window a new strike counts
        $this->travel(11)->seconds();
        $res3 = $this->callReport($seed);
        $this->assertEquals(2, $res3->getData(true)['count']);

        $this->assertEquals(2, $attempt->fresh()->tab_switch_count);
        $this->assertEquals('in_progress', $attempt->fresh()->status);
    }

    public function test_fifth_strike_blocks_attempt_and_flags_cheating(): void
    {
        $seed = $this->seedData();
        $this->actingAs($seed['student']);
        $attempt = $this->startAttempt($seed);

        $lastResponse = null;
        for ($i = 0; $i < 5; $i++) {
            $this->clearCooldown($seed);
            $lastResponse = $this->callReport($seed);
        }

        $this->assertEquals('blocked', $lastResponse->getData(true)['status']);
        $this->assertEquals(5, $attempt->fresh()->tab_switch_count);
        $this->assertEquals('cheating', $attempt->fresh()->status);

        // every strike is recorded as a violation with client context
        $this->assertDatabaseCount('student_assessment_anticheat_violations', 5);

        // status endpoint reports blocked
        $this->assertEquals('blocked', $this->callStatus($seed)->getData(true)['status']);
    }

    public function test_forced_fullscreen_reports_bypass_cooldown(): void
    {
        $seed = $this->seedData();
        $this->actingAs($seed['student']);
        $attempt = $this->startAttempt($seed);

        // rapid fullscreen exits (well within the 10s cooldown) each count
        $res1 = $this->callReport($seed, 'fullscreen_exit', true);
        $this->assertEquals(1, $res1->getData(true)['count']);

        // event-storm guard: a burst of duplicate exit events within 5s counts once
        $res2 = $this->callReport($seed, 'fullscreen_exit', true);
        $this->assertEquals(1, $res2->getData(true)['count']);

        // but a genuinely new exit (after the 5s window) counts again
        $attemptForceKey = "anticheat_forced_{$attempt->id}_fullscreen_exit";
        Cache::forget($attemptForceKey);
        $res3 = $this->callReport($seed, 'fullscreen_exit', true);
        $this->assertEquals(2, $res3->getData(true)['count']);
        $this->assertEquals(2, $attempt->fresh()->tab_switch_count);
    }

    public function test_violation_stores_ip_browser_and_device(): void
    {
        $seed = $this->seedData();
        $this->actingAs($seed['student']);
        $attempt = $this->startAttempt($seed);

        $this->callReport($seed, 'visibility_hidden');

        $violation = \App\Models\StudentAssessmentAnticheatViolation::first();
        $this->assertNotNull($violation);
        $this->assertEquals($attempt->id, $violation->student_assessment_attempt_id);
        // behind Cloudflare the real client IP comes from CF-Connecting-IP, not the edge
        $this->assertEquals('203.0.113.9', $violation->ip);
        $this->assertEquals('Chrome', $violation->browser);
        $this->assertMatchesRegularExpression('/^120\./', (string) $violation->browser_version);
        $this->assertEquals('desktop', $violation->device_type);
        $this->assertEquals('Windows', $violation->platform);
        $this->assertEquals('visibility_hidden', $violation->reason);
    }

    public function test_heartbeat_gap_violation_stores_mobile_device(): void
    {
        $seed = $this->seedData();
        $this->actingAs($seed['student']);
        $attempt = $this->startAttempt($seed, overrides: [
            'last_heartbeat_at' => now()->subSeconds(40),
        ]);

        $res = $this->callHeartbeat($seed);
        $this->assertEquals('warning', $res->getData(true)['status']);

        $violation = \App\Models\StudentAssessmentAnticheatViolation::first();
        $this->assertNotNull($violation);
        $this->assertEquals('heartbeat_gap', $violation->reason);
        $this->assertEquals('203.0.113.9', $violation->ip);
        $this->assertEquals('mobile', $violation->device_type);
        $this->assertEquals('Android', $violation->platform);
    }

    public function test_heartbeat_gap_counts_strike_but_normal_ping_does_not(): void
    {
        $seed = $this->seedData();
        $this->actingAs($seed['student']);
        $attempt = $this->startAttempt($seed);

        // normal ping within 35s: ok
        $res = $this->callHeartbeat($seed);
        $this->assertEquals('ok', $res->getData(true)['status']);
        $this->assertEquals(0, $attempt->fresh()->tab_switch_count);

        // gap over 35s (phone backgrounded): server counts a strike
        $attempt->update(['last_heartbeat_at' => now()->subSeconds(40)]);
        $res = $this->callHeartbeat($seed);
        $this->assertEquals('warning', $res->getData(true)['status']);
        $this->assertEquals(1, $attempt->fresh()->tab_switch_count);
    }

    public function test_file_picker_grace_requires_server_sided_upload(): void
    {
        $seed = $this->seedData();
        $this->actingAs($seed['student']);
        $attempt = $this->startAttempt($seed);

        // client claims to be selecting a file, but no upload happened:
        // the extended 180s window must NOT apply
        $attempt->update(['last_heartbeat_at' => now()->subSeconds(100)]);
        $res = $this->callHeartbeat($seed, isSelectingFile: true);
        $this->assertEquals('warning', $res->getData(true)['status']);

        // with a server-verified recent upload the extended window applies
        Cache::put(\App\Http\Controllers\AssessmentAntiCheatController::fileGraceKey(), true, 180);
        $this->clearCooldown($seed);
        $attempt->update(['status' => 'in_progress', 'last_heartbeat_at' => now()->subSeconds(100)]);
        $res = $this->callHeartbeat($seed, isSelectingFile: true);
        $this->assertEquals('ok', $res->getData(true)['status']);

        // but beyond even the grace window it still counts
        $this->clearCooldown($seed);
        $attempt->update(['last_heartbeat_at' => now()->subSeconds(200)]);
        $res = $this->callHeartbeat($seed, isSelectingFile: true);
        $this->assertEquals('warning', $res->getData(true)['status']);
    }

    public function test_heartbeat_for_someone_elses_attempt_is_ignored(): void
    {
        $seed = $this->seedData();
        $this->actingAs($seed['otherStudent']); // no attempt for this user
        $attempt = $this->startAttempt($seed, $seed['student']);

        $res = $this->callHeartbeat($seed);
        $this->assertEquals('ignored', $res->getData(true)['status']);
        $this->assertEquals(0, $attempt->fresh()->tab_switch_count);
    }

    public function test_answer_requires_started_attempt(): void
    {
        $seed = $this->seedData();
        $this->actingAs($seed['student']);

        $res = $this->callAnswer($seed);

        $this->assertEquals(422, $res->getStatusCode());
        $this->assertEquals('not_started', $res->getData(true)['status']);
        $this->assertDatabaseMissing('student_assessment_answers', [
            'student_id' => $seed['student']->id,
        ]);
    }

    public function test_draft_answer_save_does_not_end_the_exam(): void
    {
        $seed = $this->seedData();
        $this->actingAs($seed['student']);
        $attempt = $this->startAttempt($seed);

        // per-question draft saves carry a hidden status_attempt=submitted field;
        // they must NOT flip the attempt (that would disable the anti-cheat)
        $res = $this->callAnswer($seed, ['status_attempt' => 'submitted']);
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('in_progress', $attempt->fresh()->status);
    }

    public function test_cheating_answer_rejected_even_with_auto_submit(): void
    {
        $seed = $this->seedData();
        $this->actingAs($seed['student']);
        $this->startAttempt($seed, overrides: ['status' => 'cheating']);

        // no per-question writes are allowed after the attempt is flagged
        $res = $this->callAnswer($seed, [
            'auto_submit' => true,
            'status_attempt' => 'cheating',
        ]);
        $this->assertEquals(422, $res->getStatusCode());
        $this->assertDatabaseMissing('student_assessment_answers', [
            'student_id' => $seed['student']->id,
        ]);
        $this->assertEquals('cheating', StudentAssessmentAttempt::first()->status);
    }

    public function test_answer_rejected_after_submit_or_timeout(): void
    {
        $seed = $this->seedData();
        $this->actingAs($seed['student']);
        $attempt = $this->startAttempt($seed);

        foreach (['submitted', 'timeout'] as $status) {
            $attempt->update(['status' => $status]);

            $res = $this->callAnswer($seed);
            $this->assertEquals(422, $res->getStatusCode());
        }

        $this->assertDatabaseMissing('student_assessment_answers', [
            'student_id' => $seed['student']->id,
        ]);
    }

    public function test_answer_rejected_after_expiry_without_writing(): void
    {
        $seed = $this->seedData();
        $this->actingAs($seed['student']);
        $this->startAttempt($seed, overrides: ['expire_time' => now()->subMinute()]);

        $res = $this->callAnswer($seed);

        $this->assertEquals(422, $res->getStatusCode());
        $this->assertEquals('expired', $res->getData(true)['status']);
        $this->assertDatabaseMissing('student_assessment_answers', [
            'student_id' => $seed['student']->id,
        ]);
    }

    public function test_end_is_one_shot_and_blocks_later_answer_writes(): void
    {
        $seed = $this->seedData();
        $this->actingAs($seed['student']);
        $attempt = $this->startAttempt($seed);

        $res = $this->callEnd($seed, [
            'current_question_id' => $seed['question']->id,
            'current_answer_value' => 'A',
        ]);
        $this->assertEquals(200, $res->getStatusCode());

        $attempt->refresh();
        $this->assertEquals('submitted', $attempt->status);
        $this->assertNotNull($attempt->finalized_at);
        $this->assertDatabaseHas('student_assessment_answers', [
            'student_id' => $seed['student']->id,
            'school_assessment_id' => $seed['assessment']->id,
            'answer_value' => '"A"',
        ]);

        // per-question writes are closed
        $this->assertEquals(422, $this->callAnswer($seed)->getStatusCode());

        // replaying the final submit must not rewrite the answer
        $res = $this->callEnd($seed, [
            'current_question_id' => $seed['question']->id,
            'current_answer_value' => 'B',
        ]);
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertDatabaseHas('student_assessment_answers', [
            'student_id' => $seed['student']->id,
            'school_assessment_id' => $seed['assessment']->id,
            'answer_value' => '"A"',
        ]);
    }

    public function test_cheating_final_submit_saves_answers_once(): void
    {
        $seed = $this->seedData();
        $this->actingAs($seed['student']);
        $attempt = $this->startAttempt($seed, overrides: ['status' => 'cheating']);

        $res = $this->callEnd($seed, [
            'current_question_id' => $seed['question']->id,
            'current_answer_value' => 'A',
        ]);
        $this->assertEquals(200, $res->getStatusCode());

        $attempt->refresh();
        $this->assertEquals('cheating', $attempt->status);
        $this->assertNotNull($attempt->finalized_at);
        $this->assertDatabaseHas('student_assessment_answers', [
            'student_id' => $seed['student']->id,
            'school_assessment_id' => $seed['assessment']->id,
            'answer_value' => '"A"',
        ]);

        // a replay cannot change the saved answer
        $res = $this->callEnd($seed, [
            'current_question_id' => $seed['question']->id,
            'current_answer_value' => 'B',
        ]);
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertDatabaseHas('student_assessment_answers', [
            'student_id' => $seed['student']->id,
            'school_assessment_id' => $seed['assessment']->id,
            'answer_value' => '"A"',
        ]);
    }

    public function test_cheating_attempt_gets_zero_score_in_summary(): void
    {
        $seed = $this->seedData();
        $this->actingAs($seed['student']);
        $attempt = $this->startAttempt($seed, overrides: ['status' => 'cheating']);

        StudentAssessmentAnswer::create([
            'student_id' => $seed['student']->id,
            'school_assessment_id' => $seed['assessment']->id,
            'school_assessment_question_id' => $seed['question']->id,
            'answer_value' => 'A',
            'question_score' => 100,
            'status_answer' => 'submitted',
        ]);

        app(\App\Services\LMS\AssessmentSummaryService\AssessmentSummaryService::class)
            ->updateStudentAssessmentSummary($seed['student']->id, $seed['assessment']);

        $summary = StudentAssessmentSummary::where('student_id', $seed['student']->id)->first();
        $this->assertNotNull($summary);
        $this->assertEquals(0, $summary->main_score);
        $this->assertEquals(0, $summary->final_score);
    }

    public function test_honest_attempt_keeps_its_score_in_summary(): void
    {
        $seed = $this->seedData();
        $this->actingAs($seed['student']);
        $this->startAttempt($seed);

        StudentAssessmentAnswer::create([
            'student_id' => $seed['student']->id,
            'school_assessment_id' => $seed['assessment']->id,
            'school_assessment_question_id' => $seed['question']->id,
            'answer_value' => 'A',
            'question_score' => 100,
            'status_answer' => 'submitted',
        ]);

        app(\App\Services\LMS\AssessmentSummaryService\AssessmentSummaryService::class)
            ->updateStudentAssessmentSummary($seed['student']->id, $seed['assessment']);

        $summary = StudentAssessmentSummary::where('student_id', $seed['student']->id)->first();
        $this->assertEquals(100, $summary->main_score);
        $this->assertEquals(100, $summary->final_score);
    }
}
