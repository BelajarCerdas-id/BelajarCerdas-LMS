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
use App\Models\SubBab;
use App\Models\UserAccount;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class AssessmentAnswerCrossAssessmentGuardTest extends TestCase
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

        // school_classes diperlukan sebagai FK
        $class = \App\Models\SchoolClass::create([
            'school_partner_id' => $partner->id,
            'class_name' => '10-A',
            'kelas_id' => $kelas->id,
            'wali_kelas_id' => $creator->id,
            'tahun_ajaran' => '2026/2027',
        ]);

        $makeAssessment = function () use ($creator, $partner, $class, $mapel, $type, $bank) {
            $assessment = SchoolAssessment::create([
                'user_id' => $creator->id,
                'school_partner_id' => $partner->id,
                'school_class_id' => $class->id,
                'mapel_id' => $mapel->id,
                'assessment_type_id' => $type->id,
                'assessment_category' => 'main',
                'title' => 'Ujian',
                'semester' => 1,
                'start_date' => now()->subDays(2),
                'end_date' => now()->addDays(2),
            ]);

            $question = SchoolAssessmentQuestion::create([
                'school_assessment_id' => $assessment->id,
                'question_bank_id' => $bank->id,
                'question_weight' => 100,
            ]);

            return [$assessment, $question];
        };

        [$assessment, $question] = $makeAssessment();
        [$otherAssessment, $otherQuestion] = $makeAssessment();

        return compact('student', 'partner', 'kurikulum', 'mapel', 'type', 'assessment', 'question', 'otherAssessment', 'otherQuestion');
    }

    private function callAnswer(array $seed, int $questionId): void
    {
        $this->actingAs($seed['student']);

        $request = Request::create('/answer', 'POST', [
            'school_assessment_question_id' => $questionId,
            'answer_value' => 'A',
            'status_answer' => 'draft',
        ]);

        app(\App\Http\Controllers\StudentAssessmentExamController::class)
            ->studentAssessmentExamAnswer(
                $request,
                'student',
                'sekolah-test',
                $seed['partner']->id,
                $seed['kurikulum']->id,
                $seed['mapel']->id,
                $seed['type']->id,
                1,
                $seed['assessment']->id
            );
    }

    public function test_rejects_question_from_another_assessment(): void
    {
        $seed = $this->seedData();

        $this->expectException(ModelNotFoundException::class);

        $this->callAnswer($seed, $seed['otherQuestion']->id);
    }

    public function test_accepts_question_of_the_same_assessment(): void
    {
        $seed = $this->seedData();

        $this->callAnswer($seed, $seed['question']->id);

        $this->assertDatabaseHas('student_assessment_answers', [
            'student_id' => $seed['student']->id,
            'school_assessment_id' => $seed['assessment']->id,
            'school_assessment_question_id' => $seed['question']->id,
            'status_answer' => 'draft',
        ]);
    }
}
