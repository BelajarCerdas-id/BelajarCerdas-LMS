<?php
namespace App\Http\Controllers\Lms\Academic;

use App\Http\Controllers\Controller;
use App\Models\SchoolPartner;
use App\Models\UserAccount;

class AcademicDashboardController extends Controller
{
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
            'lmsRoleManagement' => '/lms/school-subscription/:schoolName/:schoolId/academic-management/management-role-account',
            'lmsQuestionBankManagement' => '/lms/school-subscription/:schoolName/:schoolId/academic-management/question-bank-management',
            'lmsCurriculumManagementBySchool' => '/lms/school-subscription/:schoolName/:schoolId/academic-management/kurikulum',
            'lmsContentManagement' => '/lms/school-subscription/:schoolName/:schoolId/academic-management/content-management',
            'lmsAssessmentTypeManagement' => '/lms/school-subscription/:schoolName/:schoolId/academic-management/assessment-type-management',
            'lmsTeacherSubjectManagement' => '/lms/school-subscription/:schoolName/:schoolId/academic-management/subject-teacher-management',
            'lmsAssessmentWeightManagement' => '/lms/school-subscription/:schoolName/:schoolId/academic-management/assessment-weight-management',
            'lmsSubjectPassingGradeCriteria' => '/lms/school-subscription/:schoolName/:schoolId/academic-management/subject-passing-grade-criteria-management',
        ]);
    }
}