<?php

namespace App\Http\Controllers\Lms\StudentSubjectSelection;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StudentSubjectSelectionController extends Controller
{
    public function index($role, $schoolName, $schoolId)
    {
        return view('errors.404');
    }

    public function loadStudentSubjectSelectionForm(Request $request, $role, $schoolName, $schoolId)
    {
        return response()->json(['data' => []]);
    }

    public function studentSubjectSelectionSubmitForm(Request $request, $role, $schoolName, $schoolId)
    {
        return response()->json(['status' => 'error', 'message' => 'Not implemented'], 501);
    }
}
