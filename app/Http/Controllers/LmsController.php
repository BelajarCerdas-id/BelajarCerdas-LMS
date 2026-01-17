<?php

namespace App\Http\Controllers;

use App\Events\LmsSchoolSubscription;
use App\Events\LmsManagementAccount;
use App\Events\LmsManagementClass;
use App\Events\LmsManagementMajors;
use App\Events\LmsManagementStudentInClass;
use App\Models\Fase;
use App\Models\SchoolClass;
use App\Models\SchoolLmsSubscription;
use App\Models\SchoolMajor;
use App\Models\SchoolPartner;
use App\Models\SchoolStaffProfile;
use App\Models\StudentSchoolClass;
use App\Models\UserAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class LmsController extends Controller
{
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
            'lmsManagementUsers' => '/lms/school-subscription/:schoolName/:schoolId/management-role-account',
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
}