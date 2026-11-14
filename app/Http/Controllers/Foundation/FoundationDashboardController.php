<?php

namespace App\Http\Controllers\Foundation;

use App\Http\Controllers\Controller;
use App\Models\ParentProfile;
use App\Models\SchoolFoundationFinanceAccess;
use App\Models\SchoolPartner;
use App\Models\SchoolStaffProfile;
use App\Models\StudentProfile;

class FoundationDashboardController extends Controller
{
    public function index($role, $foundationId = null)
    {
        $financeAccessLink = collect();

        if ($foundationId) {
            $financeAccessLink = SchoolFoundationFinanceAccess::with(['SchoolPartner'])->whereHas('SchoolPartner', function ($query) use ($foundationId) {
                $query->where('school_foundation_id', $foundationId);
            })->get();
        }
        
        return view('features.lms.foundation.dashboard', compact('role', 'foundationId', 'financeAccessLink'));
    }

    public function dashboardKPI($role, $foundationId = null)
    {
        if ($foundationId) {
            $totalSchool = SchoolPartner::where('school_foundation_id', $foundationId)->count();
    
            $totalTeacher = SchoolStaffProfile::whereHas('SchoolPartner', function ($q) use ($foundationId) {
                $q->where('school_foundation_id', $foundationId);
    
            })->whereHas('UserAccount', function ($q) {
                $q->where('role', 'Guru')->where('status_akun', 'aktif');
            })->count();
    
            $totalStudent = StudentProfile::whereHas('SchoolPartner', function ($q) use ($foundationId) {
                $q->where('school_foundation_id', $foundationId);
    
            })->whereHas('UserAccount', function ($q) {
                $q->where('status_akun', 'aktif');
            })->count();
    
            $totalParent = ParentProfile::whereHas('SchoolPartner', function ($q) use ($foundationId) {
                $q->where('school_foundation_id', $foundationId);
    
            })->whereHas('UserAccount', function ($q) {
                $q->where('status_akun', 'aktif');
            })->count();
        }

        return response()->json([
            'total_school'  => $totalSchool,
            'total_teacher' => $totalTeacher,
            'total_student' => $totalStudent,
            'total_parent'  => $totalParent,
        ]);
    }
}