<?php

namespace App\Http\Controllers;

use App\Models\Mapel;

class StudentSubjectProgressController extends Controller
{
    public function index($role, $schoolName, $schoolId, $curriculumId, $mapelId)
    {
        return view('features.lms.student.components.subject-header-progress', compact('role', 'schoolName', 'schoolId', 'curriculumId', 'mapelId'));
    }

    public function data($role, $schoolName, $schoolId, $curriculumId, $mapelId)
    {
        $getMapel = Mapel::with(['TeacherMapel' => function ($q) {
            $q->where('is_active', 1);
            
            },'TeacherMapel.UserAccount.SchoolStaffProfile'])->where('id', $mapelId)->first();

        return response()->json([
            'mapel' => $getMapel,
        ]);
    }
}
