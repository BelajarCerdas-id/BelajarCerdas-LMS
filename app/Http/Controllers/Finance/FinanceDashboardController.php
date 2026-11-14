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

    private function calculateRevenue($term)
    {
        $contract = $term->SchContract;

        if (!$contract) return 0;

        $contractMonths = $contract->start_contract->diffInMonths($contract->end_contract);

        $monthsPerTerm = $contract->total_term > 0 ? ($contractMonths / $contract->total_term) : 0;

        $students = $term->SchTermStudent->where('status', 'active')->count();

        return $students * $contract->price_per_student * $monthsPerTerm;
    }

    public function loadKpiDashboard($role)
    {
        
        $now = now()->format('Y-m-d');

        $terms = SchContractTerm::with(['SchContract', 'SchTermStudent'])->where('status', 'paid')->get();

        $lifetimeRevenue = 0;

        foreach ($terms as $term) {

            $revenue = $this->calculateRevenue($term);

            $lifetimeRevenue += $revenue;
        }

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

    public function loadChartDashboard($role)
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

    public function loadTopRevenueDashboard(Request $request, $role)
    {
        $schools = SchoolPartner::query()->with(['SchContract.SchContractTerm.SchTermStudent'])->withCount('SchContract')->addSelect([
            'student_count' => StudentProfile::selectRaw('COUNT(*)')->whereColumn('student_profiles.school_partner_id', 'school_partners.id')
        ])->get();

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
        $sorted = $processed->sortByDesc('lifetime_revenue')->values()->take(10);

        return response()->json([
            'data' => $sorted->values(),
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
