<?php

namespace App\Http\Controllers;

use App\Events\ActivateQuestionBankPG;
use App\Events\BankSoalLmsUploaded;
use App\Events\LmsSchoolSubscription;
use App\Events\LmsManagementAccount;
use App\Events\LmsManagementClass;
use App\Events\LmsManagementMajors;
use App\Events\LmsManagementStudentInClass;
use App\Models\Fase;
use App\Models\Kurikulum;
use App\Models\LmsQuestionBank;
use App\Models\SchoolClass;
use App\Models\SchoolLmsSubscription;
use App\Models\SchoolMajor;
use App\Models\SchoolPartner;
use App\Models\SchoolStaffProfile;
use App\Models\StudentSchoolClass;
use App\Models\UserAccount;
use App\Services\LMS\BankSoalWordImportService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class LmsController extends Controller
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

    // function lms school subscription view
    public function lmsSchoolSubscriptionView()
    {
        return view('features.lms.administrator.lms-school-subscription');
    }

    // function pagiante lms school subscription
    public function paginateLmsSchoolSubscription(Request $request)
    {
        $today = now()->format('Y-m-d');

        $lmsSchoolSubscription = SchoolLmsSubscription::whereHas('Transaction', function ($query) {
            $query->where('transaction_status', 'Berhasil');
        })->where('end_date', '<', $today)->get();

        if ($lmsSchoolSubscription) {
            foreach ($lmsSchoolSubscription as $history) {
                $history->update([
                    'subscription_status' => 'tidak_aktif'
                ]);
            }
        }

        $lmsSchoolSubscription = SchoolPartner::with(['UserAccount.SchoolStaffProfile', 'SchoolLmsSubscription' => function ($query) {
            $query->whereHas('transaction', function ($q) {
                $q->where('transaction_status', 'Berhasil');
            })->orderByDesc('start_date')->limit(1); // ambil subscription terbaru
        }
        ])->orderBy('updated_at', 'desc');


        // Filter school
        if ($request->filled('search_school')) {
            $search = $request->search_school;
            $lmsSchoolSubscription->where('nama_sekolah', 'LIKE', "%{$search}%");
        }

        $paginated = $lmsSchoolSubscription->paginate(20);

        return response()->json([
            'data' => $paginated->values(), // flat array, bukan nested
            'links' => (string) $paginated->links(),
            'current_page' => $paginated->currentPage(),
            'per_page' => $paginated->perPage(),
            'lmsAcademicManagement' => '/lms/school-subscription/:schoolName/:schoolId/academic-management',
        ]);
    }

    // function activate lms school subscription
    public function lmsSchoolSubscriptionActivate(Request $request, $subscriptionId)
    {
        $subscription = SchoolLmsSubscription::findOrFail($subscriptionId);

        $subscription->update([
            'subscription_status' => $request->subscription_status,
        ]);

        broadcast(new LmsSchoolSubscription($subscription))->toOthers();

        return response()->json([
            'status' => 'success',
            'message' => 'Subscription status updated successfully',
            'subscription' => $subscription
        ]);
    }

    // function lms academic management
    public function lmsAcademicManagementView($schoolName, $schoolId)
    {
        return view('features.lms.administrator.academic-management.lms-academic-management', compact('schoolName', 'schoolId'));
    }

    // function paginate lms academic management
    public function paginateLmsAcademicManagement($schoolName, $schoolId)
    {
        $users = UserAccount::with(['StudentProfile', 'SchoolStaffProfile'])->where(function ($query) use ($schoolId) {
            $query->whereHas('StudentProfile', function ($q) use ($schoolId) {
                $q->where('school_partner_id', $schoolId);
            })->orWhereHas('SchoolStaffProfile', function ($q) use ($schoolId) {
                $q->where('school_partner_id', $schoolId);
            });
        })->get();

        $groupedRoles = $users->groupBy('role');

        $getSchool = SchoolPartner::with('UserAccount.SchoolStaffProfile')->where('id', $schoolId)->first();

        $countUsers = $users->count();

        return response()->json([
            'data' => $groupedRoles->values(),
            'schoolIdentity' => $getSchool,
            'countUsers' => $countUsers,
            'lmsRoleManagement' => '/lms/school-subscription/:schoolName/:schoolId/management-role-account/',
            'lmsQuestionBankManagement' => '/lms/school-subscription/:schoolName/:schoolId/question-bank-management/',
        ]);
    }

    // function lms management roles view
    public function lmsManagementRolesView($schoolName, $schoolId)
    {
        return view('Features.lms.administrator.lms-school-subscription-management-role-account', compact('schoolName', 'schoolId'));
    }

    // function paginate lms management roles
    public function paginateLmsSchoolSubscriptionRoleAccount(Request $request, $schoolName, $schoolId)
    {
        $users = UserAccount::with(['StudentProfile', 'SchoolStaffProfile'])->where(function ($query) use ($schoolId) {
            $query->whereHas('StudentProfile', function ($q) use ($schoolId) {
                $q->where('school_partner_id', $schoolId);
            })->orWhereHas('SchoolStaffProfile', function ($q) use ($schoolId) {
                $q->where('school_partner_id', $schoolId);
            });
        })->get();

        $groupedRoles = $users->groupBy('role');

        $getSchool = SchoolPartner::with('UserAccount.SchoolStaffProfile')->where('id', $schoolId)->first();

        $countUsers = $users->count();

        return response()->json([
            'data' => $groupedRoles->values(),
            'schoolIdentity' => $getSchool,
            'countUsers' => $countUsers,
            'lmsManagementAccounts' => '/lms/school-subscription/:schoolName/:schoolId/management-role-account/:role/management-accounts',
            'lmsManagementMajors' => '/lms/school-subscription/:schoolName/:schoolId/management-role-account/:role/management-majors',
            'lmsManagementClass' => '/lms/school-subscription/:schoolName/:schoolId/management-role-account/:role/management-class',
        ]);
    }

    // function lms management account view
    public function lmsManagementAccountView($schoolName, $schoolId, $role)
    {
        return view('Features.lms.administrator.lms-school-subscription-management-account', compact('schoolName', 'schoolId', 'role'));
    }

    // function paginate lms management account
    public function paginateLmsSchoolAccount(Request $request, $schoolName, $schoolId, $role)
    {
        $users = UserAccount::with(['StudentProfile', 'SchoolStaffProfile'])->where(function ($query) use ($schoolId) {
            $query->whereHas('StudentProfile', function ($q) use ($schoolId) {
                $q->where('school_partner_id', $schoolId);
            })->orWhereHas('SchoolStaffProfile', function ($q) use ($schoolId) {
                $q->where('school_partner_id', $schoolId);
            });
        })->where('role', $role);

        // Filter school
        if ($request->filled('search_user')) {
            $search = $request->search_user;

            $users->where(function ($q) use ($search) {
                $q->whereHas('StudentProfile', function ($s) use ($search) {
                    $s->where('nama_lengkap', 'LIKE', "%{$search}%");
                })->orWhereHas('SchoolStaffProfile', function ($s) use ($search) {
                    $s->where('nama_lengkap', 'LIKE', "%{$search}%");
                });
            });
        }

        $countUsers = $users->count();

        $paginated = $users->paginate(10);

        $getSchool = SchoolPartner::with('UserAccount.SchoolStaffProfile')->where('id', $schoolId)->first();

        return response()->json([
            'data' => $paginated->items(),
            'links' => (string) $paginated->links(),
            'current_page' => $paginated->currentPage(),
            'per_page' => $paginated->perPage(),
            'schoolIdentity' => $getSchool,
            'countUsers' => $countUsers,
        ]);
    }

    // function lms activate account
    public function lmsActivateAccount(Request $request, $schoolId, $id)
    {
        DB::beginTransaction();

        try {
            $user = UserAccount::findOrFail($id);

            // Pastikan ini kepsek
            if ($user->role !== 'Kepala Sekolah') {
                $user->update(['status_akun' => $request->status_akun]);
                DB::commit();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Berhasil mengubah status akun',
                ]);
            }

            // Ambil semua kepsek di sekolah ini
            $kepsekQuery = UserAccount::where('role', 'Kepala Sekolah')
                ->whereHas('SchoolStaffProfile', function ($q) use ($schoolId) {
                    $q->where('school_partner_id', $schoolId);
                });

            $activeKepsek = (clone $kepsekQuery)->where('status_akun', 'aktif')->get();

            // ==========================
            // KASUS 1: AKTIFKAN KEPSEK
            // ==========================
            if ($request->status_akun === 'aktif') {

                // Nonaktifkan kepsek aktif lainnya
                $kepsekQuery
                    ->where('status_akun', 'aktif')
                    ->where('id', '!=', $user->id)
                    ->update(['status_akun' => 'non-aktif']);

                // Aktifkan kepsek ini
                $user->update(['status_akun' => 'aktif']);

                // Update kepsek_id di SchoolPartner
                SchoolPartner::where('id', $schoolId)->update(['kepsek_id' => $user->id]);

                broadcast(new LmsManagementAccount($user))->toOthers();

                DB::commit();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Kepala sekolah berhasil diaktifkan dan kepsek lain dinonaktifkan.',
                ]);
            }

            // ==========================
            // KASUS 2: NONAKTIFKAN KEPSEK
            // ==========================
            if ($request->status_akun === 'non-aktif') {

                // Jika hanya ada 1 kepsek aktif → TOLAK
                if ($activeKepsek->count() <= 1) {
                    DB::rollBack();
                    return response()->json([
                        'status' => 'error',
                        'cannotDeactivateLastKepsek' => true,
                        'message' => 'Minimal harus ada satu Kepala Sekolah yang aktif.',
                    ], 422);
                }

                $user->update(['status_akun' => 'non-aktif']);
                DB::commit();

                broadcast(new LmsManagementAccount($user))->toOthers();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Kepala sekolah berhasil dinonaktifkan',
                ]);
            }

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // function lms management majors view
    public function lmsManagementMajorsView($schoolName, $schoolId, $role)
    {
        return view('Features.lms.administrator.lms-school-subscription-management-majors', compact('schoolName', 'schoolId', 'role'));
    }

    // function paginate lms management majors
    public function paginateLmsSchoolSubscriptionMajors(Request $request, $schoolName, $schoolId, $role)
    {
        $majors = SchoolMajor::withCount([
            'schoolClass as school_class_count' => function ($q) {
                $q->where('status_major', 'active');
            }
        ])->where('school_partner_id', $schoolId)->get();

        $getSchool = SchoolPartner::with('UserAccount.SchoolStaffProfile')->where('id', $schoolId)->first();

        return response()->json([
            'data' => $majors,
            'schoolIdentity' => $getSchool,
            'lmsManagementClass' => '/lms/school-subscription/:schoolName/:schoolId/management-role-account/:role/management-majors/:majorId/management-class',
        ]);
    }

    // function lms management create majors
    public function lmsManagementCreateMajor(Request $request, $schoolName, $schoolId, $role)
    {
        $validator = Validator::make($request->all(), [
            'major_name' => [
                'required',
                Rule::unique('school_majors', 'major_name')->where('school_partner_id', $schoolId),
            ],
            'major_code' => [
                'required',
                Rule::unique('school_majors', 'major_code')->where('school_partner_id', $schoolId),
            ]
        ], [
            'major_name.required' => 'Nama jurusan harus diisi.',
            'major_name.unique' => 'Nama jurusan telah terdaftar pada sekolah ini.',
            'major_code.required' => 'Kode jurusan harus diisi.',
            'major_code.unique' => 'Kode jurusan telah terdaftar pada sekolah ini.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $major = SchoolMajor::create([
            'school_partner_id' => $schoolId,
            'major_name' => request()->major_name,
            'major_code' => request()->major_code,
        ]);

        broadcast(new LmsManagementMajors('SchoolMajor', 'create', $major))->toOthers();

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil menambahkan jurusan',
        ]);
    }

    // function lms management edit major
    public function lmsManagementEditMajor(Request $request, $schoolName, $schoolId, $role, $majorId)
    {
        $validator = Validator::make($request->all(), [
            'major_name' => [
                'required',
                Rule::unique('school_majors', 'major_name')->where('school_partner_id', $schoolId),
            ],
            'major_code' => [
                'required',
            ]
        ], [
            'major_name.required' => 'Nama jurusan harus diisi.',
            'major_name.unique' => 'Nama jurusan telah terdaftar pada sekolah ini.',
            'major_code.required' => 'Kode jurusan harus diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }
        $major = SchoolMajor::findOrFail($majorId);

        $major->update([
            'school_partner_id' => $schoolId,
            'major_name' => $request->major_name,
            'major_code' => $request->major_code,
        ]);

        broadcast(new LmsManagementMajors('SchoolMajor', 'edit', $major));

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil edit jurusan',
        ]);
    }

    // function lms activate major
    public function lmsActivateMajor(Request $request, $id)
    {
        $major = SchoolMajor::findOrFail($id);

        $major->update([
            'status_major' => $request->status_major,
        ]);

        broadcast(new LmsManagementMajors('SchoolMajor', 'activate', $major))->toOthers();

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil mengubah status jurusan',
        ]);
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
                    $q->where('status_class', 'active')
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

    // function lms management students view
    public function lmsManagementStudentsView($schoolName, $schoolId, $role, $classId, $majorId = null)
    {
        return view('Features.lms.administrator.lms-school-subscription-management-students', compact('schoolName', 'schoolId', 'role', 'classId', 'majorId'));
    }

    // function paginate lms management users
    public function paginateLmsSchoolSubscriptionUsers($schoolName, $schoolId, $role, $classId, $majorId = null)
    {
        $getUsersQuery = StudentSchoolClass::with(['UserAccount.StudentProfile', 'SchoolClass', 
        'SchoolClass.UserAccount.SchoolStaffProfile']);

        if ($majorId) {
            $getUsersQuery->with(['SchoolClass.SchoolMajor']);
        }

        $getUsers = $getUsersQuery->whereHas('SchoolClass', function ($query) use ($schoolId) {
            $query->where('school_partner_id', $schoolId);
        })->where('school_class_id', $classId)->get();

        $getSchool = SchoolPartner::with('UserAccount.SchoolStaffProfile')->where('id', $schoolId)->first();

        $academicActionCheck = $getUsers->map(function ($item) {
            $item->has_academic_action = !empty($item->academic_action);
            return $item;
        });;

        return response()->json([
            'data' => $getUsers,
            'schoolIdentity' => $getSchool,
            'academicActionCheck' => $academicActionCheck,
        ]);
    }

    // function activate student in class
    public function lmsActivateStudentInClass(Request $request, $id)
    {
        $studentSchoolClass = StudentSchoolClass::findOrFail($id);

        $studentSchoolClass->update([
            'student_class_status' => $request->student_class_status,
        ]);

        broadcast(new LmsManagementStudentInClass('StudentSchoolClass', 'activate', $studentSchoolClass))->toOthers();

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil mengubah status siswa di kelas',
        ]);
    }

    // function promote class lms management users
    public function promotionClassOptions(Request $request, $schoolId, $majorId = null)
    {
        $currentClassId = $request->class_id;

        $currentClass = SchoolClass::findOrFail($currentClassId);

        // ambil tingkat kelas (7 dari 7.1)
        $currentLevel = $this->extractClassLevel($currentClass->class_name);
        $currentYear  = $currentClass->tahun_ajaran;

        $targetLevel = $currentLevel + 1;

        $classesQuery = SchoolClass::where('school_partner_id', $schoolId)->orderBy('tahun_ajaran');

        if ($majorId) {
            $classesQuery->where('major_id', $majorId);
        }

        // ambil semua kelas sekolah
        $classes = $classesQuery->get()->filter(function ($cls) use ($currentYear, $currentLevel, $targetLevel) {
            // tahun ajaran lebih besar
            if ($cls->tahun_ajaran <= $currentYear) {
                return false;
            }

            $level = $this->extractClassLevel($cls->class_name);

            // hanya memunculkan options 1 tingkat kelas dari kelas sebelumnya
            return $level === $targetLevel;
        })->values(); // reset index

        return response()->json($classes);
    }

    // function repeat class lms management users
    public function repeatClassOptions(Request $request, $schoolId, $majorId = null)
    {
        $currentClassId = $request->class_id;

        $currentClass = SchoolClass::findOrFail($currentClassId);

        // ambil tingkat kelas (7 dari 7.1)
        $currentLevel = $this->extractClassLevel($currentClass->class_name);
        $currentYear  = $currentClass->tahun_ajaran;

        $classesQuery = SchoolClass::where('school_partner_id', $schoolId)->orderBy('tahun_ajaran');

        if ($majorId) {
            $classesQuery->where('major_id', $majorId);
        }

        // ambil semua kelas sekolah
        $classes = $classesQuery->get()->filter(function ($cls) use ($currentYear, $currentLevel) {
            // tahun ajaran lebih besar
            if ($cls->tahun_ajaran <= $currentYear) {
                return false;
            }

            $level = $this->extractClassLevel($cls->class_name);

            // hanya memunculkan options 1 tingkat kelas dari kelas sebelumnya
            return $level === $currentLevel;
        })->values(); // reset index

        return response()->json($classes);
    }

    // function move class lms management users
    public function moveClassOptions(Request $request, $schoolId, $majorId = null)
    {
        $currentClassId = $request->class_id;

        $currentClass = SchoolClass::findOrFail($currentClassId);

        // ambil tingkat kelas (7 dari 7.1)
        $currentLevel = $this->extractClassLevel($currentClass->class_name);
        $currentYear  = $currentClass->tahun_ajaran;

        $classesQuery = SchoolClass::where('school_partner_id', $schoolId)->where('tahun_ajaran', $currentYear)
        ->where('id', '!=', $currentClassId)->orderBy('tahun_ajaran');

        if ($majorId) {
            $classesQuery->where('major_id', $majorId);
        }

        // ambil semua kelas sekolah
        $classes = $classesQuery->get()->filter(function ($cls) use ($currentYear, $currentLevel) {
            $level = $level = $this->extractClassLevel($cls->class_name);;

            // hanya memunculkan options 1 tingkat kelas dari kelas sebelumnya
            return $level === $currentLevel;
        })->values(); // reset index

        return response()->json($classes);
    }

    // function move major lms management users
    public function moveMajorOptions(Request $request, $schoolId, $majorId = null)
    {
        $currentClass = SchoolClass::findOrFail($request->class_id);
        $currentLevel = $this->extractClassLevel($currentClass->class_name);

        $classes = SchoolClass::with(['SchoolMajor'])->where('school_partner_id', $schoolId)
            ->where('tahun_ajaran', $currentClass->tahun_ajaran)
            ->where('id', '!=', $currentClass->id)
            ->when($majorId, fn ($q) => $q->where('major_id', '!=', $majorId))
            ->get()
            ->filter(fn ($cls) =>
                $this->extractClassLevel($cls->class_name) === $currentLevel
            )
            ->values();

        return response()->json($classes);
    }

    // function update lms management promote class
    public function lmsManagementPromoteClass(Request $request, $schoolName, $schoolId, $role, $classId)
    {
        $validator = Validator::make($request->all(), [
            'tahun_ajaran' => 'required',
            'school_class_id' => 'required',
        ], [
            'tahun_ajaran.required' => 'Tahun ajaran harus diisi.',
            'school_class_id.required' => 'Kelas harus diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $explodeStudentIds = explode(',', $request->student_id);

        $studentSchoolClass = StudentSchoolClass::whereIn('student_id', $explodeStudentIds)->where('school_class_id', $classId)->whereNotNull('academic_action')->where('academic_action', '!=', '')
        ->exists();

        if ($studentSchoolClass) {
            return response()->json([
                'status' => 'error',
                'studentSchoolClassCheck' => true,
                'message' => 'tidak dapat menggunakan aksi akademik kembali pada siswa yang telah memiliki keterangan.',
            ], 422);
        } else {
            foreach ($explodeStudentIds as $studentId) {
                StudentSchoolClass::where('student_id', $studentId) ->where('school_class_id', $classId)->update([
                    'student_class_status' => 'inactive',
                    'academic_action' => 'PROMOTED_CLASS',
                ]);
    
                StudentSchoolClass::create([
                    'student_id' => $studentId,
                    'school_class_id' => $request->school_class_id,
                    'student_class_status' => 'active',
                ]);
            }
        }

        broadcast(new LmsManagementStudentInClass('StudentSchoolClass', 'promote-class', $studentSchoolClass))->toOthers();

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil menaikkan kelas',
        ]);
    }

    // function update lms management repeat class
    public function lmsManagementRepeatClass(Request $request, $schoolName, $schoolId, $role, $classId)
    {
        $validator = Validator::make($request->all(), [
            'tahun_ajaran' => 'required',
            'school_class_id' => 'required',
        ], [
            'tahun_ajaran.required' => 'Tahun ajaran harus diisi.',
            'school_class_id.required' => 'Kelas harus diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $explodeStudentIds = explode(',', $request->student_id);

        $studentSchoolClass = StudentSchoolClass::whereIn('student_id', $explodeStudentIds)->where('school_class_id', $classId)->whereNotNull('academic_action')->where('academic_action', '!=', '')
        ->exists();

        if ($studentSchoolClass) {
            return response()->json([
                'status' => 'error',
                'studentSchoolClassCheck' => true,
                'message' => 'tidak dapat menggunakan aksi akademik kembali pada siswa yang telah memiliki keterangan.',
            ], 422);
        } else {
            foreach ($explodeStudentIds as $studentId) {
                StudentSchoolClass::where('student_id', $studentId)->update([
                    'student_class_status' => 'inactive',
                    'academic_action' => 'REPEATED_CLASS',
                ]);
    
                StudentSchoolClass::create([
                    'student_id' => $studentId,
                    'school_class_id' => $request->school_class_id,
                    'student_class_status' => 'active',
                ]);
            }
        }

        broadcast(new LmsManagementStudentInClass('StudentSchoolClass', 'repeat-class', $studentSchoolClass))->toOthers();

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil mengulang kelas',
        ]);
    }

    // function update lms management move class
    public function lmsManagementMoveClass(Request $request, $schoolName, $schoolId, $role, $classId)
    {
        $validator = Validator::make($request->all(), [
            'tahun_ajaran' => 'required',
            'school_class_id' => 'required',
        ], [
            'tahun_ajaran.required' => 'Tahun ajaran harus diisi.',
            'school_class_id.required' => 'Kelas harus diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $explodeStudentIds = explode(',', $request->student_id);

        $studentSchoolClass = StudentSchoolClass::whereIn('student_id', $explodeStudentIds)->where('school_class_id', $classId)->whereNotNull('academic_action')->where('academic_action', '!=', '')
        ->exists();

        if ($studentSchoolClass) {
            return response()->json([
                'status' => 'error',
                'studentSchoolClassCheck' => true,
                'message' => 'tidak dapat menggunakan aksi akademik kembali pada siswa yang telah memiliki keterangan.',
            ], 422);
        } else {
            foreach ($explodeStudentIds as $studentId) {
                StudentSchoolClass::where('student_id', $studentId)->update([
                    'student_class_status' => 'inactive',
                    'academic_action' => 'TRANSFERRED_CLASS',
                ]);
    
                StudentSchoolClass::create([
                    'student_id' => $studentId,
                    'school_class_id' => $request->school_class_id,
                    'student_class_status' => 'active',
                ]);
            }
        }

        broadcast(new LmsManagementStudentInClass('StudentSchoolClass', 'move-class', $studentSchoolClass))->toOthers();

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil memindahkan kelas',
        ]);
    }

    // function update lms management move major
    public function lmsManagementMoveMajor(Request $request, $schoolName, $schoolId, $role, $classId)
    {
        $validator = Validator::make($request->all(), [
            'tahun_ajaran' => 'required',
            'major_id' => 'required',
            'school_class_id' => 'required',
        ], [
            'tahun_ajaran.required' => 'Tahun ajaran harus diisi.',
            'major_id.required' => 'Jurusan harus diisi.',
            'school_class_id.required' => 'Kelas harus diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $explodeStudentIds = explode(',', $request->student_id);
        $majorId = $request->major_id;

        $studentSchoolClass = StudentSchoolClass::whereIn('student_id', $explodeStudentIds)->where('school_class_id', $classId)->whereNotNull('academic_action')->where('academic_action', '!=', '')
        ->exists();

        if ($studentSchoolClass) {
            return response()->json([
                'status' => 'error',
                'studentSchoolClassCheck' => true,
                'message' => 'tidak dapat menggunakan aksi akademik kembali pada siswa yang telah memiliki keterangan.',
            ], 422);
        } else {
            foreach ($explodeStudentIds as $studentId) {
                StudentSchoolClass::where('student_id', $studentId)->update([
                    'student_class_status' => 'inactive',
                    'academic_action' => 'TRANSFERRED_MAJOR',
                ]);
    
                StudentSchoolClass::create([
                    'student_id' => $studentId,
                    'school_class_id' => $request->school_class_id,
                    'major_id' => $majorId,
                    'student_class_status' => 'active',
                ]);
            }
        }

        broadcast(new LmsManagementStudentInClass('StudentSchoolClass', 'move-major', $studentSchoolClass))->toOthers();

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil memindahkan jurusan',
        ]);
    }

    // function question bank management view
    public function lmsQuestionBankManagementView($schoolName = null, $schoolId = null)
    {
        $getCurriculum = Kurikulum::all();

        return view('features.lms.administrator.question-bank-management.lms-question-bank-management', compact('schoolName', 'schoolId', 'getCurriculum'));
    }

    // function paginate bank soal
    public function paginateLmsQuestionBankManagement(Request $request, $schoolName = null, $schoolId = null)
    {
        $users = UserAccount::with(['StudentProfile', 'SchoolStaffProfile'])->where(function ($query) use ($schoolId) {
            $query->whereHas('StudentProfile', function ($q) use ($schoolId) {
                $q->where('school_partner_id', $schoolId);
            })->orWhereHas('SchoolStaffProfile', function ($q) use ($schoolId) {
                $q->where('school_partner_id', $schoolId);
            });
        })->get();

        $getQuestions = LmsQuestionBank::with(['UserAccount', 'UserAccount.OfficeProfile', 'UserAccount.SchoolStaffProfile','Kurikulum', 'Kelas', 'Mapel', 'Bab', 'SubBab', 'SchoolPartner'])
        ->orderBy('created_at', 'desc');

        if ($schoolId) {
            $getQuestions->where(function ($q) use ($schoolId) {
                $q->where('school_partner_id', $schoolId)
                ->orWhereNull('school_partner_id');
            });
        } else {
            $getQuestions->whereNull('school_partner_id');
        }

        $rows = $getQuestions->get()->groupBy(fn ($q) => $q->sub_bab_id.'-'.$q->school_partner_id)->values();

        // Pagination manual
        $page = $request->get('page', 1);
        $perPage = 20;

        $paged = $rows->slice(
            ($page - 1) * $perPage,
            $perPage
        )->values();

        $paginated = new LengthAwarePaginator(
            $paged,
            $rows->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $getSchool = SchoolPartner::with('UserAccount.SchoolStaffProfile')->where('id', $schoolId)->first();

        $countUsers = $users->count();

        return response()->json([
            'data' => $paginated->values(),
            'links' => (string) $paginated->links(),
            'current_page' => $paginated->currentPage(),
            'per_page' => $paginated->perPage(),
            'schoolIdentity' => $getSchool,
            'countUsers' => $countUsers,
            'source' => $source ?? null,
            'lmsReviewQuestion' => '/lms/question-bank-management/source/:source/review/:subBabId',
            'lmsReviewQuestionBySchool' => '/lms/school-subscription/question-bank-management/source/:source/review/:subBabId/:schoolName/:schoolId',
        ]);
    }

    // function bank soal store UH, ASTS, ASAS
    public function lmsQuestionBankManagementStore(Request $request)
    {
        return app(BankSoalWordImportService::class)->bankSoalImportService($request);
    }

    // function activate bank soal
    public function lmsActivateQuestionBank(Request $request, $subBabId, $source)
    {
        $affectedRows = LmsQuestionBank::where('sub_bab_id', $subBabId)->where('question_source', $source)->update([
            'status_bank_soal' => $request->status_bank_soal
        ]);

        broadcast(new ActivateQuestionBankPG($subBabId,$source,$request->status_bank_soal,$affectedRows))->toOthers();

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil mengubah status bank soal',
        ]);
    }

    // function bank soal detail view
    public function lmsQuestionBankManagementDetailView($source, $subBabId, $schoolName = null, $schoolId = null)
    {
        return view('features.lms.administrator.question-bank-management.lms-question-bank-management-detail', compact('source', 'subBabId', 'schoolName', 'schoolId'));
    }

    // function paginate bank soal detail
    public function paginateReviewQuestionBank($source, $subBabId, $schoolName = null, $schoolId = null)
    {
        $getQuestions = LmsQuestionBank::with(['UserAccount', 'UserAccount.OfficeProfile', 'UserAccount.SchoolStaffProfile', 'Kurikulum', 'Kelas', 'Mapel', 'Bab', 'SubBab', 'SchoolPartner'])
            ->where('sub_bab_id', $subBabId)->where('question_source', $source);

        $grouped = $getQuestions->get()->groupBy('questions');

        $videoIds = [];

        // Loop untuk mendapatkan ID video dari URL
        foreach ($grouped as $groupedSoal) {
            $videoId = null;

            // Cari explanation yang mengandung url video menggunakan regex, lalu mengambil 1 data pertama dari masing" array group soal.
            if (preg_match('/youtu\.be\/([a-zA-Z0-9_-]{11})|youtube\.com\/.*v=([a-zA-Z0-9_-]{11})/', $groupedSoal[0]['explanation'], $matches)) {
                $videoId = $matches[1] ?? $matches[2];
            }

            // Menyiapkan array untuk ID video
            $videoIds[] = $videoId;
        }

        return response()->json([
            'data' => $grouped->values(),
            'videoIds' => $videoIds,
        ]);
    }
}