<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use App\Models\SchoolFoundation;
use App\Models\SchoolFoundationFinanceAccess;
use App\Models\SchoolFoundationProfile;
use App\Models\SchoolPartner;
use App\Models\SchoolStaffProfile;
use App\Models\StudentProfile;
use App\Models\UserAccount;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class FoundationManagementController extends Controller
{
    public function index($role)
    {
        return view('features.lms.administrator.school-foundation.school-foundation-management', compact('role'));
    }

    public function paginateSchoolFoundation($role)
    {
        $query = SchoolFoundation::with([
            'SchoolPartner' => function ($query) {
                $query->withCount([
                    'StudentProfile as student_count' => function ($q) {
                        $q->whereHas('UserAccount', function ($qq) {
                            $qq->where('status_akun', 'aktif');
                        });
                    },  
                    'SchoolStaffProfile as teacher_count' => function ($q) {
                        $q->whereHas('UserAccount', function ($qq) {
                            $qq->where('role', 'Guru')->where('status_akun', 'aktif');
                        });
                    }
                ]);
            }
        ]);

        $schoolFoundation = $query->get();

        $results = collect();

        foreach ($schoolFoundation as $foundation) {

            $teacherCount = SchoolStaffProfile::whereIn('school_partner_id', $foundation->SchoolPartner->pluck('id'))->whereHas('UserAccount', function ($q) {
                $q->where('role', 'Guru')->where('status_akun', 'aktif');
            })->count();

            $studentCount = StudentProfile::whereIn('school_partner_id', $foundation->SchoolPartner->pluck('id'))->whereHas('UserAccount', function ($q) {
                $q->where('status_akun', 'aktif');
            })->count();

            $results[] = [
                'id' => $foundation->id,
                'nama_yayasan' => $foundation->nama_yayasan,
                'logo' => $foundation->logo,
                'school_count' => $foundation->SchoolPartner->count(),
                'teacher_count' => $teacherCount,
                'student_count' => $studentCount,
                'schools' => $foundation->SchoolPartner->map(function ($school) {
                    return [
                        'id'             => $school->id,
                        'nama_sekolah'   => $school->nama_sekolah,
                        'logo'           => $school->logo,
                        'npsn'           => $school->npsn,
                        'student_count'  => $school->student_count,
                        'teacher_count'  => $school->teacher_count,
                    ];
                }),
            ];
        }

        $page = LengthAwarePaginator::resolveCurrentPage();

        $perPage = 20;

        $paginatedResult = new LengthAwarePaginator(
            $results->forPage($page, $perPage)->values(),
            $results->count(),
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );
        
        // summary KPI
        $totalSchoolFoundation = SchoolFoundation::count();
        $totalSchool = SchoolPartner::count();
        $totalTeacher = SchoolStaffProfile::whereHas('UserAccount', function ($q) {
            $q->where('role', 'Guru')->where('status_akun', 'aktif');
        })->count();
        $totalStudent = StudentProfile::whereHas('UserAccount', function ($q) {
            $q->where('status_akun', 'aktif');
        })->count();

        return response()->json([
            'data' => $paginatedResult->items(),
            'links' => (string) $paginatedResult->links(),
            'schoolFoundationEditForm' => '/lms/:role/school-foundation/manage/edit-form/:schoolFoundationId',
            'schoolFoundationAccessControl' => '/lms/:role/school-foundation/manage/access-control/:schoolFoundationId',
            'schoolFoundationFinanceAccess' => '/lms/:role/school-foundation/manage/finance-access-control/:schoolFoundationId',

            'summary' => [
                'total_school_foundation' => $totalSchoolFoundation,
                'total_school' => $totalSchool,
                'total_teacher' => $totalTeacher,
                'total_student' => $totalStudent
            ]
        ]);
    }

    // view school foundation form
    public function viewSchoolFoundationForm($role)
    {
        return view('features.lms.administrator.school-foundation.school-foundation-form', compact('role'));
    }

    // paginate school list
    public function paginateSchoolList(Request $request, $role, $foundationId = null)
    {
        $baseQuery = SchoolPartner::query();

        if ($foundationId) {
            $baseQuery->whereNull('school_foundation_id');
        }

        // total sebelum search
        $total = $baseQuery->count();

        $query = SchoolPartner::with(['SchoolFoundation'])
            ->withCount([
                'studentProfile as student_count' => function ($query) {
                    $query->whereHas('UserAccount', function ($q) {
                        $q->where('status_akun', 'aktif');
                    });
                },
                'SchoolStaffProfile as teacher_count' => function ($query) {
                    $query->whereHas('UserAccount', function ($q) {
                        $q->where('role', 'Guru')->where('status_akun', 'aktif');
                    });
                }
            ]);

        if ($foundationId) {
            $query->whereNull('school_foundation_id');
        }

        if ($request->filled('search_school')) {
            $search = $request->search_school;

            $query->where('nama_sekolah', 'LIKE', "%{$search}%");
        }

        $schools = $query->get();   

        return response()->json([
            'data' => $schools,
            'total' => $total,
        ]);
    }

    // school foundation submit form
    public function schoolFoundationSubmitForm(Request $request, $role)
    {
        $validator = Validator::make($request->all(), [
            'nama_yayasan' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,svg|max:2000',

            'nama_lengkap' => 'required|string|max:255',
            'no_hp' => 'required|string|unique:user_accounts,no_hp',
            'email' => 'required|unique:user_accounts,email',
            'password' => 'required',

            'school_partner_id' => 'required|array|min:1',
            'school_partner_id.*' => 'required|integer|exists:school_partners,id',
        ], [
            'nama_yayasan.required' => 'Harap isi nama yayasan.',

            'nama_lengkap.required' => 'Harap isi nama lengkap.',

            'email.required' => 'Harap isi email akun.',
            'email.unique' => 'Email akun telah terdaftar.',

            'no_hp.required' => 'Harap isi nomor HP.',
            'no_hp.unique' => 'Nomor HP sudah digunakan.',

            'password.required' => 'Harap isi password.',

            'logo.image' => 'File harus berupa gambar.',
            'logo.mimes' => 'Format harus JPG, JPEG, PNG, atau SVG.',
            'logo.max' => 'Ukuran file melebihi batas yang ditentukan.',

            'school_partner_id.required' => 'Harap pilih sekolah.',
            'school_partner_id.min' => 'Harap pilih minimal satu sekolah.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $registeredSchool = SchoolPartner::with('SchoolFoundation')->whereIn('id', $request->school_partner_id)->whereNotNull('school_foundation_id')->get();

        if ($registeredSchool->isNotEmpty()) {

            return response()->json([
                'flag' => 'school_already_has_foundation',
                'message' => 'Beberapa sekolah sudah terhubung dengan yayasan lain.',
                'schools' => $registeredSchool->map(function ($school) {
                    return [
                        'id' => $school->id,
                        'nama_sekolah' => $school->nama_sekolah,
                        'nama_yayasan' => $school->SchoolFoundation?->nama_yayasan,
                    ];
                }),
            ], 422);

        }

        DB::beginTransaction();

        try {
            $logo = null;

            if ($request->hasFile('logo')) {

                $file = $request->file('logo');

                $filename = 'logo_' . time() . '.' . $file->getClientOriginalExtension();

                $destinationPath = public_path('school-foundation-logo');

                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }

                $file->move($destinationPath, $filename);

                $logo = 'school-foundation-logo/' . $filename;
            }

            $schoolFoundation = SchoolFoundation::create([
                'nama_yayasan' => $request->nama_yayasan,
                'logo' => $logo,
            ]);

            $user = UserAccount::create([
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'no_hp' => $request->no_hp,
                'role' => 'Yayasan',
            ]);

            SchoolFoundationProfile::create([
                'user_id' => $user->id,
                'school_foundation_id' => $schoolFoundation->id,
                'nama_lengkap' => $request->nama_lengkap,
            ]);

            SchoolPartner::whereIn('id', $request->school_partner_id)->update([
                'school_foundation_id' => $schoolFoundation->id,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Yayasan berhasil ditambahkan.',
                'data' => $schoolFoundation,
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'flag' => 'server_error',
                'message' => 'Terjadi kesalahan saat menyimpan data.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);

        }
    }

    public function addSchoolToFoundation(Request $request, $role, $schoolFoundationId)
    {
        $validator = Validator::make($request->all(), [
            'school_partner_id' => 'required|array|min:1',
            'school_partner_id.*' => 'required|integer|exists:school_partners,id',
        ], [
            'school_partner_id.required' => 'Harap pilih sekolah.',
            'school_partner_id.min' => 'Harap pilih minimal satu sekolah.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $schoolFoundation = SchoolFoundation::findOrFail($schoolFoundationId);

        DB::beginTransaction();

        try {

            $registeredSchool = SchoolPartner::whereIn('id', $request->school_partner_id)->whereNotNull('school_foundation_id')->get();

            if ($registeredSchool->isNotEmpty()) {

                DB::rollBack();

                return response()->json([
                    'flag' => 'school_already_has_foundation',
                    'message' => 'Beberapa sekolah sudah memiliki yayasan.',
                ], 422);

            }

            SchoolPartner::whereIn('id', $request->school_partner_id)->update([
                'school_foundation_id' => $schoolFoundation->id
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Sekolah berhasil ditambahkan ke yayasan.'
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'flag' => 'server_error',
                'message' => 'Terjadi kesalahan saat menambahkan sekolah.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function removeSchoolFromFoundation(Request $request, $role, $schoolFoundationId, $schoolId)
    {
        $school = SchoolPartner::findOrFail($schoolId);

        DB::beginTransaction();

        try {

            $school->update([
                'school_foundation_id' => null
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Sekolah berhasil dikeluarkan dari yayasan.',
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'flag' => 'server_error',
                'message' => 'Terjadi kesalahan saat mengeluarkan sekolah.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);

        }
    }

    public function viewSchoolFoundationEditForm($role, $schoolFoundationId)
    {
        return view('features.lms.administrator.school-foundation.school-foundation-edit-form', compact('role', 'schoolFoundationId'));
    }

    public function editSchoolFoundationForm(Request $request, $role, $schoolFoundationId)
    {
        $schoolFoundation = SchoolFoundation::findOrFail($schoolFoundationId);

        return response()->json([
            'data' => [
                'id' => $schoolFoundation->id,
                'nama_yayasan' => $schoolFoundation->nama_yayasan,
                'logo' => $schoolFoundation->logo,
            ]
        ]);
    }

    public function editSchoolFoundationSubmitForm(Request $request, $role, $schoolFoundationId)
    {
        $validator = Validator::make($request->all(), [
            'nama_yayasan' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,svg|max:2000',
        ], [
            'nama_yayasan.required' => 'Harap isi nama yayasan.',
            'logo.image' => 'File harus berupa gambar.',
            'logo.mimes' => 'Format harus JPG, JPEG, PNG, atau SVG.',
            'logo.max' => 'Ukuran file melebihi batas yang ditentukan.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $schoolFoundation = SchoolFoundation::findOrFail($schoolFoundationId);
        
        $logo = null;

        if ($request->hasFile('logo')) {

            $file = $request->file('logo');

            $filename = 'logo_' . time() . '.' . $file->getClientOriginalExtension();

            $destinationPath = public_path('school-foundation-logo');

            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $file->move($destinationPath, $filename);

            $logo = 'school-foundation-logo/' . $filename;
        } else {
            $logo = $schoolFoundation->logo;
        }
        
        $schoolFoundation->update([
            'nama_yayasan' => $request->nama_yayasan,
            'logo' => $logo,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Yayasan berhasil diperbarui.',
        ]);
    }

    public function viewSchoolFoundationAccessControl($role, $schoolFoundationId)
    {
        $schoolFoundation = SchoolFoundation::findOrFail($schoolFoundationId);
        return view('features.lms.administrator.school-foundation.school-foundation-access-control', compact('role', 'schoolFoundationId', 'schoolFoundation'));
    }

    public function paginateSchoolFoundationAccessControl(Request $request, $role, $schoolFoundationId)
    {
        $accounts = SchoolFoundationProfile::with(['UserAccount'])->where('school_foundation_id', $schoolFoundationId)->paginate(10);

        return response()->json([
            'data' => $accounts->items(),
            'links' => (string) $accounts->links(),
        ]);
    }

    public function schoolFoundationAccessControlActivate(Request $request, $role, $schoolFoundationId, $userId)
    {
        $user = UserAccount::findOrFail($userId);

        $user->update([
            'status_akun' => $request->status_akun,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Status akun berhasil diperbarui.',
        ]);
    }

    public function toggleSchoolFoundationAccessControl(Request $request, $role, $schoolFoundationId, $profileId)
    {
        $profile = SchoolFoundationProfile::findOrFail($profileId);

        if ($profile->school_foundation_id) {

            $profile->update([
                'school_foundation_id' => null,
            ]);

            $message = 'Akses yayasan berhasil dinonaktifkan.';

        } else {
            $profile->update([
                'school_foundation_id' => $schoolFoundationId,
            ]);

            $message = 'Akses yayasan berhasil diaktifkan.';
        }

        return response()->json([
            'status' => 'success',
            'message' => $message,
        ]);
    }

    public function foundationCreateUser(Request $request, $role, $schoolFoundationId)
    {
        $validator = Validator::make($request->all(), [
            'nama_lengkap' => 'required|string|max:255',
            'no_hp' => 'required|string|unique:user_accounts,no_hp',
            'email' => 'required|unique:user_accounts,email',
            'password' => 'required',
        ], [
            'nama_lengkap.required' => 'Harap isi nama lengkap.',

            'email.required' => 'Harap isi email akun.',
            'email.unique' => 'Email akun telah terdaftar.',

            'no_hp.required' => 'Harap isi nomor HP.',
            'no_hp.unique' => 'Nomor HP sudah digunakan.',

            'password.required' => 'Harap isi password.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {

            $schoolFoundation = SchoolFoundation::findOrFail($schoolFoundationId);

            $user = UserAccount::create([
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'no_hp' => $request->no_hp,
                'role' => 'Yayasan',
            ]);

            SchoolFoundationProfile::create([
                'user_id' => $user->id,
                'school_foundation_id' => $schoolFoundation->id,
                'nama_lengkap' => $request->nama_lengkap,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Akun berhasil ditambahkan.',
                'data' => $schoolFoundation,
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'flag' => 'server_error',
                'message' => 'Terjadi kesalahan saat menyimpan data.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);

        }
    }

    public function loadExistingAccounts(Request $request, $role, $schoolFoundationId)
    {
        $search = $request->search;

        $accounts = SchoolFoundationProfile::with('UserAccount')->whereNull('school_foundation_id')->whereHas('UserAccount', function ($query) use ($search) {
            $query->where('role', 'yayasan')->where('status_akun', 'aktif');

            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('email', 'like', "%{$search}%");
                });
            }
        })->when(!empty($search), function ($query) use ($search) {
            $query->where('nama_lengkap', 'like', "%{$search}%");
        })->latest()->get();

        return response()->json($accounts);
    }

    public function assignExistingAccount($role, $schoolFoundationId, $userId)
    {
        $profile = SchoolFoundationProfile::where('user_id', $userId)->whereNull('school_foundation_id')->first();

        if (!$profile) {
            return response()->json([
                'success' => false,
                'message' => 'Akun tidak ditemukan atau sudah memiliki akses yayasan.'
            ], 422);
        }

        $profile->update([
            'school_foundation_id' => $schoolFoundationId
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Berhasil menambahkan akun ke yayasan.'
        ]);
    }

    public function viewSchoolFoundationFinanceAccess(Request $request, $role, $schoolFoundationId) 
    {
        $schools = SchoolPartner::where('school_foundation_id', $schoolFoundationId)->get();

        return view('features.lms.administrator.school-foundation.school-foundation-finance-access', compact('role', 'schoolFoundationId', 'schools'));
    }

    public function paginateSchoolFoundationFinanceAccess($role, $schoolFoundationId)
    {
        $financeAccessLink = SchoolFoundationFinanceAccess::with(['SchoolPartner'])->whereHas('SchoolPartner', function ($query) use ($schoolFoundationId) {
            $query->where('school_foundation_id', $schoolFoundationId);
        })->get();

        return response()->json([
            'data' => $financeAccessLink
        ]);
    }

    public function schoolFoundationFinanceAccessStore(Request $request, $role, $schoolFoundationId) 
    {
        $validator = Validator::make($request->all(), [
            'school_partner_id' => [
                'required',
                Rule::exists('school_partners', 'id')->where(function ($query) use ($schoolFoundationId) {
                    $query->where('school_foundation_id', $schoolFoundationId);
                }),
            ],

            'link' => [
                'required',
                'url',
                'regex:/^https:\/\/(drive|docs)\.google\.com\//i',
            ],
        ], [
            'school_partner_id.required' => 'Harap pilih sekolah.',
            'school_partner_id.exists' => 'Sekolah tidak ditemukan atau bukan bagian dari yayasan ini.',
            'link.required' => 'Harap isi link file keuangan.',
            'link.url' => 'Link yang dimasukkan tidak valid.',
            'link.regex' => 'Link yang dimasukkan tidak valid.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        // Cek apakah sekolah sudah memiliki link
        $existingAccess = SchoolFoundationFinanceAccess::where('school_partner_id', $request->school_partner_id)->exists();

        if ($existingAccess) {
            return response()->json([
                'errors' => [
                    'school_partner_id' => [
                        'Sekolah tersebut sudah memiliki link file keuangan.'
                    ]
                ]
            ], 422);
        }

        SchoolFoundationFinanceAccess::create([
            'school_partner_id' => $request->school_partner_id,
            'link' => $request->link,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Link file keuangan berhasil disimpan.',
        ]);
    }

    public function schoolFoundationFinanceAccessActivate(Request $request, $role, $schoolFoundationId, $linkId)
    {
        $link = SchoolFoundationFinanceAccess::find($linkId);

        $link->update([
            'status_access' => $request->status_access,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Status akun berhasil diperbarui.',
        ]);
    }

    public function schoolFoundationFinanceAccessEdit(Request $request, $role, $schoolFoundationId, $linkId) 
    {
        $validator = Validator::make($request->all(), [
            'school_partner_id' => [
                'required',
                Rule::exists('school_partners', 'id')->where(function ($query) use ($schoolFoundationId) {
                    $query->where('school_foundation_id', $schoolFoundationId);
                }),
                Rule::unique('school_foundation_finance_accesses', 'school_partner_id')->ignore($linkId),
            ],

            'edit_link' => [
                'required',
                'url',
                'regex:/^https:\/\/(drive|docs)\.google\.com\//i',
            ],
        ], [
            'school_partner_id.required' => 'Harap pilih sekolah.',
            'school_partner_id.exists' => 'Sekolah tidak ditemukan atau bukan bagian dari yayasan ini.',
            'school_partner_id.unique' => 'Sekolah tersebut sudah memiliki akses keuangan.',
            'edit_link.required' => 'Harap isi link file keuangan.',
            'edit_link.url' => 'Link yang dimasukkan tidak valid.',
            'edit_link.regex' => 'Link yang dimasukkan tidak valid.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $link = SchoolFoundationFinanceAccess::where('id', $linkId)->whereHas('SchoolPartner', function ($query) use ($schoolFoundationId) {
            $query->where('school_foundation_id', $schoolFoundationId);
        })->first();

        if (!$link) {
            return response()->json([
                'message' => 'Data akses keuangan tidak ditemukan.'
            ], 404);
        }

        $link->update([
            'link' => $request->edit_link,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Link file keuangan berhasil diperbarui.',
        ]);
    }
}