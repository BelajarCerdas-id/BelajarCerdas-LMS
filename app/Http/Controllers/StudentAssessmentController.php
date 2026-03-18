<?php

namespace App\Http\Controllers;

use App\Models\SchoolAssessment;
use App\Models\SchoolAssessmentQuestion;
use App\Models\SchoolAssessmentType;
use App\Models\StudentAssessmentAnswer;
use App\Models\StudentProjectSubmission;
use Illuminate\Support\Facades\Auth;

class StudentAssessmentController extends Controller
{
    public function studentPreviewAssessment($role, $schoolName, $schoolId, $curriculumId, $mapelId, $assessmentTypeId)
    {
        $getAssessmentType = SchoolAssessmentType::where('id', $assessmentTypeId)->first();

        $getAssessment = SchoolAssessment::where('assessment_type_id', $assessmentTypeId)->first();

        return view('features.lms.student.assessment.student-preview-assessment-schedule', compact('role', 'schoolName', 'schoolId', 'curriculumId', 
            'mapelId', 'assessmentTypeId', 'getAssessmentType', 'getAssessment'));
    }

    public function loadStudentPreviewAssessment($role, $schoolName, $schoolId, $curriculumId, $mapelId, $assessmentTypeId, $semester) 
    {
        $user = Auth::user();

        $assessments = SchoolAssessment::with(['SchoolAssessmentType', 'SchoolClass', 'Mapel'])
            ->whereHas('SchoolClass.StudentSchoolClass', function ($query) use ($user) {
                $query->where('student_id', $user->id)->where('student_class_status', 'active');
            })->where('assessment_type_id', $assessmentTypeId)->where('semester', $semester)->where('mapel_id', $mapelId)->get();

        if (!$assessments) {
            return response()->json(['data' => null]);
        }

        $data = $assessments->map(function ($assessment) use ($user) {

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
