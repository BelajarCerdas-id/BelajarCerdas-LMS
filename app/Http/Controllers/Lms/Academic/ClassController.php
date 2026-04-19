<?php

namespace App\Http\Controllers\Lms\Academic;

use App\Events\LmsManagementClass;
use App\Http\Controllers\Controller;
use App\Models\Fase;
use App\Models\SchoolClass;
use App\Models\SchoolPartner;
use App\Models\SchoolStaffProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ClassController extends Controller
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
    
    // function lms management class view
    public function lmsManagementClassView($schoolName, $schoolId, $role, $majorId = null)
    {
        $getSchool = SchoolPartner::where('id', $schoolId)->first();

        $phaseMap = [
            'SD' => ['fase a', 'fase b', 'fase c'],
            'MI' => ['fase a', 'fase b', 'fase c'],
            'SMP' => ['fase d'],
            'MTS' => ['fase d'],
            'SMA' => ['fase e', 'fase f'],
            'SMK' => ['fase e', 'fase f'],
            'MA' => ['fase e', 'fase f'],
            'MAK' => ['fase e', 'fase f'],
        ];

        $allowedPhases = $phaseMap[$getSchool->jenjang_sekolah] ?? [];

        $phases = Fase::whereIn(DB::raw('LOWER(kode)'), $allowedPhases)->get();

        return view('Features.lms.administrator.lms-school-subscription-management-class', compact('schoolName', 'schoolId', 'role', 'majorId', 'phases'));
    }

    // function paginate lms management class
    public function paginateLmsSchoolSubscriptionClass(Request $request, $schoolName, $schoolId, $role, $majorId = null) 
    {
        $getSchool = SchoolPartner::with('UserAccount.SchoolStaffProfile')->where('id', $schoolId)->first();

        $startLevelMap = [
            'SD' => 1,
            'MI' => 1,
            'SMP' => 7,
            'MTS' => 7,
            'SMA' => 10,
            'SMK' => 10,
            'MA' => 10,
            'MAK' => 10
        ];

        $defaultLevel = $startLevelMap[$getSchool->jenjang_sekolah] ?? 1;

        // level dari dropdown (optional)
        $selectedClass = $request->filled('search_class') ? (int) $request->search_class : $defaultLevel;
        $selectedYear = $request->filled('search_year') ? $request->search_year : SchoolClass::where('school_partner_id', $schoolId)
        ->orderBy('tahun_ajaran')->value('tahun_ajaran');

        $getClassQuery = SchoolClass::with(['UserAccount', 'UserAccount.SchoolStaffProfile', 'Kelas'])
            ->withCount([
                'StudentSchoolClass as student_school_class_count' => function ($q) {
                    $q->where('student_class_status', 'active')
                    ->where(function ($sub) {
                        $sub->whereNull('academic_action')
                            ->orWhere('academic_action', '');
                    });
                }
            ])
            ->where('school_partner_id', $schoolId)
            ->where('tahun_ajaran', $selectedYear);

        if ($majorId) {
            $getClassQuery->where('major_id', $majorId);
        }        

        $getClass = $getClassQuery->get()->filter(function ($class) use ($selectedClass) {
            return $this->extractClassLevel($class->class_name) === $selectedClass;
        })->values();


        $className = SchoolClass::where('school_partner_id', $schoolId)->pluck('class_name')->map(function ($className) {
            return $this->extractClassLevel($className);
        })->unique()->sort()->values();

        // ambil tahun ajaran berdasarkan tingkat kelas
        $tahunAjaran = SchoolClass::where('school_partner_id', $schoolId)->when($majorId, function ($q) use ($majorId) {
            $q->where('major_id', $majorId);
        })->get()->filter(function ($class) use ($selectedClass) {
            if (!$selectedClass) return true;
                return $this->extractClassLevel($class->class_name) === $selectedClass;
            })->pluck('tahun_ajaran')->unique()->sort()->values();

        return response()->json([
            'data' => $getClass,
            'schoolIdentity' => $getSchool,
            'className' => $className,
            'tahunAjaran' => $tahunAjaran,
            'selectedYear' => $selectedYear,
            'selectedClass' => $selectedClass,
            'lmsManagementStudentsWithMajor' => '/lms/school-subscription/:schoolName/:schoolId/management-role-account/:role/management-class/:classId/management-majors/:majorId/management-students',
            'lmsManagementStudentsNoMajor' => '/lms/school-subscription/:schoolName/:schoolId/management-role-account/:role/management-class/:classId/management-students',
        ]);
    }

    // function lms management create class
    public function lmsManagementCreateClass(Request $request, $schoolName, $schoolId, $role, $majorId = null)
    {
        // Rule dasar yang selalu berlaku
        $rules = [
            'fase_id' => 'required',
            'kelas_id' => 'required',
            'akun_wali_kelas' => 'required|email|regex:/^[A-Za-z0-9._%+-]+@belajarcerdas\.id$/',
            'tahun_ajaran' => 'required',
        ];

        // Membuat rule unique untuk class_name
        $classNameRule = Rule::unique('school_classes', 'class_name')->where('tahun_ajaran', $request->tahun_ajaran)->where('school_partner_id', $schoolId);

        // Jika MAJOR ID ada (kelas berbasis jurusan),
        if ($majorId) {
            $classNameRule->where('major_id', $majorId);
        }

        // Menambahkan rule class_name ke dalam rules utama
        $rules['class_name'] = ['required', $classNameRule];

        // Tentukan pesan error unique berdasarkan ada/tidaknya jurusan
        $classNameUniqueMessage = $majorId ? 'Kelas telah terdaftar pada tahun ajaran dan jurusan ini.' : 'Kelas telah terdaftar pada tahun ajaran ini.';

        $validator = Validator::make(
            $request->all(),
            $rules,
            [
                'fase_id.required' => 'Fase harus diisi.',
                'kelas_id.required' => 'Kelas harus diisi.',
                'class_name.required' => 'Nama kelas harus diisi.',
                'class_name.unique'   => $classNameUniqueMessage,
                'akun_wali_kelas.required' => 'Akun wali kelas harus diisi',
                'akun_wali_kelas.email'    => 'Format email harus @belajarcerdas.id.',
                'akun_wali_kelas.regex'    => 'Format email harus @belajarcerdas.id.',
                'tahun_ajaran.required'    => 'Tahun ajaran harus diisi.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $getWaliKelas = SchoolStaffProfile::whereHas('UserAccount', function ($query) use ($request) {
            $query->where('email', $request->akun_wali_kelas);
        })->where('school_partner_id', $schoolId)->first();

        if (!$getWaliKelas) {
            return response()->json([
                'status' => 'error',
                'errors' => [
                    'akun_wali_kelas' => ['Akun wali kelas tidak terdaftar.']
                ]
            ], 422);
        }

        $class = SchoolClass::create([
            'school_partner_id' => $schoolId,
            'class_name' => $request->class_name,
            'fase_id' => $request->fase_id ?? null,
            'kelas_id' => $request->kelas_id,
            'major_id' => $majorId ?? null,
            'wali_kelas_id' => $getWaliKelas->user_id,
            'tahun_ajaran' => $request->tahun_ajaran,
        ]);

        broadcast(new LmsManagementClass('SchoolClass', 'create', $class))->toOthers();

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil menambahkan kelas',
        ]);
    }

    // function lms management edit class
    public function lmsManagementEditClass(Request $request, $schoolName, $schoolId, $role, $classId, $majorId = null)
    {
        // Rule dasar yang selalu berlaku
        $rules = [
            'fase_id' => 'required',
            'kelas_id' => 'required',
            'akun_wali_kelas' => 'required|email|regex:/^[A-Za-z0-9._%+-]+@belajarcerdas\.id$/',
            'tahun_ajaran' => 'required',
        ];

        // Membuat rule unique untuk class_name
        $classNameRule = Rule::unique('school_classes', 'class_name')->where('tahun_ajaran', $request->tahun_ajaran)->where('school_partner_id', $schoolId)->ignore($classId);

        // Jika MAJOR ID ada (kelas berbasis jurusan),
        if ($majorId) {
            $classNameRule->where('major_id', $majorId);
        }

        // Menambahkan rule class_name ke dalam rules utama
        $rules['class_name'] = ['required', $classNameRule];

        // Tentukan pesan error unique berdasarkan ada/tidaknya jurusan
        $classNameUniqueMessage = $majorId ? 'Kelas telah terdaftar pada tahun ajaran dan jurusan ini.' : 'Kelas telah terdaftar pada tahun ajaran ini.';

        $validator = Validator::make(
            $request->all(),
            $rules,
            [
                'fase_id.required' => 'Fase harus diisi.',
                'kelas_id.required' => 'Kelas harus diisi.',
                'class_name.required' => 'Nama kelas harus diisi.',
                'class_name.unique'   => $classNameUniqueMessage,
                'akun_wali_kelas.required' => 'Akun wali kelas harus diisi',
                'akun_wali_kelas.email'    => 'Format email harus @belajarcerdas.id.',
                'akun_wali_kelas.regex'    => 'Format email harus @belajarcerdas.id.',
                'tahun_ajaran.required'    => 'Tahun ajaran harus diisi.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $getWaliKelas = SchoolStaffProfile::whereHas('UserAccount', function ($query) use ($request) {
            $query->where('email', $request->akun_wali_kelas);
        })->where('school_partner_id', $schoolId)->first();

        if (!$getWaliKelas) {
            return response()->json([
                'status' => 'error',
                'errors' => [
                    'akun_wali_kelas' => ['Akun wali kelas tidak terdaftar.']
                ]
            ], 422);
        }

        $class = SchoolClass::findOrFail($classId);

        $class->update([
            'school_partner_id' => $schoolId,
            'class_name' => $request->class_name,
            'fase_id' => $request->fase_id ?? null,
            'kelas_id' => $request->kelas_id,
            'wali_kelas_id' => $getWaliKelas->user_id,
            'tahun_ajaran' => $request->tahun_ajaran,
        ]);

        broadcast(new LmsManagementClass('SchoolClass', 'edit', $class))->toOthers();

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil edit kelas',
        ]);
    }

    // function lms activate class
    public function LmsActivateClass(Request $request, $id)
    {
        $class = SchoolClass::findOrFail($id);

        $class->update([
            'status_class' => $request->status_class,
        ]);
        
        broadcast(new LmsManagementClass('SchoolClass', 'activate', $class));

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil mengubah status kelas',
        ]);
    }
}