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

    // Paid revenue per term in SQL: active students (correlated subquery) * price * term months
    private function revenueExpression(): string
    {
        return "(SELECT COUNT(*) FROM sch_term_students sts WHERE sts.term_id = sch_contract_terms.id AND sts.status = 'active')"
            . " * sch_contracts.price_per_student"
            . " * TIMESTAMPDIFF(MONTH, sch_contracts.start_contract, sch_contracts.end_contract)"
            . " / NULLIF(sch_contracts.total_term, 0)";
    }

    private function sumPaidRevenue(?int $year = null)
    {
        $query = SchContractTerm::query()->where('status', 'paid')
            ->join('sch_contracts', 'sch_contracts.id', '=', 'sch_contract_terms.contract_id');

        if ($year) {
            $query->whereYear('paid_at', $year);
        }

        return (float) $query->selectRaw('COALESCE(SUM(' . $this->revenueExpression() . '), 0) as revenue')->value('revenue');
    }

    private function schoolsRevenueQuery()
    {
        return SchoolPartner::query()
            ->withCount('SchContract')
            ->addSelect([
                'student_count' => StudentProfile::selectRaw('COUNT(*)')->whereColumn('student_profiles.school_partner_id', 'school_partners.id'),
                'lifetime_revenue' => SchContractTerm::selectRaw('COALESCE(SUM(' . $this->revenueExpression() . '), 0)')
                    ->join('sch_contracts', 'sch_contracts.id', '=', 'sch_contract_terms.contract_id')
                    ->whereColumn('sch_contracts.school_partner_id', 'school_partners.id')
                    ->where('sch_contract_terms.status', 'paid'),
            ]);
    }

    public function revenueManagementLoadKpi($role)
    {
        $lifetimeRevenue = $this->sumPaidRevenue();

        $yearRevenue = $this->sumPaidRevenue(Carbon::now()->year);

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
        $schools = $this->schoolsRevenueQuery()->orderByDesc('lifetime_revenue')->get();

        $totalRevenueAll = $schools->sum('lifetime_revenue');

        $processed = $schools->map(function ($school) use ($totalRevenueAll) {

            return [
                'id' => $school->id,
                'nama_sekolah' => $school->nama_sekolah,
                'logo' => $school->logo,
                'student_count' => $school->student_count,
                'sch_contract_count' => $school->sch_contract_count,
                'lifetime_revenue' => (float) $school->lifetime_revenue,
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

        $query = SchContractTerm::query()->where('status', 'paid')
            ->join('sch_contracts', 'sch_contracts.id', '=', 'sch_contract_terms.contract_id')
            ->when($period === 'monthly', fn ($q) => $q->whereYear('paid_at', $year))
            ->selectRaw('YEAR(paid_at) as y, MONTH(paid_at) as m, COALESCE(SUM(' . $this->revenueExpression() . '), 0) as revenue')
            ->groupByRaw('YEAR(paid_at), MONTH(paid_at)');

        if ($period === 'yearly') {
            $rows = $query->orderBy('y')->get();

            $labels = $rows->pluck('y');
        } else {
            $rows = $query->orderBy('m')->get();

            $labels = $rows->map(fn ($r) => date('F', mktime(0, 0, 0, $r->m, 1)));
        }

        return response()->json([
            'labels' => $labels->values(),
            'data'   => $rows->pluck('revenue')->map(fn ($v) => (float) $v)->values(),
            'years'  => SchContractTerm::selectRaw('YEAR(paid_at) as year')->whereNotNull('paid_at')->distinct()->orderBy('year', 'desc')->pluck('year'),
        ]);
    }

    public function revenueManagementLoadRanking(Request $request, $role)
    {
        $search = $request->search_school;

        $schools = $this->schoolsRevenueQuery()
            ->when($search, function ($q) use ($search) {
                $q->where('nama_sekolah', 'like', "%$search%");
            })
            ->orderByDesc('lifetime_revenue')
            ->get();

        // hitung revenue per school + total global
        $totalRevenueAll = $schools->sum('lifetime_revenue');

        $processed = $schools->map(function ($school) use ($totalRevenueAll) {

            return [
                'id' => $school->id,
                'nama_sekolah' => $school->nama_sekolah,
                'logo' => $school->logo,
                'student_count' => $school->student_count,
                'sch_contract_count' => $school->sch_contract_count,
                'lifetime_revenue' => (float) $school->lifetime_revenue,
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
