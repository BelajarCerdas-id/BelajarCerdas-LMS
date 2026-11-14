<?php

namespace App\Http\Controllers;

use App\Http\Requests\Profile\ResetPasswordRequest;
use App\Http\Requests\Profile\UpdateStaffFullPersonalRequest;
use App\Http\Requests\Profile\UpdateStaffPersonalRequest;
use App\Http\Requests\Profile\UpdateStudentPersonalRequest;
use App\Models\StudentSchoolClass;
use App\Models\UserAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileAccountController extends Controller
{
    public function index($role, $schoolName = null, $schoolId = null)
    {
        $user = Auth::user();

        // STUDENT QUERY
        $studentSchoolClass = StudentSchoolClass::with('SchoolClass')
            ->where('student_id', $user->id)
            ->where('student_class_status', 'active')
            ->where(function ($query) {
                $query->whereNull('academic_action')->orWhere('academic_action', '');
            })->first();

        return view('features.lms.profile-account.profile-user', compact('role', 'schoolName', 'schoolId', 'studentSchoolClass'));
    }

    public function updatePersonalInformationStudent(UpdateStudentPersonalRequest $request, $role, $schoolName, $schoolId, $userId): JsonResponse
    {
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
            ],
        ]);
    }

    public function updatePersonalInformationOffice(UpdateStaffPersonalRequest $request, $role, $userId): JsonResponse
    {
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
            ],
        ]);
    }

    public function updatePersonalInformationSchoolStaff(UpdateStaffFullPersonalRequest $request, $role, $schoolName, $schoolId, $userId): JsonResponse
    {
        $dataSchoolStaffProfile = UserAccount::with('SchoolStaffProfile')->findOrFail($userId);

        $dataSchoolStaffProfile->SchoolStaffProfile->update([
            'nama_lengkap' => $request->nama_lengkap,
            'personal_email' => $request->personal_email,
            'nik' => $request->nik,
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
                'nik' => $dataSchoolStaffProfile->SchoolStaffProfile->nik,
            ],
        ]);
    }

    public function updatePersonalInformationParent(UpdateStaffPersonalRequest $request, $role, $schoolName, $schoolId, $userId): JsonResponse
    {
        $dataParentProfile = UserAccount::with('ParentProfile')->findOrFail($userId);

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
            ],
        ]);
    }

    public function updatePersonalInformationSchoolFoundation(UpdateStaffPersonalRequest $request, $role, $userId): JsonResponse
    {
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
            ],
        ]);
    }

    public function resetPasswordView($role, $schoolName = null, $schoolId = null)
    {
        return view('features.lms.profile-account.settings.reset-password', compact('role', 'schoolName', 'schoolId'));
    }

    public function resetPasswordUpdate(ResetPasswordRequest $request, $role, $schoolName = null, $schoolId = null): JsonResponse
    {
        $dataUser = UserAccount::findOrFail(Auth::user()->id);

        if (! Hash::check($request->current_password, $dataUser->password)) {
            return response()->json([
                'status' => 'error',
                'errors' => [
                    'current_password' => [
                        'Kata sandi lama salah.',
                    ],
                ],
            ], 422);
        }

        if ($request->new_password !== $request->new_password_confirmation) {
            return response()->json([
                'status' => 'error',
                'errors' => [
                    'new_password_confirmation' => [
                        'Konfirmasi kata sandi baru tidak sesuai.',
                    ],
                ],
            ], 422);
        }

        if (Hash::check($request->new_password, $dataUser->password)) {
            return response()->json([
                'status' => 'error',
                'errors' => [
                    'new_password' => [
                        'Kata sandi baru tidak boleh sama dengan kata sandi lama.',
                    ],
                ],
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

class_alias(ProfileAccountController::class, 'App\Http\Controllers\ProfileAccountContorller');
