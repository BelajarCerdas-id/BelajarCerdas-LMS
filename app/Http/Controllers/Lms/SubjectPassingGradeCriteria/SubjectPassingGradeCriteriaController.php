<?php

namespace App\Http\Controllers\Lms\SubjectPassingGradeCriteria;

use App\Events\LmsSubjectPassingGradeCriteria;
use App\Http\Controllers\Controller;
use App\Imports\SubjectPassingGradeCriteria\SubjectPassingGradeCriteriaSheetImport;
use App\Models\Kurikulum;
use App\Models\SchoolClass;
use App\Models\SchoolPartner;
use App\Models\SubjectPassingGradeCriteria;
use App\Models\UserAccount;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class SubjectPassingGradeCriteriaController extends Controller
{
    // HELPER NAMING CLASS
    private function extractClassLevel(string $className): int
    {
        $className = trim(strtoupper($className));

        // 1. Coba angka di depan (7, 10, 12, dst)
        if (preg_match('/^\d+/', $className, $match)) {
            return (int) $match[0];
        }

        // 2. Coba romawi di depan (I, II, III, IV, V, VI, VII, VIII, IX, X, XI, XII)
        if (preg_match('/^(XII|XI|X|IX|VIII|VII|VI|V|IV|III|II|I)\b/', $className, $match)) {
            return $this->romanToInt($match[0]);
        }

        return 0; // fallback aman
    }

    private function romanToInt(string $roman): int
    {
        $map = [
            'I' => 1,
            'II' => 2,
            'III' => 3,
            'IV' => 4,
            'V' => 5,
            'VI' => 6,
            'VII' => 7,
            'VIII' => 8,
            'IX' => 9,
            'X' => 10,
            'XI' => 11,
            'XII' => 12,
        ];

        return $map[$roman] ?? 0;
    }

    // function subject passing grade criteria management
    public function subjectPassingGradeCriteria($schoolName, $schoolId) {
        $tahunAjaran = SchoolClass::where('school_partner_id', $schoolId)->pluck('tahun_ajaran')->unique()->sortDesc()->values();

        $getCurriculum = Kurikulum::all();

        return view('features.lms.administrator.subject-passing-grade-criteria.lms-subject-passing-grade-criteria', compact('schoolName', 'schoolId', 'tahunAjaran', 'getCurriculum'));
    }

    public function paginateSubjectPassingGradeCriteria(Request $request, $schoolName, $schoolId)
    {
        // GET USERS
        $users = UserAccount::with(['StudentProfile', 'SchoolStaffProfile'])
            ->where(function ($query) use ($schoolId) {
                $query->whereHas('StudentProfile', function ($q) use ($schoolId) {
                    $q->where('school_partner_id', $schoolId);
                })->orWhereHas('SchoolStaffProfile', function ($q) use ($schoolId) {
                    $q->where('school_partner_id', $schoolId);
                });
            })->get();

        $countUsers = $users->count();

        // SCHOOL DETAIL
        $getSchool = SchoolPartner::with('UserAccount.SchoolStaffProfile')->where('id', $schoolId)->first();

        $schoolPartner = SchoolPartner::findOrFail($schoolId);
        $jenjang = strtoupper($schoolPartner->jenjang_sekolah);

        // DEFAULT LEVEL (fallback)
        $startLevelMap = [
            'SD'  => 1,  'MI'  => 1,
            'SMP' => 7,  'MTS' => 7,
            'SMA' => 10, 'SMK' => 10,
            'MA'  => 10, 'MAK' => 10,
        ];

        $defaultLevel = $startLevelMap[$jenjang] ?? 1;

        // TAHUN AJARAN
        $tahunAjaran = SchoolClass::where('school_partner_id', $schoolId)->pluck('tahun_ajaran')->unique()->sortDesc()->values();

        $searchYear = $request->filled('search_year') ? $request->search_year : ($tahunAjaran->first() ?? null);

        // CLASS LEVEL (BERDASARKAN TAHUN AJARAN)
        $classLevels = SchoolClass::where('school_partner_id', $schoolId)->where('tahun_ajaran', $searchYear)->pluck('class_name')->map(fn($c) => (int) $this->extractClassLevel($c))
        ->unique()->sort()->values();

        $selectedClass = $request->filled('search_class') ? (int) $request->search_class : ($classLevels->first() ?? $defaultLevel);

        // QUERY DATA KKM
        $query = SubjectPassingGradeCriteria::with(['SchoolPartner', 'Kelas', 'Mapel', 'UserAccount.SchoolStaffProfile', 'UserAccount.OfficeProfile'])
        ->where('school_partner_id', $schoolId)->where('school_year', $searchYear);

        // FILTER KELAS
        if ($selectedClass) {
            $kelasIds = SchoolClass::where('school_partner_id', $schoolId)->where('tahun_ajaran', $searchYear)->get()->filter(function ($item) use ($selectedClass) {
                return (int) $this->extractClassLevel($item->class_name) === (int) $selectedClass;
            })->pluck('kelas_id')->unique()->values();

            $query->whereIn('kelas_id', $kelasIds);
        }

        // PAGINATION (LANGSUNG DARI QUERY)
        $subjectPassingGradeCriteria = $query->orderBy('created_at', 'desc')->paginate(20);

        // RESPONSE
        return response()->json([
            'data' => $subjectPassingGradeCriteria->items(),
            'links' => (string) $subjectPassingGradeCriteria->links(),
            'current_page' => $subjectPassingGradeCriteria->currentPage(),
            'per_page' => $subjectPassingGradeCriteria->perPage(),
            'tahunAjaran'   => $tahunAjaran,
            'selectedYear'  => $searchYear,
            'classLevels'   => $classLevels,
            'selectedClass' => $selectedClass,
            'schoolIdentity' => $getSchool,
            'countUsers' => $countUsers,
        ]);
    }

    // function subject passing grade criteria management store
    public function subjectPassingGradeCriteriaStore(Request $request, $schoolName, $schoolId) {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'kurikulum_id' => 'required',
            'kelas_id' => 'required',
            'mapel_id' => 'required',
            'school_year' => 'required',
            'kkm_value' => 'required|integer|min:1|max:100',
        ], [
            'kurikulum_id.required' => 'Harap pilih kurikulum.',
            'kelas_id.required' => 'Harap pilih kelas.',
            'mapel_id.required' => 'Harap pilih mata pelajaran.',
            'school_year.required' => 'Harap pilih tahun ajaran.',
            'kkm_value.required' => 'Nilai KKM tidak boleh kosong.',
            'kkm_value.min' => 'Nilai KKM harus lebih dari 0.',
            'kkm_value.max' => 'Nilai KKM tidak boleh lebih dari 100.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $subjectPassingGradeCriteria = SubjectPassingGradeCriteria::create([
                'user_id' => $user->id,
                'school_partner_id' => $schoolId,
                'kelas_id' => $request->kelas_id,
                'mapel_id' => $request->mapel_id,
                'school_year' => $request->school_year,
                'kkm_value' => $request->kkm_value,
            ]);
        } catch (QueryException $e) {
            if ($e->getCode() == 23000) {
                return response()->json([
                    'status' => 'error',
                    'errors' => [
                        'mapel_id' => ['Nilai KKM pada mata pelajaran di tahun ajaran ini telah terdaftar.']
                    ]
                ], 422);
            }

            throw $e;
        }

        broadcast(new LmsSubjectPassingGradeCriteria('SubjectPassingGradeCriteria', 'create', $subjectPassingGradeCriteria))->toOthers();

        return response()->json([
            'status'  => 'success',
            'message' => 'data berhasil disimpan.',
            'data'    => $subjectPassingGradeCriteria,
        ]);
    }

    // function subject passing grade criteria management edit
    public function subjectPassingGradeCriteriaEdit(Request $request, $schoolName, $schoolId, $subjectPassingGradeCriteriaId)
    {
        $validator = Validator::make($request->all(), [
            'kkm_value' => 'required|integer|min:1|max:100',
        ], [
            'kkm_value.required' => 'Nilai KKM tidak boleh kosong.',
            'kkm_value.min' => 'Nilai KKM harus lebih dari 0.',
            'kkm_value.max' => 'Nilai KKM tidak boleh lebih dari 100.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $subjectPassingGradeCriteria = SubjectPassingGradeCriteria::findOrFail($subjectPassingGradeCriteriaId);

            $subjectPassingGradeCriteria->update([
                'kkm_value' => $request->kkm_value,
            ]);
        } catch (QueryException $e) {
            if ($e->getCode() == 23000) {
                return response()->json([
                    'status' => 'error',
                    'errors' => [
                        'mapel_id' => ['Nilai KKM pada mata pelajaran di tahun ajaran ini telah terdaftar.']
                    ]
                ], 422);
            }

            throw $e;
        }

        broadcast(new LmsSubjectPassingGradeCriteria('SubjectPassingGradeCriteria', 'edit', $subjectPassingGradeCriteria))->toOthers();

        return response()->json([
            'status'  => 'success',
            'message' => 'data berhasil diubah.',
            'data'    => $subjectPassingGradeCriteria,
        ]);
    }

    // function bulkUpload syllabus (EXCEL)
    public function bulkUploadSubjectPassingGradeCriteria(Request $request, $schoolName, $schoolId)
    {
        $validator = Validator::make($request->all(), [
            'bulkUpload-subject-passing-grade-criteria' => 'required|file|mimes:xlsx,xls,csv|max:100000',
        ], [
            'bulkUpload-subject-passing-grade-criteria.required' => 'File tidak boleh kosong.',
            'bulkUpload-subject-passing-grade-criteria.mimes' => 'Format file harus .xlsx.',
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
            Excel::import(new SubjectPassingGradeCriteriaSheetImport($userId, $schoolId, $request->file('bulkUpload-subject-passing-grade-criteria')), 
            $request->file('bulkUpload-subject-passing-grade-criteria'));

            return response()->json([
                'status' => 'success',
                'message' => 'Import file berhasil.',
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
}