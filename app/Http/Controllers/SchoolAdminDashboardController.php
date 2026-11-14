<?php

namespace App\Http\Controllers;

class SchoolAdminDashboardController extends Controller
{
    public function index($role, $schoolName, $schoolId)
    {
        return view('Features.Lms.SchoolAdmin.Dashboard', compact('role', 'schoolName', 'schoolId'));
    }
}
