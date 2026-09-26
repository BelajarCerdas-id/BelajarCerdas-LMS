<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use App\Models\SchoolPartner;
use App\Models\UserAccount;
use App\Models\UserActivity;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class UserAnalyticsController extends Controller
{
    /**
     * Pastikan otorisasi ketat: HANYA Administrator Utama yang boleh mengakses
     */
    protected function authorizeAdmin(): void
    {
        if (!Auth::check() || !in_array(strtolower((string) Auth::user()->role), ['administrator', 'admin'])) {
            abort(403, 'Akses ditolak. Halaman analytics hanya dapat diakses oleh Administrator Utama.');
        }
    }

    /**
     * Halaman Utama Sub-Page Analytics
     */
    public function index(Request $request, string $role): View
    {
        $this->authorizeAdmin();

        $schools = SchoolPartner::select('id', 'nama_sekolah', 'npsn', 'jenjang_sekolah', 'logo')
            ->orderBy('nama_sekolah', 'asc')
            ->get();

        $roles = [
            'Siswa',
            'Guru',
            'Kepala Sekolah',
            'Wakil Kepala Sekolah',
            'Wakil Kesiswaan',
            'Admin Sekolah',
            'Orang Tua',
            'Administrator',
            'Finance',
            'Yayasan'
        ];

        $modules = [
            'Library',
            'Assessment & Ujian',
            'Presensi & Kehadiran',
            'Agenda Guru',
            'Buku Nilai',
            'Bank Soal',
            'Bank Materi',
            'Simulasi TKA',
            'Kurikulum & Silabus',
            'Manajemen Pengguna',
            'Mitra Sekolah',
            'Manajemen Yayasan',
            'Keuangan',
            'Dashboard',
            'Pengaturan Akun'
        ];

        return view('features.lms.administrator.analytics', compact('role', 'schools', 'roles', 'modules'));
    }

    /**
     * JSON KPI Cards: Pengguna Online, Total Aktivitas Hari Ini, Sekolah Aktif, Top Modul
     */
    public function kpiSummary(Request $request): JsonResponse
    {
        $this->authorizeAdmin();

        $fifteenMinutesAgo = now()->subMinutes(15);
        $fifteenMinutesTimestamp = $fifteenMinutesAgo->timestamp;

        // 1. Pengguna Online (dari database sessions dan recent activities)
        $onlineFromSessions = 0;
        try {
            $onlineFromSessions = DB::table('sessions')
                ->where('last_activity', '>=', $fifteenMinutesTimestamp)
                ->whereNotNull('user_id')
                ->distinct('user_id')
                ->count('user_id');
        } catch (\Throwable $e) {
            // Jika tabel sessions tidak menggunakan database driver
            $onlineFromSessions = 0;
        }

        $onlineFromActivities = UserActivity::where('created_at', '>=', $fifteenMinutesAgo)
            ->distinct('user_id')
            ->count('user_id');

        $onlineUsersCount = max($onlineFromSessions, $onlineFromActivities);

        // 2. Total Aktivitas Hari Ini
        $activitiesToday = UserActivity::today()->count();

        // 3. Total Pengguna Unik Aktif Hari Ini
        $activeUsersToday = UserActivity::today()->distinct('user_id')->count('user_id');

        // 4. Sekolah Aktif Hari Ini
        $activeSchoolsToday = UserActivity::today()
            ->whereNotNull('school_partner_id')
            ->distinct('school_partner_id')
            ->count('school_partner_id');

        // 5. Modul Paling Populer Hari Ini
        $topModuleToday = UserActivity::today()
            ->select('module', DB::raw('COUNT(*) as total'))
            ->groupBy('module')
            ->orderByDesc('total')
            ->first();

        // 6. Role Paling Aktif Hari Ini
        $topRoleToday = UserActivity::today()
            ->select('role', DB::raw('COUNT(*) as total'))
            ->groupBy('role')
            ->orderByDesc('total')
            ->first();

        // 7. Total Akun Terdaftar
        $totalRegisteredUsers = UserAccount::count();

        return response()->json([
            'status' => 'success',
            'data'   => [
                'online_users_now'       => $onlineUsersCount,
                'activities_today'       => $activitiesToday,
                'active_users_today'     => $activeUsersToday,
                'active_schools_today'   => $activeSchoolsToday,
                'total_registered_users' => $totalRegisteredUsers,
                'top_module'             => $topModuleToday ? $topModuleToday->module : 'Belum Ada',
                'top_module_count'       => $topModuleToday ? $topModuleToday->total : 0,
                'top_role'               => $topRoleToday ? $topRoleToday->role : 'Belum Ada',
                'top_role_count'         => $topRoleToday ? $topRoleToday->total : 0,
            ]
        ]);
    }

    /**
     * JSON Data Chart Visualisasi (Trends, Modul, Role)
     */
    public function chartData(Request $request): JsonResponse
    {
        $this->authorizeAdmin();

        $period    = $request->get('period', 'today'); // today, 7days, 30days
        $schoolId  = $request->get('school_id');
        $role      = $request->get('role');
        $module    = $request->get('module');

        // Base query dengan filter
        $baseQuery = UserActivity::query();

        if (!empty($schoolId) && $schoolId !== 'all') {
            $baseQuery->where('school_partner_id', $schoolId);
        }
        if (!empty($role) && $role !== 'all') {
            $baseQuery->where('role', $role);
        }
        if (!empty($module) && $module !== 'all') {
            $baseQuery->where('module', $module);
        }

        // 1. DATA TREND WAKTU
        $timelineLabels = [];
        $timelineValues = [];

        if ($period === 'today') {
            // Per jam hari ini (00:00 - 23:00)
            $today = now()->toDateString();
            $query = (clone $baseQuery)->whereDate('created_at', $today);

            $hourlyData = $query->select(
                DB::raw('HOUR(created_at) as hour'),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy(DB::raw('HOUR(created_at)'))
            ->pluck('total', 'hour')
            ->toArray();

            for ($i = 0; $i <= 23; $i++) {
                $label = sprintf('%02d:00', $i);
                $timelineLabels[] = $label;
                $timelineValues[] = $hourlyData[$i] ?? 0;
            }
        } elseif ($period === '7days') {
            // 7 hari terakhir
            $startDate = now()->subDays(6)->startOfDay();
            $query = (clone $baseQuery)->where('created_at', '>=', $startDate);

            $dailyData = $query->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy(DB::raw('DATE(created_at)'))
            ->pluck('total', 'date')
            ->toArray();

            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $dateStr = $date->toDateString();
                $timelineLabels[] = $date->translatedFormat('d M');
                $timelineValues[] = $dailyData[$dateStr] ?? 0;
            }
        } else {
            // 30 hari terakhir
            $startDate = now()->subDays(29)->startOfDay();
            $query = (clone $baseQuery)->where('created_at', '>=', $startDate);

            $dailyData = $query->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy(DB::raw('DATE(created_at)'))
            ->pluck('total', 'date')
            ->toArray();

            for ($i = 29; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $dateStr = $date->toDateString();
                $timelineLabels[] = $date->translatedFormat('d M');
                $timelineValues[] = $dailyData[$dateStr] ?? 0;
            }
        }

        // 2. BREAKDOWN POPULARITAS MODUL (Top 8 Modul)
        $moduleRangeQuery = (clone $baseQuery);
        if ($period === 'today') {
            $moduleRangeQuery->whereDate('created_at', now()->toDateString());
        } elseif ($period === '7days') {
            $moduleRangeQuery->where('created_at', '>=', now()->subDays(6)->startOfDay());
        } else {
            $moduleRangeQuery->where('created_at', '>=', now()->subDays(29)->startOfDay());
        }

        $moduleStats = (clone $moduleRangeQuery)
            ->select('module', DB::raw('COUNT(*) as total'))
            ->groupBy('module')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        $moduleLabels = $moduleStats->pluck('module')->toArray();
        $moduleValues = $moduleStats->pluck('total')->toArray();

        // 3. BREAKDOWN SUB-MODUL UNTUK MODUL TERPOPULER
        $topModuleName = $moduleStats->first()?->module;
        $subModuleLabels = [];
        $subModuleValues = [];

        if ($topModuleName) {
            $subModuleStats = (clone $moduleRangeQuery)
                ->where('module', $topModuleName)
                ->whereNotNull('sub_module')
                ->select('sub_module', DB::raw('COUNT(*) as total'))
                ->groupBy('sub_module')
                ->orderByDesc('total')
                ->limit(6)
                ->get();

            $subModuleLabels = $subModuleStats->pluck('sub_module')->toArray();
            $subModuleValues = $subModuleStats->pluck('total')->toArray();
        }

        // 4. BREAKDOWN ROLE PENGGUNA
        $roleStats = (clone $moduleRangeQuery)
            ->select('role', DB::raw('COUNT(*) as total'))
            ->groupBy('role')
            ->orderByDesc('total')
            ->get();

        $roleLabels = $roleStats->pluck('role')->toArray();
        $roleValues = $roleStats->pluck('total')->toArray();

        return response()->json([
            'status' => 'success',
            'timeline' => [
                'labels' => $timelineLabels,
                'data'   => $timelineValues,
            ],
            'modules' => [
                'labels' => $moduleLabels,
                'data'   => $moduleValues,
            ],
            'sub_modules' => [
                'parent_module' => $topModuleName,
                'labels'        => $subModuleLabels,
                'data'          => $subModuleValues,
            ],
            'roles' => [
                'labels' => $roleLabels,
                'data'   => $roleValues,
            ]
        ]);
    }

    /**
     * JSON Log Aktivitas Pengguna (Server-Side Filterable & Paginated Audit Feed)
     */
    public function activityLogs(Request $request): JsonResponse
    {
        $this->authorizeAdmin();

        $query = UserActivity::with([
            'UserAccount.StudentProfile',
            'UserAccount.SchoolStaffProfile',
            'UserAccount.ParentProfile',
            'UserAccount.OfficeProfile',
            'SchoolPartner'
        ]);

        // Filter Sekolah
        if ($request->filled('school_id') && $request->school_id !== 'all') {
            $query->where('school_partner_id', $request->school_id);
        }

        // Filter Role
        if ($request->filled('role') && $request->role !== 'all') {
            $query->where('role', $request->role);
        }

        // Filter Modul
        if ($request->filled('module') && $request->module !== 'all') {
            $query->where('module', $request->module);
        }

        // Filter Tanggal
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        // Pencarian Pengguna (Nama, Email, Modul, Sub-modul, IP)
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('module', 'like', "%{$search}%")
                  ->orWhere('sub_module', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhereHas('UserAccount', function ($u) use ($search) {
                      $u->where('email', 'like', "%{$search}%")
                        ->orWhereHas('StudentProfile', function ($p) use ($search) {
                            $p->where('nama_lengkap', 'like', "%{$search}%");
                        })
                        ->orWhereHas('SchoolStaffProfile', function ($p) use ($search) {
                            $p->where('nama_lengkap', 'like', "%{$search}%");
                        })
                        ->orWhereHas('ParentProfile', function ($p) use ($search) {
                            $p->where('nama_lengkap', 'like', "%{$search}%");
                        })
                        ->orWhereHas('OfficeProfile', function ($p) use ($search) {
                            $p->where('nama_lengkap', 'like', "%{$search}%");
                        });
                  })
                  ->orWhereHas('SchoolPartner', function ($s) use ($search) {
                      $s->where('nama_sekolah', 'like', "%{$search}%");
                  });
            });
        }

        $perPage = (int) $request->get('per_page', 15);
        $paginated = $query->latest('created_at')->paginate($perPage);

        // Format items untuk konsumsi frontend yang bersih
        $items = collect($paginated->items())->map(function ($item) {
            $user = $item->UserAccount;
            $school = $item->SchoolPartner ?? $user?->school;

            return [
                'id'              => $item->id,
                'user_id'         => $item->user_id,
                'user_name'       => $user?->full_name ?? 'Pengguna #' . $item->user_id,
                'user_email'      => $user?->email ?? '-',
                'role'            => $item->role,
                'school_name'     => $school ? $school->nama_sekolah : 'Belajar Cerdas Office',
                'school_logo'     => $school?->logo ? asset($school->logo) : null,
                'module'          => $item->module,
                'sub_module'      => $item->sub_module ?? '-',
                'action'          => $item->action,
                'url'             => $item->url,
                'ip_address'      => $item->ip_address ?? '-',
                'user_agent'      => $item->user_agent,
                'created_at_human'=> Carbon::parse($item->created_at)->diffForHumans(),
                'created_at_full' => Carbon::parse($item->created_at)->translatedFormat('d M Y, H:i:s'),
            ];
        });

        return response()->json([
            'status'       => 'success',
            'data'         => $items,
            'current_page' => $paginated->currentPage(),
            'last_page'    => $paginated->lastPage(),
            'per_page'     => $paginated->perPage(),
            'total'        => $paginated->total(),
        ]);
    }

    /**
     * JSON Leaderboard & Statistik Aktivitas per Sekolah
     */
    public function schoolLeaderboard(Request $request): JsonResponse
    {
        $this->authorizeAdmin();

        $period = $request->get('period', '7days');

        $query = UserActivity::query()->whereNotNull('school_partner_id');

        if ($period === 'today') {
            $query->whereDate('created_at', now()->toDateString());
        } elseif ($period === '7days') {
            $query->where('created_at', '>=', now()->subDays(6)->startOfDay());
        } else {
            $query->where('created_at', '>=', now()->subDays(29)->startOfDay());
        }

        $schools = (clone $query)
            ->select(
                'school_partner_id',
                DB::raw('COUNT(*) as total_activities'),
                DB::raw('COUNT(DISTINCT user_id) as active_users_count')
            )
            ->groupBy('school_partner_id')
            ->orderByDesc('total_activities')
            ->limit(10)
            ->get();

        $schoolIds = $schools->pluck('school_partner_id');
        $schoolPartners = SchoolPartner::whereIn('id', $schoolIds)->get()->keyBy('id');

        $data = $schools->map(function ($row) use ($schoolPartners) {
            $school = $schoolPartners->get($row->school_partner_id);

            return [
                'id'                 => $row->school_partner_id,
                'school_name'        => $school ? $school->nama_sekolah : 'Sekolah #' . $row->school_partner_id,
                'npsn'               => $school?->npsn ?? '-',
                'jenjang'            => $school?->jenjang_sekolah ?? '-',
                'logo'               => $school?->logo ? asset($school->logo) : null,
                'total_activities'   => (int) $row->total_activities,
                'active_users_count' => (int) $row->active_users_count,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data'   => $data,
        ]);
    }

    /**
     * JSON Daftar Pengguna yang Sedang Online (15 Menit Terakhir)
     */
    public function liveOnlineUsers(Request $request): JsonResponse
    {
        $this->authorizeAdmin();

        $fifteenMinutesAgo = now()->subMinutes(15);

        // Ambil aktivitas terkini dari user_activities
        $recentUsers = UserActivity::with([
            'UserAccount.StudentProfile',
            'UserAccount.SchoolStaffProfile',
            'UserAccount.ParentProfile',
            'UserAccount.OfficeProfile',
            'SchoolPartner'
        ])
        ->where('created_at', '>=', $fifteenMinutesAgo)
        ->select('user_id', DB::raw('MAX(created_at) as last_seen'))
        ->groupBy('user_id')
        ->orderByDesc('last_seen')
        ->limit(20)
        ->get();

        $userIds = $recentUsers->pluck('user_id');

        // Ambil data detail aktivitas terakhir untuk masing-masing user
        $latestActivities = UserActivity::whereIn('user_id', $userIds)
            ->whereIn('created_at', $recentUsers->pluck('last_seen'))
            ->get()
            ->keyBy('user_id');

        $users = UserAccount::with([
            'StudentProfile.SchoolPartner',
            'SchoolStaffProfile.SchoolPartner',
            'ParentProfile.SchoolPartner',
            'OfficeProfile'
        ])
        ->whereIn('id', $userIds)
        ->get()
        ->keyBy('id');

        $result = $recentUsers->map(function ($item) use ($users, $latestActivities) {
            $user = $users->get($item->user_id);
            $activity = $latestActivities->get($item->user_id);
            $school = $user?->school;

            return [
                'user_id'    => $item->user_id,
                'name'       => $user?->full_name ?? 'User #' . $item->user_id,
                'email'      => $user?->email ?? '-',
                'role'       => $user?->role ?? $activity?->role ?? 'User',
                'school'     => $school ? $school->nama_sekolah : 'Belajar Cerdas Office',
                'module'     => $activity?->module ?? 'LMS',
                'sub_module' => $activity?->sub_module ?? '-',
                'last_seen'  => Carbon::parse($item->last_seen)->diffForHumans(),
            ];
        });

        return response()->json([
            'status' => 'success',
            'data'   => $result,
        ]);
    }
}
