<?php

namespace Tests\Feature;

use App\Models\AssessmentMode;
use App\Models\Fase;
use App\Models\Kelas;
use App\Models\Kurikulum;
use App\Models\Mapel;
use App\Models\SchoolAssessment;
use App\Models\SchoolAssessmentQuestion;
use App\Models\SchoolAssessmentType;
use App\Models\SchoolClass;
use App\Models\SchoolPartner;
use App\Models\StudentAssessmentAnswer;
use App\Models\StudentAssessmentSummary;
use App\Models\SubjectPassingGradeCriteria;
use App\Models\UserAccount;
use App\Services\LMS\AssessmentSummaryService\AssessmentSummaryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssessmentSummaryServiceTest extends TestCase
{
    use RefreshDatabase;

    private function seedBase(): array
    {
        $user = UserAccount::create([
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

        $partner = SchoolPartner::create([
            'nama_sekolah' => 'SMA Test',
            'npsn' => '12345678',
            'kepsek_id' => $user->id,
            'jenjang_sekolah' => 'SMA',
        ]);

        $kurikulum = Kurikulum::create([
            'user_id' => $user->id,
            'nama_kurikulum' => 'Kurikulum Merdeka',
            'kode' => 'KM',
        ]);

        $fase = Fase::create([
            'user_id' => $user->id,
            'nama_fase' => 'Fase E',
            'kode' => 'E',
            'kurikulum_id' => $kurikulum->id,
        ]);

        $kelas = Kelas::create([
            'user_id' => $user->id,
            'kelas' => 'Kelas 10',
            'kode' => 'X',
            'fase_id' => $fase->id,
            'kurikulum_id' => $kurikulum->id,
        ]);

        $mapel = Mapel::create([
            'user_id' => $user->id,
            'mata_pelajaran' => 'Matematika',
            'kode' => 'MAT',
            'kelas_id' => $kelas->id,
            'kurikulum_id' => $kurikulum->id,
        ]);

        $mode = AssessmentMode::create([
            'name' => 'Test',
            'code' => 'test',
        ]);

        $type = SchoolAssessmentType::create([
            'user_id' => $user->id,
            'school_partner_id' => $partner->id,
            'name' => 'Ulangan Harian',
            'assessment_mode_id' => $mode->id,
        ]);

        $class = SchoolClass::create([
            'school_partner_id' => $partner->id,
            'class_name' => '10-A',
            'kelas_id' => $kelas->id,
            'wali_kelas_id' => $user->id,
            'tahun_ajaran' => '2026/2027',
        ]);

        SubjectPassingGradeCriteria::create([
            'user_id' => $user->id,
            'school_partner_id' => $partner->id,
            'kelas_id' => $kelas->id,
            'mapel_id' => $mapel->id,
            'school_year' => '2026/2027',
            'kkm_value' => 70,
        ]);

        return compact('user', 'student', 'partner', 'kelas', 'mapel', 'type', 'class');
    }

    // satu soal berbobot 100 per assessment, jawaban memberi score langsung = nilai
    private function seedAttempt(array $seed, string $category, int $score, ?int $parentId = null): SchoolAssessment
    {
        $assessment = SchoolAssessment::create([
            'user_id' => $seed['user']->id,
            'school_partner_id' => $seed['partner']->id,
            'school_class_id' => $seed['class']->id,
            'mapel_id' => $seed['mapel']->id,
            'assessment_type_id' => $seed['type']->id,
            'assessment_category' => $category,
            'parent_assessment_id' => $parentId,
            'title' => ucfirst($category),
            'semester' => 1,
            'start_date' => now()->subDays(2),
            'end_date' => now()->addDays(2),
        ]);

        $question = SchoolAssessmentQuestion::create([
            'school_assessment_id' => $assessment->id,
            'question_bank_id' => $this->bankIdFor($seed),
            'question_weight' => 100,
        ]);

        StudentAssessmentAnswer::create([
            'student_id' => $seed['student']->id,
            'school_assessment_id' => $assessment->id,
            'school_assessment_question_id' => $question->id,
            'answer_value' => 'A',
            'question_score' => $score,
            'status_answer' => 'submitted',
        ]);

        return $assessment;
    }

    private function bankIdFor(array $seed): int
    {
        if (isset($this->bankId)) {
            return $this->bankId;
        }

        $bab = \App\Models\Bab::create([
            'user_id' => $seed['user']->id,
            'nama_bab' => 'Bab 1',
            'kode' => 'B1',
            'mapel_id' => $seed['mapel']->id,
            'kelas_id' => $seed['kelas']->id,
            'kurikulum_id' => Kurikulum::first()->id,
        ]);

        $subBab = \App\Models\SubBab::create([
            'user_id' => $seed['user']->id,
            'sub_bab' => 'Sub 1',
            'kode' => 'S1',
            'bab_id' => $bab->id,
            'mapel_id' => $seed['mapel']->id,
            'kelas_id' => $seed['kelas']->id,
            'kurikulum_id' => Kurikulum::first()->id,
        ]);

        $bank = \App\Models\LmsQuestionBank::create([
            'user_id' => $seed['user']->id,
            'kurikulum_id' => Kurikulum::first()->id,
            'kelas_id' => $seed['kelas']->id,
            'mapel_id' => $seed['mapel']->id,
            'bab_id' => $bab->id,
            'sub_bab_id' => $subBab->id,
            'questions' => 'Soal?',
            'difficulty' => 'Mudah',
            'bloom' => 'C1',
            'tipe_soal' => 'MCQ',
            'question_source' => 'manual',
        ]);

        return $this->bankId = $bank->id;
    }

    public function test_remedial_below_kkm_stays_as_final_score(): void
    {
        $seed = $this->seedBase();
        $main = $this->seedAttempt($seed, 'main', 50);
        $remedial = $this->seedAttempt($seed, 'remedial', 65, $main->id);

        app(AssessmentSummaryService::class)->updateStudentAssessmentSummary($seed['student']->id, $remedial);

        $summary = StudentAssessmentSummary::where('student_id', $seed['student']->id)->first();

        $this->assertNotNull($summary);
        $this->assertSame('remedial', $summary->score_source);
        $this->assertEquals(65, $summary->final_score);
        $this->assertEquals(50, $summary->main_score);
    }

    public function test_remedial_at_or_above_kkm_is_capped_at_kkm(): void
    {
        $seed = $this->seedBase();
        $main = $this->seedAttempt($seed, 'main', 50);
        $remedial = $this->seedAttempt($seed, 'remedial', 85, $main->id);

        app(AssessmentSummaryService::class)->updateStudentAssessmentSummary($seed['student']->id, $remedial);

        $summary = StudentAssessmentSummary::where('student_id', $seed['student']->id)->first();

        $this->assertSame('remedial', $summary->score_source);
        $this->assertEquals(70, $summary->final_score);
    }

    public function test_highest_score_wins_between_main_and_remedial(): void
    {
        $seed = $this->seedBase();
        $main = $this->seedAttempt($seed, 'main', 80);
        $remedial = $this->seedAttempt($seed, 'remedial', 40, $main->id);

        app(AssessmentSummaryService::class)->updateStudentAssessmentSummary($seed['student']->id, $remedial);

        $summary = StudentAssessmentSummary::where('student_id', $seed['student']->id)->first();

        $this->assertSame('main', $summary->score_source);
        $this->assertEquals(80, $summary->final_score);
    }

    public function test_highest_score_wins_between_main_and_susulan(): void
    {
        $seed = $this->seedBase();
        $main = $this->seedAttempt($seed, 'main', 40);
        $susulan = $this->seedAttempt($seed, 'susulan', 75, $main->id);

        app(AssessmentSummaryService::class)->updateStudentAssessmentSummary($seed['student']->id, $susulan);

        $summary = StudentAssessmentSummary::where('student_id', $seed['student']->id)->first();

        $this->assertSame('susulan', $summary->score_source);
        $this->assertEquals(75, $summary->final_score);
    }
}
