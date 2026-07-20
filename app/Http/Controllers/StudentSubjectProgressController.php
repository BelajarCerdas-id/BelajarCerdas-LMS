<?php

namespace App\Http\Controllers;

use App\Models\Mapel;
use App\Models\StudentSchoolClass;
use Illuminate\Support\Facades\Auth;

class StudentSubjectProgressController extends Controller
{
    public function index($role, $schoolName, $schoolId, $curriculumId, $mapelId)
    {
        return view('features.lms.student.components.subject-header-progress', compact('role', 'schoolName', 'schoolId', 'curriculumId', 'mapelId'));
    }

    public function data($role, $schoolName, $schoolId, $curriculumId, $mapelId)
    {
        $studentSchoolClass = StudentSchoolClass::where('student_id', Auth::id())->where('student_class_status', 'active')->first();

        if (!$studentSchoolClass) {
            return response()->json([
                'mapel' => null,
            ]);
        }

        $schoolClassId = $studentSchoolClass->school_class_id;

        $getMapel = Mapel::with(['TeacherMapel.UserAccount.SchoolStaffProfile', 'TeacherMapel' => function ($q) use ($schoolClassId) {
            $q->where('is_active', 1)->where('school_class_id', $schoolClassId);
        }])->where('id', $mapelId)->first();

        return response()->json([
            'mapel' => $getMapel,
        ]);
    }
}
