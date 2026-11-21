<?php

namespace App\Http\Controllers;

use App\Models\LmsContent;
use App\Models\SchContract;
use App\Models\SchoolAssessment;
use App\Models\SchoolPartner;
use App\Models\SchoolQuestionBank;
use App\Models\StudentAssessmentAttempt;
use App\Models\UserAccount;
use App\Models\UserActivity;
use Illuminate\Support\Facades\DB;

class AdministratorDashboardController extends Controller
{
    public function index($role)
    {
        $today = now()->toDateString();
        $monthAhead = now()->addDays(30)->toDateString();

        // shortcut: aggregates run on every admin dashboard load; cache when admin traffic or activity volume grows.
        $schools = SchoolPartner::count();

        $activeContracts = SchContract::where('status', 'active')
            ->whereDate('start_contract', '<=', $today)
            ->whereDate('end_contract', '>=', $today)
            ->count();

        $expiringContracts = SchContract::where('status', 'active')
            ->whereDate('start_contract', '<=', $today)
            ->whereBetween('end_contract', [$today, $monthAhead])
            ->count();

        $totalUsers = UserAccount::count();
        $activeUsers = UserAccount::where('status_akun', 'aktif')->count();

        $onlineUsers = UserActivity::where('created_at', '>=', now()->subMinutes(15))
            ->distinct()->count('user_id');

        $activitiesToday = UserActivity::whereDate('created_at', $today)->count();

        $activeSchoolsToday = UserActivity::whereDate('created_at', $today)
            ->whereNotNull('school_partner_id')
            ->distinct()->count('school_partner_id');

        $roleCounts = UserAccount::select('role')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('role')
            ->orderByDesc('total')
            ->get();

        $attemptsToday = StudentAssessmentAttempt::whereDate('created_at', $today)->count();
        $blockedAttempts = StudentAssessmentAttempt::where('status', 'cheating')->count();
        $inProgress = StudentAssessmentAttempt::where('status', 'in_progress')->count();

        $contents = LmsContent::where('is_active', 1)->count();
        $questions = SchoolQuestionBank::where('is_active', 1)->count();
        $assessments = SchoolAssessment::count();

        $recentActivities = DB::table('user_activities as ua')
            ->leftJoin('user_accounts as u', 'u.id', '=', 'ua.user_id')
            ->leftJoin('school_partners as sp', 'sp.id', '=', 'ua.school_partner_id')
            ->select('ua.role', 'ua.module', 'ua.sub_module', 'ua.action', 'ua.created_at', 'u.email', 'sp.nama_sekolah')
            ->orderByDesc('ua.created_at')
            ->limit(8)
            ->get();

        $attentionSchools = DB::table('school_partners as sp')
            ->leftJoin('sch_contracts as c', function ($join) use ($today) {
                $join->on('c.school_partner_id', '=', 'sp.id')
                    ->where('c.status', '=', 'active')
                    ->whereDate('c.end_contract', '>=', $today);
            })
            ->select(
                'sp.id',
                'sp.nama_sekolah',
                'sp.npsn',
                DB::raw('COUNT(c.id) as active_contracts'),
                DB::raw('MIN(c.end_contract) as next_end_contract')
            )
            ->groupBy('sp.id', 'sp.nama_sekolah', 'sp.npsn')
            ->havingRaw('COUNT(c.id) = 0 OR MIN(c.end_contract) <= ?', [$monthAhead])
            ->orderBy('next_end_contract')
            ->limit(5)
            ->get();

        return view('features.lms.administrator.dashboard', compact(
            'role',
            'schools',
            'activeContracts',
            'expiringContracts',
            'totalUsers',
            'activeUsers',
            'onlineUsers',
            'activitiesToday',
            'activeSchoolsToday',
            'roleCounts',
            'attemptsToday',
            'blockedAttempts',
            'inProgress',
            'contents',
            'questions',
            'assessments',
            'recentActivities',
            'attentionSchools'
        ));
    }
}
