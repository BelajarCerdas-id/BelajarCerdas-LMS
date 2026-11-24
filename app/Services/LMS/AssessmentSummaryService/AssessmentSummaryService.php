<?php

namespace App\Services\LMS\AssessmentSummaryService;

use App\Models\SchoolAssessment;
use App\Models\SchoolAssessmentQuestion;
use App\Models\StudentAssessmentAnswer;
use App\Models\StudentAssessmentAttempt;
use App\Models\StudentAssessmentSummary;
use App\Models\StudentProjectSubmission;
use App\Models\SubjectPassingGradeCriteria;
use Illuminate\Support\Facades\Cache;

class AssessmentSummaryService
{
    // private function update student assessment summary
    private function truncateScore($score): float
    {
        return floor((float) $score * 100) / 100;
    }

    public function updateStudentAssessmentSummary($studentId, $assessment)
    {
        // cari root assessment
        $rootAssessmentId = $assessment->parent_assessment_id ? $assessment->parent_assessment_id : $assessment->id;

        // ambil semua assessment turunan (main, remedial, susulan, dll)
        $assessments = SchoolAssessment::where(function ($q) use ($rootAssessmentId) {
            $q->where('id', $rootAssessmentId)->orWhere('parent_assessment_id', $rootAssessmentId);
        })->get();

        // ambil semua jawaban siswa dari semua attempt
        $answers = StudentAssessmentAnswer::where('student_id', $studentId)->whereIn('school_assessment_id', $assessments->pluck('id'))->get()->groupBy('school_assessment_id');

        // attempt yang ditandai 'cheating' selalu bernilai 0
        $cheatingAssessmentIds = StudentAssessmentAttempt::where('student_id', $studentId)
            ->whereIn('school_assessment_id', $assessments->pluck('id'))
            ->where('status', 'cheating')
            ->pluck('school_assessment_id');

        $mainScore = null;
        $susulanScore = null;
        $remedialScores = [];
        $pengayaanScore = null;
        $lastRemedialAssessmentId = null;
        $assessmentScores = [];

        foreach ($assessments as $a) {

            $studentAnswers = $answers->get($a->id);

            // ambil project submission
            $project = StudentProjectSubmission::where('student_id', $studentId)->where('school_assessment_id', $a->id)->orderByDesc('id')->first();

            $attempt = StudentAssessmentAttempt::where('student_id', $studentId)->where('school_assessment_id', $a->id)->first();

            // skip kalau tidak ada answer, tidak ada project, DAN exam belum selesai.
            // Attempt yang sudah difinalisasi tanpa jawaban tetap dicatat (nilai 0).
            if ((!$studentAnswers || $studentAnswers->count() === 0) && !$project && (!$attempt || !$attempt->finalized_at)) {
                continue;
            }

            // default score dari answer berdasarkan total bobot soal
            $totalScore = 0;

            if ($studentAnswers && $studentAnswers->count() > 0) {

                $totalEarnedWeight = (float) $studentAnswers->sum('question_score');

                // pembagi = semua soal yang ditawarkan pada exam ini, bukan hanya yang
                // terjawab: soal tanpa baris jawaban dihitung 0 (tidak ada lagi baris
                // jawaban kosong yang di-bulk-insert saat /end)
                $totalQuestionWeight = (float) $this->offeredQuestionsQuery($a, $studentId)->sum('question_weight');

                $totalScore = $totalQuestionWeight > 0 ? $this->truncateScore(($totalEarnedWeight / $totalQuestionWeight) * 100) : 0;
            }

            // override kalau project
            if ($a->SchoolAssessmentType->AssessmentMode->code === 'project') {
                $totalScore = $project->score !== null ? $this->truncateScore($project->score) : 0;
            }

            // attempt 'cheating' tidak dapat nilai (guru bisa mengatasi via remedial)
            if ($cheatingAssessmentIds->contains($a->id)) {
                $totalScore = 0;
            }

            // simpan score berdasarkan assessment ID
            $assessmentScores[$a->id] = $totalScore;

            switch ($a->assessment_category) {
                case 'main':
                    $mainScore = $totalScore;
                    break;

                case 'susulan':
                    $susulanScore = $totalScore;
                    break;

                case 'remedial':
                    $remedialScores[] = [
                        'score' => $totalScore,
                        'assessment_id' => $a->id,
                    ];
                    break;

                case 'pengayaan':
                    $pengayaanScore = $totalScore;
                    break;
            }
        }

        // ambil remedial terakhir
        $lastRemedialScore = null;

        if (!empty($remedialScores)) {
            $currentAssessmentCategory = strtolower($assessment->assessment_category ?? '');

            if ($currentAssessmentCategory === 'remedial') {

                $currentScore = $assessmentScores[$assessment->id] ?? null;

                if ($currentScore !== null) {
                    $lastRemedialScore = $currentScore;
                    $lastRemedialAssessmentId = $assessment->id;
                }

            } else {

                $lastRemedial = collect($remedialScores)
                    ->sortByDesc('assessment_id')
                    ->first();

                if ($lastRemedial) {
                    $lastRemedialScore = $lastRemedial['score'];
                    $lastRemedialAssessmentId = $lastRemedial['assessment_id'];
                }
            }
        }

        $kelasId = $assessment->SchoolClass?->kelas_id;

        $kkm = SubjectPassingGradeCriteria::where('mapel_id', $assessment->mapel_id)->where('kelas_id', $kelasId)->where('school_partner_id', $assessment->school_partner_id)->value('kkm_value');

        // tentukan final score
        $finalScore = null;
        $scoreSource = null;

        // kumpulkan semua score
        $allScores = collect([
            ['type' => 'main', 'score' => $mainScore],
            ['type' => 'susulan', 'score' => $susulanScore],
            ['type' => 'remedial', 'score' => $lastRemedialScore],
        ])->filter(fn($item) => $item['score'] !== null);

        // ambil yang tertinggi
        $best = $allScores->sortByDesc('score')->first();

        if ($best && is_numeric($best['score'])) {

            $scoreSource = $best['type'];

            $bestScore = $this->truncateScore($best['score']);

            if ($best['type'] === 'remedial' && $kkm !== null && $bestScore >= $kkm) {
                $finalScore = $this->truncateScore($kkm);
            } else {
                $finalScore = $bestScore;
            }
        }

        // upsert summary
        StudentAssessmentSummary::updateOrCreate(
            [
                'student_id' => $studentId,
                'root_assessment_id' => $rootAssessmentId,
            ],
            [
                // semua score distandarisasi 2 decimal
                'main_score' => $mainScore !== null ? $this->truncateScore($mainScore) : null,
                'susulan_score' => $susulanScore !== null ? $this->truncateScore($susulanScore) : null,
                'last_remedial_score' => $lastRemedialScore !== null ? $this->truncateScore($lastRemedialScore) : null,
                'pengayaan_score' => $pengayaanScore !== null ? $this->truncateScore($pengayaanScore) : null,

                'final_score' => $finalScore !== null ? $this->truncateScore($finalScore) : null,
                'score_source' => $scoreSource,
                'remedial_count' => count($remedialScores),
                'last_remedial_assessment_id' => $lastRemedialAssessmentId,
            ]
        );
    }

    /**
     * Soal yang ditawarkan pada satu exam: exam biasa memakai soalnya sendiri;
     * remedial/susulan mengambil soal dari parent (remedial dibatasi bank soal
     * salah yang jadi dasar examnya). Sama dengan aturan /end sebelum auto-fill
     * jawaban kosong dihapus.
     */
    private function offeredQuestionsQuery(SchoolAssessment $assessment, int $studentId)
    {
        $category = strtolower($assessment->assessment_category ?? '');
        $sourceId = in_array($category, ['remedial', 'susulan']) && $assessment->parent_assessment_id
            ? $assessment->parent_assessment_id
            : $assessment->id;

        $query = SchoolAssessmentQuestion::where('school_assessment_id', $sourceId);

        if ($category === 'remedial' && $assessment->parent_assessment_id) {
            $wrongBankIds = $this->remedialWrongBankIds($assessment, $studentId);
            if (!empty($wrongBankIds)) {
                $query->whereIn('question_bank_id', $wrongBankIds);
            }
        }

        return $query;
    }

    /**
     * Bank soal salah dari exam sebelumnya dalam rantai remedial (sumber yang sama
     * dengan StudentAssessmentExamController saat membangun exam remedial).
     */
    private function remedialWrongBankIds(SchoolAssessment $assessment, int $studentId): array
    {
        $chain = SchoolAssessment::where(function ($q) use ($assessment) {
            $q->where('id', $assessment->parent_assessment_id)->orWhere('parent_assessment_id', $assessment->parent_assessment_id);
        })->where(function ($q) use ($assessment, $studentId) {
            $q->where('id', $assessment->id)->orWhereHas('StudentAssessmentAnswer', function ($q) use ($studentId) {
                $q->where('student_id', $studentId);
            });
        })->orderBy('start_date')->pluck('id')->toArray();

        $currentIndex = array_search($assessment->id, $chain);
        if ($currentIndex === false || $currentIndex === 0) {
            return [];
        }

        $previousId = $chain[$currentIndex - 1];

        return Cache::get("assessment-remedial-wrong-{$studentId}-prev-{$previousId}", []);
    }
}