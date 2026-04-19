<?php

namespace App\Http\Controllers\Lms\UserManagement;

use App\Events\LmsManagementAccount;
use App\Http\Controllers\Controller;
use App\Models\SchoolPartner;
use App\Models\UserAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountController extends Controller
{
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

}