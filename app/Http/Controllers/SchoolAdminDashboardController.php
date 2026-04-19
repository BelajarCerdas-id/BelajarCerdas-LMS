<?php

namespace App\Http\Controllers;

class SchoolAdminDashboardController extends Controller
{
    public function index($role, $schoolName, $schoolId)
    {
        return view('features.lms.school-admin.dashboard', compact('role', 'schoolName', 'schoolId'));
    }
}
