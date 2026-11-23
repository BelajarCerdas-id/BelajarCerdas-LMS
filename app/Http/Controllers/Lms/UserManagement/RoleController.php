<?php
namespace App\Http\Controllers\Lms\UserManagement;

use App\Http\Controllers\Controller;
use App\Models\SchoolPartner;
use App\Models\UserAccount;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    // function lms management roles view
    public function lmsManagementRolesView($role, $schoolName, $schoolId)
    {
        $school = SchoolPartner::find($schoolId, [
            'id',
            'nama_sekolah',
            'anticheat_max_strikes',
            'anticheat_auto_lock',
            'anticheat_warning_modal',
        ]);

        return view('features.lms.administrator.lms-school-subscription-management-role-account', compact('role', 'schoolName', 'schoolId', 'school'));
    }

    // update per-school anticheat settings
    public function anticheatSettingsUpdate(Request $request, $role, $schoolName, $schoolId)
    {
        $validated = $request->validate([
            'anticheat_max_strikes' => 'required|integer|min:1|max:20',
            'anticheat_auto_lock' => 'required|boolean',
            'anticheat_warning_modal' => 'required|boolean',
        ]);

        $school = SchoolPartner::findOrFail($schoolId);
        $school->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Pengaturan anti-cheat sekolah berhasil disimpan.',
        ]);
    }

    // function paginate lms management roles
    public function paginateLmsSchoolSubscriptionRoleAccount(Request $request, $schoolName, $schoolId)
    {
        $users = UserAccount::with(['StudentProfile', 'SchoolStaffProfile', 'ParentProfile'])->where(function ($query) use ($schoolId) {
            $query->whereHas('StudentProfile', function ($q) use ($schoolId) {
                $q->where('school_partner_id', $schoolId);
            })
            ->orWhereHas('SchoolStaffProfile', function ($q) use ($schoolId) {
                $q->where('school_partner_id', $schoolId);
            })
            ->orWhereHas('ParentProfile', function ($q) use ($schoolId) {
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
            'lmsManagementAccounts' => '/lms/:role/school-subscription/:schoolName/:schoolId/academic-management/management-role-account/:managedRole/management-accounts',
            'lmsManagementMajors' => '/lms/:role/school-subscription/:schoolName/:schoolId/academic-management/management-role-account/:managedRole/management-majors',
            'lmsManagementClass' => '/lms/:role/school-subscription/:schoolName/:schoolId/academic-management/management-role-account/:managedRole/management-class',
        ]);
    }
}