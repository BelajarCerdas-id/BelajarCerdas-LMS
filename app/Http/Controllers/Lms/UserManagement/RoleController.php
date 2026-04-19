<?php
namespace App\Http\Controllers\Lms\UserManagement;

use App\Http\Controllers\Controller;
use App\Models\SchoolPartner;
use App\Models\UserAccount;
use Illuminate\Http\Request;

class RoleController extends Controller
{
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
}