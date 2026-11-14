<?php

namespace App\Http\Controllers;

use App\Models\SchoolAssessment;
use App\Models\StudentAssessmentSummary;
use App\Models\TeacherMapel;
use App\Models\UserAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GradebookAssessmentController extends Controller
{
    public function teacherGradebookAssessmentPreview($role, $schoolName, $schoolId, $subjectTeacherId, $assessmentTypeId, $studentId, $semester)
    {
        return view('features.lms.teacher.gradebook.teacher-gradebook-assessment-preview', compact('role', 'schoolName', 'schoolId', 'subjectTeacherId', 
        'assessmentTypeId', 'studentId', 'semester'));
    }

    public function paginateTeacherGradebookAssessment(Request $request, $role, $schoolName, $schoolId, $subjectTeacherId, $assessmentTypeId, $studentId, $semester)
    {
        $student = UserAccount::with('StudentProfile')->where('id', $studentId)->first();
        
        $teacherMapel = TeacherMapel::with(['Mapel', 'SchoolClass'])->where('id', $subjectTeacherId)->firstOrFail();
        
        $assessments = SchoolAssessment::where('assessment_type_id', $assessmentTypeId)->where('school_class_id', $teacherMapel->school_class_id)
            ->where('mapel_id', $teacherMapel->mapel_id)->where('semester', $semester)->where('assessment_category', 'main')->latest()->get();

        $summaries = StudentAssessmentSummary::where('student_id', $studentId)->get()->keyBy('root_assessment_id');

        $data = $assessments->map(function ($assessment) use ($summaries, $student) {
            $summary = $summaries[$assessment->id] ?? null;

            return [
                'assessment_id' => $assessment->id,
                'title' => $assessment->title,
                'start_date' => $assessment->start_date,
                'end_date' => $assessment->end_date,
                'assessment_type' => $assessment->SchoolAssessmentType->name,
                'student_name' => $student->StudentProfile->nama_lengkap ?? '-',
                'final_score' => $summary ? $summary->final_score : null,
            ];
        });

        return response()->json([
            'data' => $data,
            'teacherMapel' => $teacherMapel
        ]);
    }

    public function bulkUpdateScore(Request $request, $role, $schoolName, $schoolId, $subjectTeacherId, $assessmentTypeId, $studentId, $semester)
    {
        $rules = [
            'data' => 'required|array',
            'data.*.assessment_id' => 'required|exists:school_assessments,id',
            'data.*.final_score' => 'required|numeric|min:0|max:100'
        ];

        $messages = [
            'data.*.final_score.required' => 'Nilai wajib diisi',
            'data.*.final_score.numeric' => 'Nilai harus berupa angka',
            'data.*.final_score.max' => 'Nilai maksimal 100',
            'data.*.final_score.min' => 'Nilai minimal 0',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        foreach ($request->data as $item) {
            StudentAssessmentSummary::updateOrCreate(
                [
                    'student_id' => $studentId,
                    'root_assessment_id' => $item['assessment_id'],
                ],
                [
                    'final_score' => $item['final_score'],
                    'score_source' => 'manual'
                ]
            );
        }

        return response()->json([
            'message' => 'Nilai berhasil diubah'
        ]);
    }
}
