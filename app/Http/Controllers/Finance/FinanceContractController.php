<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Imports\ContractTermStudent\contractStudentSheetImport;
use App\Models\SchContract;
use App\Models\SchContractTerm;
use App\Models\SchoolPartner;
use App\Models\SchTermStudent;
use App\Models\StudentProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class FinanceContractController extends Controller
{
    public function index($role)
    {
        return view('features.lms.finance.contract.manage-contract', compact('role'));
    }

    public function paginateManageContract(Request $request, $role)
    {
        $today = now();

        $contract = SchContract::whereDate('end_contract', '<', $today)->get();

        if ($contract) {
            foreach ($contract as $c) {
                $c->update([
                    'status' => 'inactive'
                ]);
            }
        }

        $query = SchoolPartner::query()->withCount('SchContract')->with(['SchContract', 'SchContract.SchContractTerm.SchTermStudent'])->addSelect([
            'student_count' => StudentProfile::selectRaw('COUNT(*)')->whereColumn('student_profiles.school_partner_id', 'school_partners.id')
        ]);

        // Search
        if ($request->filled('search_school')) {

            $search = $request->search_school;

            $query->where('nama_sekolah', 'LIKE', "%{$search}%");
        }

        $schools = $query->paginate(20);

        // KPI GLOBAL
        $totalContractValue = 0;
        $totalPaid = 0;
        $totalOutstanding = 0;

        foreach ($schools as $school) {

            foreach ($school->SchContract as $contract) {

                $contractMonths = $contract->start_contract->diffInMonths($contract->end_contract);

                $monthsPerTerm = $contract->total_term > 0 ? ($contractMonths / $contract->total_term) : 0;

                // Target Contract Value
                $contractValue = $contract->init_student_count * $contract->price_per_student * $contractMonths;

                $totalContractValue += $contractValue;

                foreach ($contract->SchContractTerm as $term) {

                    $studentsCount = $term->SchTermStudent->where('status', 'active')->count();

                    $amount = $studentsCount * $contract->price_per_student * $monthsPerTerm;

                    if ($term->status === 'paid') {
                        $totalPaid += $amount;
                    }

                    if (in_array($term->status, ['unpaid', 'overdue'])) {
                        $totalOutstanding += $amount;
                    }
                }
            }
        }

        $collectionRate = ($totalPaid + $totalOutstanding) > 0 ? round(($totalPaid / ($totalPaid + $totalOutstanding)) * 100) : 0;

        // TABLE DATA
        $schools->getCollection()->transform(function ($school) {

            $contracts = $school->SchContract;

            $activeContract = $contracts->where('start_contract', '<=', now())->where('end_contract', '>=', now())->where('status', 'active')->sortByDesc('end_contract')->first();

            $school->start_contract = $activeContract?->start_contract?->format('d M Y');

            $school->end_contract = $activeContract?->end_contract?->format('d M Y');

            $contractValue = 0;
            $outstanding = 0;
            $overdueTerms = 0;
            $activeContractCount = 0;

            foreach ($contracts as $contract) {

                $contractMonths = $contract->start_contract->diffInMonths($contract->end_contract);

                $monthsPerTerm = $contract->total_term > 0 ? ($contractMonths / $contract->total_term) : 0;

                $contractValue += $contract->init_student_count * $contract->price_per_student * $contractMonths;

                if (now()->between($contract->start_contract, $contract->end_contract)) {
                    $activeContractCount++;
                }

                foreach ($contract->SchContractTerm as $term) {

                    $studentsCount = $term->SchTermStudent->where('status', 'active')->count();

                    $amount = $studentsCount * $contract->price_per_student * $monthsPerTerm;

                    if (in_array($term->status, ['unpaid', 'overdue'])) {
                        $outstanding += $amount;
                    }

                    if ($term->status === 'overdue') {
                        $overdueTerms++;
                    }
                }
            }

            $school->active_contract_count = $activeContractCount;

            $school->contract_value = round($contractValue);

            $school->outstanding = round($outstanding);

            $school->overdue_terms = $overdueTerms;

            if ($activeContract) {

                $school->status = 'active';
                $school->status_label = 'Aktif';

            } else {

                $school->status = 'inactive';
                $school->status_label = 'Tidak Aktif';
            }

            unset($school->SchContract);

            return $school;
        });

        return response()->json([

            'kpi' => [
                'total_contract_value' => round($totalContractValue),
                'total_paid' => round($totalPaid),
                'total_outstanding' => round($totalOutstanding),
                'collection_rate' => $collectionRate,
            ],

            'data' => $schools->items(),
            'links' => (string) $schools->links(),
            'current_page' => $schools->currentPage(),
            'manageContractDetail' => '/lms/:role/manage-contract/schools/:schoolId',
        ]);
    }
        
    public function manageContractDetail($role, $schoolId)
    {
        $today = now();

        $contract = SchContract::whereDate('end_contract', '<', $today)->get();

        if ($contract) {
            foreach ($contract as $c) {
                $c->update([
                    'status' => 'inactive'
                ]);
            }
        }

        $schoolPartner = SchoolPartner::where('id', $schoolId)->first();

        return view('features.lms.finance.contract.manage-contract-detail', compact('role', 'schoolId', 'schoolPartner'));
    }

    public function paginateManageContractDetail($role, $schoolId)
    {
        $contracts = SchContract::with(['SchContractTerm.SchTermStudent'])->where('school_partner_id', $schoolId)->orderByDesc('start_contract')->paginate(20);

        $contracts->getCollection()->transform(function ($contract) {

            $contractMonths = $contract->start_contract->diffInMonths($contract->end_contract);

            $monthsPerTerm = $contract->total_term > 0 ? ($contractMonths / $contract->total_term) : 0;

            // TARGET CONTRACT VALUE
            $contractValue = $contract->init_student_count * $contract->price_per_student * $contractMonths;

            // PAID
            $paidAmount = $contract->SchContractTerm->reduce(
                function ($carry, $term) use ($contract, $monthsPerTerm) {

                    if ($term->status !== 'paid') {
                        return $carry;
                    }

                    $activeStudents = $term->SchTermStudent->where('status', 'active')->count();

                    return $carry + ($activeStudents * $contract->price_per_student * $monthsPerTerm);
                }, 0
            );

            // OUTSTANDING (UNPAID + OVERDUE)
            $outstandingAmount = $contract->SchContractTerm->reduce(
                function ($carry, $term) use ($contract, $monthsPerTerm) {

                    if (!in_array($term->status, ['unpaid', 'overdue'])) {
                        return $carry;
                    }

                    $activeStudents = $term->SchTermStudent->where('status', 'active')->count();

                    return $carry + ($activeStudents * $contract->price_per_student * $monthsPerTerm);
                }, 0
            );

            // PROGRESS
            $actualBill = $paidAmount + $outstandingAmount;

            $progress = $actualBill > 0 ? round(($paidAmount / $actualBill) * 100) : 0;

            $contract->contract_months = $contractMonths;

            $contract->contract_value = round($contractValue);

            $contract->paid_amount = round($paidAmount);

            $contract->outstanding = round($outstandingAmount);

            $contract->payment_progress = $progress;

            $contract->period = $contract->start_contract->format('d M Y') . ' - ' . $contract->end_contract->format('d M Y');

            unset($contract->SchContractTerm);

            return $contract;
        });

        // KPI
        $allContracts = SchContract::with(['SchContractTerm.SchTermStudent'])->where('school_partner_id', $schoolId)->get();

        $lifetimeContractValue = 0;
        $totalPaid = 0;
        $totalOutstanding = 0;

        foreach ($allContracts as $contract) {

            $contractMonths = $contract->start_contract->diffInMonths($contract->end_contract);

            $monthsPerTerm = $contract->total_term > 0 ? ($contractMonths / $contract->total_term) : 0;

            $contractValue = $contract->init_student_count * $contract->price_per_student * $contractMonths;

            $paidAmount = $contract->SchContractTerm->reduce(
                function ($carry, $term) use ($contract, $monthsPerTerm) {

                    if ($term->status !== 'paid') {
                        return $carry;
                    }

                    $activeStudents = $term->SchTermStudent->where('status', 'active')->count();

                    return $carry + ($activeStudents * $contract->price_per_student * $monthsPerTerm);
                }, 0
            );

            $outstandingAmount = $contract->SchContractTerm->reduce(
                function ($carry, $term) use ($contract, $monthsPerTerm) {

                    if (!in_array($term->status, ['unpaid', 'overdue'])) {
                        return $carry;
                    }

                    $activeStudents = $term->SchTermStudent->where('status', 'active')->count();

                    return $carry + ($activeStudents * $contract->price_per_student * $monthsPerTerm);
                }, 0
            );

            $lifetimeContractValue += $contractValue;
            $totalPaid += $paidAmount;
            $totalOutstanding += $outstandingAmount;
        }

        return response()->json([
            'kpi' => [

                // jumlah kontrak
                'total_contracts' => $allContracts->count(),

                // target awal seluruh kontrak
                'lifetime_contract_value' => round($lifetimeContractValue),

                // sudah dibayar
                'total_paid' => round($totalPaid),

                // tagihan unpaid + overdue
                'outstanding' => round($totalOutstanding),
            ],

            'data' => $contracts->items(),
            'links' => (string) $contracts->links(),
            'current_page' => $contracts->currentPage(),
            'contractPaymentDetail' => '/lms/:role/manage-contract/schools/:schoolId/contract/:contractId/payment-detail',
        ]);
    }

    public function contractDetailActivate(Request $request, $role, $schoolId, $contractId) 
    {
        return DB::transaction(function () use ($request, $contractId) {

            // LOCK CONTRACT
            $contract = SchContract::where('id', $contractId)->lockForUpdate()->firstOrFail();

            //  tanggal sudah lewat
            if ($contract->start_contract && now()->gt($contract->end_contract)) {
                return response()->json([
                    'message' => 'Periode kontrak sudah berakhir, perubahan status tidak diperbolehkan.'
                ], 422);
            }

            $contract->update([
                'status' => $request->status,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Status kontrak berhasil diubah.',
            ]);
        });
    }

    public function contractPaymentDetail($role, $schoolId, $contractId)
    {
        $contract = SchContract::with(['SchoolPartner'])->findOrFail($contractId);

        return view('features.lms.finance.contract.contract-payment-detail', compact('role', 'schoolId', 'contractId', 'contract'));
    }

    public function paginateContractPaymentDetail($role, $schoolId, $contractId)
    {
        $contract = SchContract::with(['SchContractTerm.SchTermStudent'])->findOrFail($contractId);

        $terms = $contract->SchContractTerm->sortBy('term_number')->values();

        $contractMonths = $contract->start_contract->diffInMonths($contract->end_contract);

        $monthlyAmount = $contract->init_student_count * $contract->price_per_student;

        $totalContractValue = $monthlyAmount * $contractMonths;

        $monthsPerTerm = $contract->total_term > 0 ? ($contractMonths / $contract->total_term) : 0;

        $terms->each(function ($term) use ($contract, $monthsPerTerm) {

            $studentsCount = $term->SchTermStudent->count();

            $term->active_students = $term->SchTermStudent->where('status', 'active')->count();

            $monthlyAmount = $term->active_students * $contract->price_per_student;

            $termAmount = $monthlyAmount * $monthsPerTerm;

            $term->students_count = $studentsCount;

            $term->price_per_student = $contract->price_per_student;

            $term->monthly_amount = round($monthlyAmount);

            $term->term_amount = round($termAmount);
        });

        $totalPaid = $terms->where('status', 'paid')->sum('term_amount');

        $outstandingAmount = $terms->whereIn('status', ['unpaid', 'overdue'])->sum('term_amount');

        $actualRevenue = $totalPaid + $outstandingAmount;

        $progress = $actualRevenue > 0 ? round(($totalPaid / $actualRevenue) * 100) : 0;

        $studentCount = $terms->max('students_count') ?? 0;

        return response()->json([

            'kpi' => [
                'total_contract_value' => $totalContractValue,
                'total_paid' => $totalPaid,
                'outstanding_amount' => $outstandingAmount,
                'student_count' => $studentCount,
                'active_students' => $terms->sum('active_students'),
                'progress' => $progress,
            ],

            'terms' => $terms->values(),
            'uploadContractStudent' => '/lms/:role/manage-contract/schools/:schoolId/contract/:contractId/payment-detail/upload-contract-student/:termId',
            'studentList' => '/lms/:role/manage-contract/schools/:schoolId/contract/:contractId/payment-detail/student-list/:termId',
        ]);
    }

    public function markContractTerm(Request $request, $role, $schoolId, $contractId, $termId)
    {
        $term = SchContractTerm::findOrFail($termId);

        $term->update([
            'status' => $request->status,
            'paid_at' => now()
        ]);
    }

    public function studentList($role, $schoolId, $contractId, $termId)
    {
        $schContractTerm = SchContractTerm::with(['SchContract', 'SchContract.SchoolPartner'])->findOrFail($termId);
        
        return view('features.lms.finance.contract.manage-contract-term.student-list', compact('role', 'schoolId', 'contractId', 'termId', 'schContractTerm'));
    }

    public function paginateStudentList(Request $request, $role, $schoolId, $contractId, $termId) 
    {

        $query = SchTermStudent::with(['StudentAccount.StudentProfile'])->where('term_id', $termId)->latest();

        $totalStudents = SchTermStudent::where('term_id', $termId)->count();

        $activeStudents = SchTermStudent::where('term_id', $termId)->where('status', 'active')->count();

        $inactiveStudents = SchTermStudent::where('term_id', $termId)->where('status', 'inactive')->count();

        $activationRate = $totalStudents > 0 ? round(($activeStudents / $totalStudents) * 100, 1) : 0;

        // Filter school
        if ($request->filled('search_student')) {
            $search = $request->search_student;
            $query->whereHas('StudentAccount.StudentProfile', function ($q) use ($search) {
                $q->where('nama_lengkap', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('filter_status')) {
            $query->where('status', $request->filter_status);
        }

        $studentList = $query->paginate(20);

        return response()->json([
            'kpi' => [
                'total_students' => $totalStudents,
                'active_students' => $activeStudents,
                'inactive_students' => $inactiveStudents,
                'activation_rate' => $activationRate,
            ],

            'studentList' => $studentList->items(),

            'pagination' => [
                'current_page' => $studentList->currentPage(),
                'per_page' => $studentList->perPage(),
                'last_page' => $studentList->lastPage(),
                'total' => $studentList->total(),
            ],

            'links' => (string) $studentList->links(),
        ]);
    }

    // function untuk bulk upload contract students
    public function bulkUploadContractStundents(Request $request, $role, $schoolId, $contractId, $termId)
    {
        $validator = Validator::make($request->all(), [
            'bulkUpload-contract-students' => 'required|file|mimes:xlsx,xls,csv|max:100000',
        ], [
            'bulkUpload-contract-students.required' => 'File tidak boleh kosong.',
            'bulkUpload-contract-students.mimes' => 'Format file harus .xlsx.',
            'bulkUpload-contract-students.max' => 'Ukuran file maksimal 100MB.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => [
                    'form_errors' => $validator->errors(),
                    'excel_validation_errors' => [],
                ]
            ], 422);
        }

        try {
            $userId = Auth::id();
            Excel::import(new contractStudentSheetImport($userId, $contractId, $termId, $request->file('bulkUpload-contract-students')), 
                $request->file('bulkUpload-contract-students'));

            return response()->json([
                'status' => 'success',
                'message' => 'Import contract students berhasil.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'errors' => [
                    'form_errors' => [],
                    'excel_validation_errors' => $e->errors()['import'] ?? [],
                ]
            ], 422);
        }
    }

    public function studentListActivate(Request $request, $role, $schoolId, $contractId, $termId, $studentId)
    {
        return DB::transaction(function () use ($request, $termId, $studentId) {

            $term = SchContractTerm::where('id', $termId)->lockForUpdate()->firstOrFail();

            if ($term->status === 'paid') {
                return response()->json([
                    'message' => 'Term sudah paid, data tidak bisa diubah.'
                ], 422);
            }

            $student = SchTermStudent::where('id', $studentId)->where('term_id', $termId)->lockForUpdate()->firstOrFail();

            $student->update([
                'status' => $request->status,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Status siswa berhasil diubah.',
            ]);
        });
    }
}
