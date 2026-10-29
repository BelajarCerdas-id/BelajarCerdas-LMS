<?php

namespace App\Http\Controllers\Lms\MajorSubject;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MajorSubjectController extends Controller
{
    public function index($role, $schoolName, $schoolId)
    {
        return view('errors.404');
    }

    public function loadMajorSubjectForm(Request $request, $role, $schoolName, $schoolId)
    {
        return response()->json(['data' => []]);
    }

    public function majorSubjectSubmitForm(Request $request, $role, $schoolName, $schoolId)
    {
        return response()->json(['status' => 'error', 'message' => 'Not implemented'], 501);
    }
}
