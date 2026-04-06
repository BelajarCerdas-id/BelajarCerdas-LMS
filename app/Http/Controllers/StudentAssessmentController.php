<?php

namespace App\Http\Controllers;

use App\Models\SchoolAssessment;
use App\Models\SchoolAssessmentQuestion;
use App\Models\SchoolAssessmentType;
use App\Models\StudentAssessmentAnswer;
use App\Models\StudentAssessmentSummary;
use App\Models\StudentProjectSubmission;
use App\Models\StudentSchoolClass;
use App\Models\SubjectPassingGradeCriteria;
use Illuminate\Support\Facades\Auth;

class StudentAssessmentController extends Controller
{
    public function studentPreviewAssessment($role, $schoolName, $schoolId, $curriculumId, $mapelId, $assessmentTypeId, $mode = null, $parentAssessmentId = null)
    {
        $getAssessmentType = SchoolAssessmentType::where('id', $assessmentTypeId)->first();

        $getAssessment = SchoolAssessment::where('assessment_type_id', $assessmentTypeId)->first();

        return view('features.lms.student.assessment.student-preview-assessment-schedule', compact('role', 'schoolName', 'schoolId', 'curriculumId', 
            'mapelId', 'assessmentTypeId', 'mode', 'parentAssessmentId', 'getAssessmentType', 'getAssessment'));
    }

    public function loadStudentPreviewAssessment($role, $schoolName, $schoolId, $curriculumId, $mapelId, $assessmentTypeId, $semester, $mode = null, $parentAssessmentId = null) 
    {
        $user = Auth::user();

        $assessment = null;
        $rootAssessmentId = null;

        if ($parentAssessmentId) {
            $assessment = SchoolAssessment::find($parentAssessmentId);

            $rootAssessmentId = $assessment->parent_assessment_id ?? $assessment->id;
        }
        
        $assessments = SchoolAssessment::with(['SchoolAssessmentType', 'SchoolClass', 'Mapel'])->whereHas('SchoolClass.StudentSchoolClass', function ($query) use ($user) {
            $query->where('student_id', $user->id)->where('student_class_status', 'active');
        })->where('assessment_type_id', $assessmentTypeId)->where('semester', $semester)->where('mapel_id', $mapelId)->when(!$mode || $mode === 'main', function ($query) {
                $query->whereNull('parent_assessment_id');
            })
            ->when($mode && $mode !== 'main' && $parentAssessmentId, function ($query) use ($mode, $rootAssessmentId) {
                $query->where('assessment_category', $mode)->where('parent_assessment_id', $rootAssessmentId);
            })
        ->orderBy('created_at', 'desc')->get();

        if (!$assessments) {
            return response()->json(['data' => null]);
        }

        $schoolYear = $assessments->first()?->SchoolClass?->tahun_ajaran;

        $studentClass = StudentSchoolClass::where('student_id', $user->id)->where('student_class_status', 'active')->where(function ($q) {
            $q->whereNull('academic_action')->orWhere('academic_action', '');
        })->first();

        $kkm = SubjectPassingGradeCriteria::where('mapel_id', $mapelId)->where('kelas_id', $studentClass?->SchoolClass->kelas_id)->where('school_year', $schoolYear)->latest()->value('kkm_value');

        if (!$rootAssessmentId) {
            $rootAssessmentId = $assessments->first()?->id;
        }

        $summary = StudentAssessmentSummary::where('student_id', $user->id)->where('root_assessment_id', $rootAssessmentId ?? null)->first();

        $finalScore = null;

        $finalScore = null;
        $hasPassed = false;

        if ($summary) {

            $mainScore = $summary->main_score;
            $susulanScore = $summary->susulan_score;
            $remedialScore = $summary->last_remedial_score;

            // ambil nilai terbaik (bukan urutan fallback)
            $finalScore = max($mainScore ?? 0, $susulanScore ?? 0, $remedialScore ?? 0
            );

            // FLAG PERNAH LULUS ATAU TIDAK
            $hasPassed = ($mainScore !== null && $mainScore >= $kkm) || ($susulanScore !== null && $susulanScore >= $kkm) || ($remedialScore !== null && $remedialScore >= $kkm);
        }

        $data = $assessments->filter(function ($assessment) use ($summary, $finalScore, $kkm, $hasPassed) {

            $category = strtolower($assessment->assessment_category ?? 'main');

            // MAIN -> selalu tampil
            if ($category === 'main') return true;

            if (!$summary) return false;

            // SUSULAN -> jika belum ada nilai main
            if ($category === 'susulan') {
                return is_null($summary->main_score);
            }

            // REMEDIAL
            if ($category === 'remedial') {

                if (!$summary) return false;

                $lastRemedialId = $summary->last_remedial_assessment_id;

                // SUDAH PERNAH LULUS -> LOCK (tidak boleh remedial lagi)
                if ($hasPassed) {
                    return $assessment->id <= $lastRemedialId; // hanya history
                }

                // BELUM LULUS -> lanjut chain
                if (!$lastRemedialId) return true;

                return $assessment->id > $lastRemedialId;
            }

            // PENGAYAAN -> kalau lulus
            if ($category === 'pengayaan') {
                return $hasPassed;
            }

            return true;

        })->values()->map(function ($assessment) use ($user) {

            $totalQuestions = SchoolAssessmentQuestion::where('school_assessment_id', $assessment->id)->count();

            $totalAnswers = StudentAssessmentAnswer::where('student_id', $user->id)->where('school_assessment_id', $assessment->id)->where('status_answer', 'submitted')->count();

            $submission = StudentProjectSubmission::where('student_id', $user->id)->where('school_assessment_id', $assessment->id)->first();

            return [
                'id' => $assessment->id,
                'title' => $assessment->title,
                'start_date' => $assessment->start_date?->format('Y-m-d H:i'),
                'end_date' => $assessment->end_date?->format('Y-m-d H:i'),
                'school_assessment_type' => $assessment->SchoolAssessmentType,
                'assessment_mode' => $assessment->SchoolAssessmentType->assessmentMode->code ?? 'exam',
                'mapel' => $assessment->Mapel->mata_pelajaran ?? '-',
                'description' => $assessment->description ?? '-',
                'assessment_value_file' => $assessment->assessment_value_file,
                'assessment_original_filename' => $assessment->assessment_original_filename,
                'school_class' => $assessment->SchoolClass,
                'total_questions' => $totalQuestions,
                'total_answers' => $totalAnswers,
                'show_score' => $assessment->show_score,

                'student_submitted' => $submission ? true : false,
                'student_submission_file' => $submission->file_path ?? null,
                'student_submission_filename' => $submission->original_filename ?? null,
                'student_submission_text' => $submission->text_answer ?? null,
                'student_grading_status_submission' => $submission->grading_status ?? null,
                'student_score_submission' => $submission->score ?? null,
                'teacher_feedback_submission' => $submission->teacher_feedback ?? null,

                'resultTestHref' => '/lms/:role/:schoolName/:schoolId/curriculum/:curriculumId/subject/:mapelId/learning/assessment/:assessmentTypeId/semester/:semester/assessment/:assessmentId/result-test',
                'projectResultTestHref' => '/lms/:role/:schoolName/:schoolId/curriculum/:curriculumId/subject/:mapelId/learning/assessment/:assessmentTypeId/semester/:semester/assessment/:assessmentId/project-result'
            ];
        });

        return response()->json([
            'data' => $data,
        ]);
    }

    public function checkAssessmentStatus($assessmentId)
    {
        $assessment = SchoolAssessment::findOrFail($assessmentId);

        return response()->json([
            'start_date' => $assessment->start_date ? $assessment->start_date->format('Y-m-d H:i') : null,
            'end_date' => $assessment->end_date ? $assessment->end_date->format('Y-m-d H:i') : null,
        ]);
    }
}
