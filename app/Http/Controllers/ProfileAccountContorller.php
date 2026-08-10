<?php

namespace App\Http\Controllers;

use App\Models\StudentSchoolClass;
use App\Models\UserAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProfileAccountContorller extends Controller
{
    public function index($role, $schoolName = null, $schoolId = null)
    {
        $user = Auth::user();

        // STUDENT QUERY
        $studentSchoolClass = StudentSchoolClass::with('SchoolClass')->where('student_id', $user->id)->where('student_class_status', 'active')->where(function ($query) {
            $query->whereNull('academic_action')->orWhere('academic_action', '');
        })->first();

        return view('features.lms.profile-account.profile-user', compact('role', 'schoolName', 'schoolId', 'studentSchoolClass'));
    }

    // update personal information student
    public function updatePersonalInformationStudent(Request $request, $role, $schoolName, $schoolId, $userId)
    {
        $validator = Validator::make($request->all(), [
            'nama_lengkap' => 'required',
            'no_hp' => [
                'required',
                Rule::unique('user_accounts', 'no_hp')->ignore($userId),
            ],
            'personal_email' => 'required|email',
        ], [
            'nama_lengkap.required' => 'Nama lengkap harus diisi.',
            'no_hp.required' => 'Nomor HP harus diisi.',
            'no_hp.unique' => 'Nomor HP telah terdaftar.',
            'personal_email.required' => 'Email pribadi harus diisi.',
            'personal_email.email' => 'Format email tidak valid.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $dataStudentProfile = UserAccount::with('StudentProfile')->findOrFail($userId);

        $dataStudentProfile->StudentProfile->update([
            'nama_lengkap' => $request->nama_lengkap,
            'personal_email' => $request->personal_email,
        ]);

        $dataStudentProfile->update([
            'no_hp' => $request->no_hp,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Data berhasil diperbarui.',
            'data' => [
                'nama_lengkap' => $dataStudentProfile->StudentProfile->nama_lengkap,
                'no_hp' => $dataStudentProfile->no_hp,
                'personal_email' => $dataStudentProfile->StudentProfile->personal_email,
            ]
        ]);
    }

    // update personal information office
    public function updatePersonalInformationOffice(Request $request, $role, $userId)
    {
        $validator = Validator::make($request->all(), [
            'nama_lengkap' => 'required',
            'no_hp' => [
                'required',
                Rule::unique('user_accounts', 'no_hp')->ignore($userId),
            ],
        ], [
            'nama_lengkap.required' => 'Nama lengkap harus diisi.',
            'no_hp.required' => 'Nomor HP harus diisi.',
            'no_hp.unique' => 'Nomor HP telah terdaftar.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $dataOfficeProfile = UserAccount::with('OfficeProfile')->findOrFail($userId);

        $dataOfficeProfile->OfficeProfile->update([
            'nama_lengkap' => $request->nama_lengkap,
        ]);

        $dataOfficeProfile->update([
            'no_hp' => $request->no_hp,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Data berhasil diperbarui.',
            'data' => [
                'nama_lengkap' => $dataOfficeProfile->OfficeProfile->nama_lengkap,
                'no_hp' => $dataOfficeProfile->no_hp,
            ]
        ]);
    }

    // update personal information school staff
    public function updatePersonalInformationSchoolStaff(Request $request, $role, $schoolName, $schoolId, $userId)
    {
        $validator = Validator::make($request->all(), [
            'nama_lengkap' => 'required',
            'no_hp' => [
                'required',
                Rule::unique('user_accounts', 'no_hp')->ignore($userId),
            ],
            'personal_email' => 'required|email',
            'nik' => 'required',
        ], [
            'nama_lengkap.required' => 'Nama lengkap harus diisi.',
            'no_hp.required' => 'Nomor HP harus diisi.',
            'no_hp.unique' => 'Nomor HP telah terdaftar.',
            'personal_email.required' => 'Email pribadi harus diisi.',
            'personal_email.email' => 'Format email tidak valid.',
            'nik.required' => 'NIK harus diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $dataSchoolStaffProfile = UserAccount::with('StudentProfile')->findOrFail($userId);

        $dataSchoolStaffProfile->SchoolStaffProfile->update([
            'nama_lengkap' => $request->nama_lengkap,
            'personal_email' => $request->personal_email,
            'nik' => $request->nik
        ]);

        $dataSchoolStaffProfile->update([
            'no_hp' => $request->no_hp,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Data berhasil diperbarui.',
            'data' => [
                'nama_lengkap' => $dataSchoolStaffProfile->SchoolStaffProfile->nama_lengkap,
                'no_hp' => $dataSchoolStaffProfile->no_hp,
                'personal_email' => $dataSchoolStaffProfile->SchoolStaffProfile->personal_email,
                'nik' => $dataSchoolStaffProfile->SchoolStaffProfile->nik
            ]
        ]);
    }

    // update personal information parent
    public function updatePersonalInformationParent(Request $request, $role, $schoolName, $schoolId, $userId)
    {
        $validator = Validator::make($request->all(), [
            'nama_lengkap' => 'required',
            'no_hp' => [
                'required',
                Rule::unique('user_accounts', 'no_hp')->ignore($userId),
            ],
        ], [
            'nama_lengkap.required' => 'Nama lengkap harus diisi.',
            'no_hp.required' => 'Nomor HP harus diisi.',
            'no_hp.unique' => 'Nomor HP telah terdaftar.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $dataParentProfile = UserAccount::with('OfficeProfile')->findOrFail($userId);

        $dataParentProfile->ParentProfile->update([
            'nama_lengkap' => $request->nama_lengkap,
        ]);

        $dataParentProfile->update([
            'no_hp' => $request->no_hp,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Data berhasil diperbarui.',
            'data' => [
                'nama_lengkap' => $dataParentProfile->ParentProfile->nama_lengkap,
                'no_hp' => $dataParentProfile->no_hp,
            ]
        ]);
    }

    // update personal information school foundation (yayasan sekolah)
    public function updatePersonalInformationSchoolFoundation(Request $request, $role, $userId)
    {
        $validator = Validator::make($request->all(), [
            'nama_lengkap' => 'required',
            'no_hp' => [
                'required',
                Rule::unique('user_accounts', 'no_hp')->ignore($userId),
            ],
        ], [
            'nama_lengkap.required' => 'Nama lengkap harus diisi.',
            'no_hp.required' => 'Nomor HP harus diisi.',
            'no_hp.unique' => 'Nomor HP telah terdaftar.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $dataSchoolFoundationProfile = UserAccount::with('SchoolFoundationProfile')->findOrFail($userId);

        $dataSchoolFoundationProfile->SchoolFoundationProfile->update([
            'nama_lengkap' => $request->nama_lengkap,
        ]);

        $dataSchoolFoundationProfile->update([
            'no_hp' => $request->no_hp,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Data berhasil diperbarui.',
            'data' => [
                'nama_lengkap' => $dataSchoolFoundationProfile->SchoolFoundationProfile->nama_lengkap,
                'no_hp' => $dataSchoolFoundationProfile->no_hp,
            ]
        ]);
    }

    public function resetPasswordView($role, $schoolName = null, $schoolId = null) {

        return view('features.lms.profile-account.settings.reset-password', compact('role', 'schoolName', 'schoolId'));
    }

    public function resetPasswordUpdate(Request $request, $role, $schoolName = null, $schoolId = null)
    {
        $dataUser = UserAccount::findOrFail(Auth::user()->id);

        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required',
            'new_password_confirmation' => 'required',
        ], [
            'current_password.required' => 'Kata sandi lama tidak boleh kosong.',
            'new_password.required' => 'Kata sandi baru tidak boleh kosong.',
            'new_password_confirmation.required' => 'Konfirmasi kata sandi baru tidak boleh kosong.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Kata sandi lama salah
        if (!Hash::check($request->current_password, $dataUser->password)) {
            return response()->json([
                'status' => 'error',
                'errors' => [
                    'current_password' => [
                        'Kata sandi lama salah.'
                    ]
                ]
            ], 422);
        }

        // Konfirmasi password tidak sama
        if ($request->new_password !== $request->new_password_confirmation) {
            return response()->json([
                'status' => 'error',
                'errors' => [
                    'new_password_confirmation' => [
                        'Konfirmasi kata sandi baru tidak sesuai.'
                    ]
                ]
            ], 422);
        }

        // Password baru sama dengan password lama
        if (Hash::check($request->new_password, $dataUser->password)) {
            return response()->json([
                'status' => 'error',
                'errors' => [
                    'new_password' => [
                        'Kata sandi baru tidak boleh sama dengan kata sandi lama.'
                    ]
                ]
            ], 422);
        }

        $dataUser->update([
            'password' => bcrypt($request->new_password),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Kata sandi berhasil diubah.',
        ]);
    }
}
