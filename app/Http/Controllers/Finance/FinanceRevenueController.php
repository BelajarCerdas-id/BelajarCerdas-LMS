<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\SchContractTerm;
use App\Models\SchoolPartner;
use App\Models\StudentProfile;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class FinanceRevenueController extends Controller
{
    public function index($role)
    {
        return view('features.lms.finance.revenue.manage-revenue', compact('role'));
    }

    private function calculateRevenue($term)
    {
        $contract = $term->SchContract;

        if (!$contract) return 0;

        $contractMonths = $contract->start_contract->diffInMonths($contract->end_contract);

        $monthsPerTerm = $contract->total_term > 0 ? ($contractMonths / $contract->total_term) : 0;

        $students = $term->SchTermStudent->where('status', 'active')->count();

        return $students * $contract->price_per_student * $monthsPerTerm;
    }

    public function revenueManagementLoadKpi($role)
    {
        $currentYear = Carbon::now()->year;

        $terms = SchContractTerm::with(['SchContract', 'SchTermStudent'])->where('status', 'paid')->get();

        $lifetimeRevenue = 0;
        $yearRevenue = 0;

        foreach ($terms as $term) {

            $revenue = $this->calculateRevenue($term);

            $lifetimeRevenue += $revenue;

            if ($term->paid_at && Carbon::parse($term->paid_at)->year == $currentYear) {
                $yearRevenue += $revenue;
            }
        }

        $schoolCount = SchoolPartner::count();

        $avgRevenuePerSchool = $schoolCount > 0 ? ($lifetimeRevenue / $schoolCount) : 0;

        return response()->json([
            'lifetime_revenue' => round($lifetimeRevenue),
            'revenue_by_year' => round($yearRevenue),
            'avg_revenue_by_school' => round($avgRevenuePerSchool),
            'school_count' => $schoolCount,
        ]);
    }

    public function revenueManagementLoadLeaderboard($role)
    {
        $schools = SchoolPartner::query()->withCount('SchContract')->with(['SchContract.SchContractTerm.SchTermStudent'])->addSelect([
            'student_count' => StudentProfile::selectRaw('COUNT(*)')->whereColumn('student_profiles.school_partner_id', 'school_partners.id')
        ])->get();

        $totalRevenueAll = 0;

        $processed = $schools->map(function ($school) use (&$totalRevenueAll) {

            $lifetimeRevenue = 0;

            foreach ($school->SchContract as $contract) {

                foreach ($contract->SchContractTerm->where('status', 'paid') as $term) {

                    $lifetimeRevenue += $this->calculateRevenue($term);
                }
            }

            $totalRevenueAll += $lifetimeRevenue;

            return [
                'id' => $school->id,
                'nama_sekolah' => $school->nama_sekolah,
                'logo' => $school->logo,
                'student_count' => $school->student_count,
                'sch_contract_count' => $school->sch_contract_count,
                'lifetime_revenue' => $lifetimeRevenue,
            ];
        });

        $sorted = collect($processed)->sortByDesc('lifetime_revenue')->values();

        $top3 = $sorted->take(3);

        $topRevenue = $top3->first()['lifetime_revenue'] ?? 0;

        $top3Total = $top3->sum('lifetime_revenue');

        $avgTop3Revenue = $top3->count() > 0 ? $top3Total / $top3->count() : 0;

        $top3Contribution = $totalRevenueAll > 0 ? ($top3Total / $totalRevenueAll) * 100 : 0;

        $top3 = $top3->map(function ($item) use ($totalRevenueAll) {

            $item['contribution'] = $totalRevenueAll > 0 ? ($item['lifetime_revenue'] / $totalRevenueAll) * 100 : 0;

            return $item;
        });

        return response()->json([
            'data' => [
                'top3' => $top3->values(),
                'summary' => [
                    'top_revenue' => $topRevenue,
                    'top3_total' => $top3Total,
                    'avg_top3_revenue' => $avgTop3Revenue,
                    'top3_contribution' => $top3Contribution,
                ]
            ]
        ]);
    }

    public function revenueManagementLoadChart(Request $request, $role)
    {
        $period = $request->period ?? 'monthly';
        $year   = $request->year ?? now()->year;

        $query = SchContractTerm::query()->where('status', 'paid')->with(['SchContract', 'SchTermStudent']);

        if ($period === 'monthly') {
            $query->whereYear('paid_at', $year);
        }

        $terms = $query->get();

        if ($period === 'yearly') {

            $grouped = $terms->groupBy(fn ($t) => $t->paid_at->format('Y'));

            $data = $grouped->map(fn ($items) => $items->sum(fn ($term) => $this->calculateRevenue($term)));

            $labels = $data->keys();
            $values = $data->values();

        } else {

            $grouped = $terms->groupBy(fn ($t) => $t->paid_at->format('m'));

            $data = $grouped->map(fn ($items) => $items->sum(fn ($term) => $this->calculateRevenue($term)));

            $labels = $data->keys() ->map(fn ($m) => date('F', mktime(0, 0, 0, $m, 1)));

            $values = $data->values();
        }

        return response()->json([
            'labels' => $labels->values(),
            'data'   => $values->values(),
            'years'  => SchContractTerm::selectRaw('YEAR(paid_at) as year')->whereNotNull('paid_at')->distinct()->orderBy('year', 'desc')->pluck('year'),
        ]);
    }

    public function revenueManagementLoadRanking(Request $request, $role)
    {
        $search = $request->search_school;

        $schools = SchoolPartner::query()->with(['SchContract.SchContractTerm.SchTermStudent'])->withCount('SchContract')->addSelect([
            'student_count' => StudentProfile::selectRaw('COUNT(*)')->whereColumn('student_profiles.school_partner_id', 'school_partners.id')
        ])->when($search, function ($q) use ($search) {
            $q->where('nama_sekolah', 'like', "%$search%");
        })
            ->get();

        // hitung revenue per school + total global
        $totalRevenueAll = 0;

        $processed = $schools->map(function ($school) use (&$totalRevenueAll) {

            $lifetimeRevenue = 0;

            foreach ($school->SchContract as $contract) {

                foreach ($contract->SchContractTerm->where('status', 'paid') as $term) {

                    $lifetimeRevenue += $this->calculateRevenue($term);
                }
            }

            $totalRevenueAll += $lifetimeRevenue;

            return [
                'id' => $school->id,
                'nama_sekolah' => $school->nama_sekolah,
                'logo' => $school->logo,
                'student_count' => $school->student_count,
                'sch_contract_count' => $school->sch_contract_count,
                'lifetime_revenue' => $lifetimeRevenue,
            ];
        });

        // sort
        $sorted = $processed->sortByDesc('lifetime_revenue')->values()->map(function ($item) use ($totalRevenueAll) {
                $item['contribution'] = $totalRevenueAll > 0 ? round(($item['lifetime_revenue'] / $totalRevenueAll) * 100, 2) : 0;

                return $item;
            });

        // buang top 3 karena sudah tampil di leaderboard
        $rankingOnly = $sorted->slice(3)->values();

        // pagination manual
        $page = $request->page ?? 1;
        $perPage = 20;

        $paginated = new LengthAwarePaginator(
            $rankingOnly->forPage($page, $perPage),
            $rankingOnly->count(),
            $perPage,
            $page,
            ['path' => request()->url()]
        );

        return response()->json([
            'data' => $paginated->items(),
            'current_page' => $paginated->currentPage(),
            'per_page' => $paginated->perPage(),
            'total' => $paginated->total(),
            'last_page' => $paginated->lastPage(),
        ]);
    }
}
