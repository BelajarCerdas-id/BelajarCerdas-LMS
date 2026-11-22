<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ExtracurricularController extends Controller
{
    public function index($role, $schoolName, $schoolId)
    {
        return view('errors.404');
    }

    public function downloadTemplate()
    {
        return response()->json(['message' => 'Not implemented'], 501);
    }

    public function paginate(Request $request, $role, $schoolName, $schoolId)
    {
        return response()->json(['data' => []]);
    }

    public function kelengkapanView($role, $schoolName, $schoolId)
    {
        return view('errors.404');
    }

    public function kelengkapanDetail($role, $schoolName, $schoolId, $extracurricularId)
    {
        return view('errors.404');
    }

    public function store(Request $request, $role, $schoolName, $schoolId)
    {
        return response()->json(['status' => 'error', 'message' => 'Feature disabled'], 400);
    }

    public function update(Request $request, $role, $schoolName, $schoolId, $id)
    {
        return response()->json(['status' => 'error', 'message' => 'Feature disabled'], 400);
    }

    public function destroy($role, $schoolName, $schoolId, $id)
    {
        return response()->json(['status' => 'error', 'message' => 'Feature disabled'], 400);
    }

    public function importExcel(Request $request, $role, $schoolName, $schoolId)
    {
        return response()->json(['status' => 'error', 'message' => 'Feature disabled'], 400);
    }

    public function importMember(Request $request, $role, $schoolName, $schoolId, $extracurricularId)
    {
        return response()->json(['status' => 'error', 'message' => 'Feature disabled'], 400);
    }

    public function uploadAttendance(Request $request, $role, $schoolName, $schoolId, $extracurricularId)
    {
        return response()->json(['status' => 'error', 'message' => 'Feature disabled'], 400);
    }

    public function detail($role, $schoolName, $schoolId, $extracurricularId)
    {
        return view('errors.404');
    }

    public function storeMember(Request $request, $role, $schoolName, $schoolId, $extracurricularId)
    {
        return response()->json(['status' => 'error', 'message' => 'Feature disabled'], 400);
    }

    public function downloadMemberTemplate($role, $schoolName, $schoolId, $extracurricularId)
    {
        return response()->json(['message' => 'Not implemented'], 501);
    }

    public function destroyMember($role, $schoolName, $schoolId, $extracurricularId, $memberId)
    {
        return response()->json(['status' => 'error', 'message' => 'Feature disabled'], 400);
    }
}
