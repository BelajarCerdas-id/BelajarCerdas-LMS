<?php

namespace App\Http\Controllers\Finance;
use App\Http\Controllers\Controller;
use App\Models\SchContract;
use App\Models\SchContractTerm;
use App\Models\SchoolPartner;
use App\Models\StudentProfile;
use Carbon\Carbon;
use Illuminate\Http\Request;

class FinanceDashboardController extends Controller
{
    public function index($role)
    {
        return view('features.lms.finance.dashboard', compact('role'));
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

    public function loadKpiDashboard($role)
    {
        $now = now()->format('Y-m-d');

        $lifetimeRevenue = $this->sumPaidRevenue();

        $schoolCount = SchoolPartner::count();

        $contracts = SchContract::whereDate('start_contract', '<=', $now)->whereDate('end_contract', '>=', $now)->count();

        $activeStudentCount = StudentProfile::whereHas('UserAccount', function ($q) {
            $q->where('status_akun', 'aktif');
        })->count();

        return response()->json([
            'lifetime_revenue' => round($lifetimeRevenue),
            'school_count' => $schoolCount,
            'contract_count' => $contracts,
            'student_count' => $activeStudentCount,
        ]);
    }

    public function loadChartDashboard(Request $request, $role)
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

    public function loadTopRevenueDashboard(Request $request, $role)
    {
        $schools = $this->schoolsRevenueQuery()->orderByDesc('lifetime_revenue')->limit(10)->get();

        // hitung revenue per school + total global
        $totalRevenueAll = $this->sumPaidRevenue();

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

        return response()->json([
            'data' => $processed->values(),
        ]);
    }

    public function loadContractExpiringDashboard($role)
    {
        $today = now();

        $contracts = SchContract::with('SchoolPartner')
            ->get()
            ->map(function ($contract) use ($today) {

                $end = Carbon::parse($contract->end_contract)->startOfDay();

                $diffDays = $today->diffInDays($end, false);

                return [
                    'school_name' => $contract->SchoolPartner->nama_sekolah ?? '-',
                    'days_left' => $diffDays,
                    'end_date' => $contract->end_contract,
                ];
            })
            ->filter(function ($item) {
                return $item['days_left'] >= 0 && $item['days_left'] <= 30;
            })
            ->sortBy('days_left')
            ->values();

        return response()->json([
            'data' => $contracts
        ]);
    }
}
