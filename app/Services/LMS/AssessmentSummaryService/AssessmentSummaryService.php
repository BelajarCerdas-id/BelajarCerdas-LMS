<?php

namespace App\Services\LMS\AssessmentSummaryService;

use App\Models\SchoolAssessment;
use App\Models\SchoolAssessmentQuestion;
use App\Models\StudentAssessmentAnswer;
use App\Models\StudentAssessmentSummary;
use App\Models\StudentProjectSubmission;
use App\Models\SubjectPassingGradeCriteria;

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

            // skip kalau tidak ada answer DAN tidak ada project
            if ((!$studentAnswers || $studentAnswers->count() === 0) && !$project) {
                continue;
            }

            // default score dari answer berdasarkan total bobot soal
            $totalScore = 0;

            if ($studentAnswers && $studentAnswers->count() > 0) {

                $questionIds = $studentAnswers->pluck('school_assessment_question_id')->unique()->values();

                $totalEarnedWeight = (float) $studentAnswers->sum('question_score');

                $totalQuestionWeight = (float) SchoolAssessmentQuestion::whereIn('id', $questionIds)->sum('question_weight');

                $totalScore = $totalQuestionWeight > 0 ? $this->truncateScore(($totalEarnedWeight / $totalQuestionWeight) * 100) : 0;
            }

            // override kalau project
            if ($a->SchoolAssessmentType->AssessmentMode->code === 'project') {
                $totalScore = $project->score !== null ? $this->truncateScore($project->score) : 0;
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
}