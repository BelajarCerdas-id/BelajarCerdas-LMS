<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Finance\FinanceContractController;
use App\Http\Controllers\Finance\FinanceDashboardController;
use App\Http\Controllers\Finance\FinanceRevenueController;
use App\Http\Controllers\GradebookAssessmentController;
use App\Http\Controllers\LmsController;
use App\Http\Controllers\MasterAcademicController;
use App\Http\Controllers\SchoolPartnerController;
use App\Http\Controllers\SchoolSyllabusController;
use App\Http\Controllers\ServiceRuleController;
use App\Http\Controllers\StudentAssessmentController;
use App\Http\Controllers\StudentAssessmentExamController;
use App\Http\Controllers\StudentLearningController;
use App\Http\Controllers\StudentSubjectProgressController;
use App\Http\Controllers\SyllabusController;
use App\Http\Controllers\TeacherAssessmentController;
use App\Http\Controllers\TeacherAssessmentGradingController;
use App\Http\Controllers\TeacherContentController;
use App\Http\Controllers\TeacherContentReleaseController;
use App\Http\Controllers\TeacherGradebookController;
use App\Http\Controllers\TeacherQuestionBankController;
use App\Http\Controllers\TeacherQuestionBankReleaseController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\Lms\Academic\AcademicDashboardController;
use App\Http\Controllers\Lms\Academic\ClassController;
use App\Http\Controllers\Lms\Academic\MajorController;
use App\Http\Controllers\Lms\QuestionBank\QuestionBankController;
use App\Http\Controllers\Lms\Academic\StudentSchoolClassController;
use App\Http\Controllers\Lms\AssessmentManagement\AssessmentTypeController;
use App\Http\Controllers\Lms\ContentBank\ContentBankController;
use App\Http\Controllers\Lms\AssessmentManagement\AssessmentWeightController;
use App\Http\Controllers\Lms\Attendances\Student\SubjectAttendanceController;
use App\Http\Controllers\Lms\SubjectPassingGradeCriteria\SubjectPassingGradeCriteriaController;
use App\Http\Controllers\Lms\TeacherSubject\TeacherSubjectController;
use App\Http\Controllers\Lms\UserManagement\AccountController;
use App\Http\Controllers\Lms\UserManagement\RoleController;
use App\Http\Controllers\SchoolAdminDashboardController;
use App\Http\Controllers\StudentDashboardController;
use App\Http\Controllers\TeacherAcademicTranscriptController;
use App\Http\Controllers\TeacherGradeLedgerController;
use App\Http\Middleware\AuthMiddleware;
use App\Http\Middleware\RedirectIfAuthenticated;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HeadmasterController;
use App\Http\Controllers\OfficeManagementController;
use App\Http\Controllers\ParentController;
use App\Http\Controllers\StudentVicePrincipalController;

// ROUTE FALLBACK
Route::fallback(function () {

    if (Auth::check()) {

        $role = Auth::user()->role;

        // jika role nya untuk office, maka gunakan route ini
        if (in_array($role, ['Administrator', 'Finance'])) {
            return redirect()->route('lms.office.dashboard.view', [
                'role' => $role,
            ]);
        } else {
            return redirect()->route('beranda', [
                'role' => $role,
            ]);
        }
    }

    return redirect()->route('login');
});

Route::get('/', fn () => redirect('/login'));

// middleware redirect if authenticated
Route::middleware([RedirectIfAuthenticated::class])->group(function () {
    Route::get('/login', [AuthController::class, 'loginView'])->name('login');
});

// routes auth login & logout
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ROUTES DROPDOWN KURIKULUM, KELAS, MAPEL, BAB, SUB BAB
Route::get('/kelas/{id}', [MasterAcademicController::class, 'getKelas']);

// service for school partner & non school partner
Route::get('/kurikulum/{curriculumId}/service', [MasterAcademicController::class, 'getServiceByKurikulum']);
Route::get('/kurikulum/{curriculumId}/{schoolId}/service', [MasterAcademicController::class, 'getServiceByKurikulum']);

// kelas for school partner & non school partner
Route::get('/kurikulum/{curriculumId}/kelas', [MasterAcademicController::class, 'getKelasByKurikulum']);
Route::get('/kurikulum/{curriculumId}/{schoolId}/kelas', [MasterAcademicController::class, 'getKelasByKurikulum']);

// route dependent dropdown mapel by kelas non school partner & school partner
Route::get('/kelas/{kelasId}/mapel', [MasterAcademicController::class, 'getMapelByKelas']);
Route::get('/kelas/{kelasId}/{schoolId}/mapel', [MasterAcademicController::class, 'getMapelByKelas']);

// route dependent dropdown rombel kelas by kelas
Route::get('/kelas/{kelasId}/rombel-kelas/{schoolId}', [MasterAcademicController::class, 'getRombelByKelas']);

Route::get('/mapel/{mapelId}/bab', [MasterAcademicController::class, 'getBabByMapel']);
Route::get('/bab/{babId}/sub-bab', [MasterAcademicController::class, 'getSubBabByBab']);

Route::get('/service/{service}/rules', [ServiceRuleController::class, 'index']);

// MIDDLEWARE LOGIN
Route::middleware([AuthMiddleware::class])->group(function () {
    // DASHBOARD
    Route::get('/lms/{role}', [DashboardController::class, 'index'])->name('beranda');

    // =========================================================
    // ROUTES LIBRARY (HARUS DI ATAS ROUTE WILDCARD LMS!)
    // =========================================================
    Route::get('/administrator/library', [LibraryController::class, 'administrator'])
        ->name('library.administrator');

    Route::post('/administrator/library/store', [LibraryController::class, 'store'])
        ->name('library.store');

    Route::post('/administrator/library/update/{id}', [LibraryController::class, 'update'])
        ->name('library.update');

    Route::put('/administrator/library/update/{id}', [LibraryController::class, 'update']);

    Route::delete('/library/delete/{id}', [LibraryController::class, 'delete'])
        ->name('library.delete');

    Route::post('/administrator/library/ppt/store', [LibraryController::class, 'storePpt'])
        ->name('ppt.store');

    Route::put('/administrator/library/ppt/update/{id}', [LibraryController::class, 'updatePpt'])
        ->name('ppt.update');

    Route::delete('/administrator/library/ppt/delete/{id}', [LibraryController::class, 'deletePpt'])
        ->name('ppt.delete');

    Route::post('/administrator/library/chapter/store', [LibraryController::class, 'storeChapter'])
        ->name('library.chapter.store');

    // =========================================================
    // ROUTES LIBRARY STUDENT (HARUS DI ATAS ROUTE WILDCARD LMS!)
    // =========================================================
    Route::get('/lms/student/library', [LibraryController::class, 'studentLibrary'])
        ->name('student.library');

    Route::get('/lms/student/library/ppt', [LibraryController::class, 'pptLibrary'])
        ->name('student.library.ppt');

    Route::get('/lms/student/library/read/{id}', [LibraryController::class, 'readBook'])
        ->name('student.library.read');

    Route::post('/lms/student/library/submit', [LibraryController::class, 'submitTask'])
        ->name('student.library.submit');

    Route::get('/student/library/mapel/{mapel}', [LibraryController::class, 'mapelDetail'])
        ->where('mapel', '[0-9]+')
        ->name('student.library.mapel');

    Route::get('/get-bab/{mapel_id}', [LibraryController::class, 'getBab']);

    // OFFICES DASHBOARD
    Route::get('/lms/{role}/dashboard', [DashboardController::class, 'index'])->name('lms.office.dashboard.view');

    // =========================================================
    // ROUTES SYLLABUS-SERVICES
    // =========================================================
    Route::get('/syllabus/curriculum', [SyllabusController::class, 'curriculumView'])->name('kurikulum.view');
    Route::get('/syllabus/curriculum/{curriculumName}/{curriculumId}/fase', [SyllabusController::class, 'faseView'])->name('fase.view');
    Route::get('/syllabus/curriculum/{curriculumName}/{curriculumId}/{faseId}/kelas', [SyllabusController::class, 'kelasView'])->name('kelas.view');
    Route::get('/syllabus/curriculum/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/mapel', [SyllabusController::class, 'mapelView'])->name('mapel.view');
    Route::get('/syllabus/curriculum/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/{mapelId}/bab', [SyllabusController::class, 'babView'])->name('bab.view');
    Route::get('/syllabus/curriculum/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/{mapelId}/{babId}/sub-bab', [SyllabusController::class, 'subBabView'])->name('subBab.view');

    // CRUD Kurikulum
    Route::post('/syllabus/curriculum/store', [SyllabusController::class, 'curiculumStore'])->name('kurikulum.store');
    Route::post('/syllabus/curriculum/edit/{curriculumId}', [SyllabusController::class, 'curiculumEdit'])->name('kurikulum.edit');

    // CRUD Fase
    Route::post('/syllabus/{curriculumId}/fase/store', [SyllabusController::class, 'faseStore'])->name('fase.store');
    Route::post('/syllabus/curriculum/fase/edit/{curriculumId}/{faseId}', [SyllabusController::class, 'faseEdit'])->name('fase.edit');

    // CRUD Kelas
    Route::post('/syllabus/{curriculumId}/{faseId}/kelas/store', [SyllabusController::class, 'kelasStore'])->name('kelas.store');
    Route::post('/syllabus/curriculum/kelas/edit/{curriculumId}/{faseId}/{kelasId}', [SyllabusController::class, 'kelasEdit'])->name('kelas.edit');

    // CRUD Mapel
    Route::post('/syllabus/{curriculumId}/{faseId}/{kelasId}/mapel/store', [SyllabusController::class, 'mapelStore'])->name('mapel.store');
    Route::post('/syllabus/curriculum/mapel/edit/{curriculumId}/{faseId}/{kelasId}/{mapelId}', [SyllabusController::class, 'mapelEdit'])->name('mapel.edit');
    Route::put('/syllabus/curriculum/mapel/activate/{mapelId}', [SyllabusController::class, 'mapelActivate'])->name('mapel.activate');

    // CRUD Bab
    Route::post('/syllabus/{curriculumId}/{faseId}/{kelasId}/{mapelId}/bab/store', [SyllabusController::class, 'babStore'])->name('bab.store');
    Route::post('/syllabus/curriculum/bab/edit/{curriculumId}/{faseId}/{kelasId}/{mapelId}/{babId}', [SyllabusController::class, 'babEdit'])->name('bab.edit');
    Route::put('/syllabus/curriculum/bab/activate/{babId}', [SyllabusController::class, 'babActivate'])->name('bab.activate');

    // CRUD Sub Bab
    Route::post('/syllabus/{curriculumId}/{faseId}/{kelasId}/{mapelId}/{babId}/sub-bab/store', [SyllabusController::class, 'subBabStore'])->name('subBab.store');
    Route::post('/syllabus/curriculum/sub-bab/edit/{curriculumId}/{faseId}/{kelasId}/{mapelId}/{babId}/{subBabId}', [SyllabusController::class, 'subBabEdit'])->name('subBab.edit');
    Route::put('/syllabus/curriculum/sub-bab/activate/{subBabId}', [SyllabusController::class, 'subBabActivate'])->name('subBab.activate');

    // PAGINATE SYLLABUS-SERVICES
    Route::get('/paginate-syllabus-service-kurikulum', [SyllabusController::class, 'paginateSyllabusCuriculum'])->name('syllabus.kurikulum');
    Route::get('/paginate-syllabus-service-fase/{curriculumName}/{curriculumId}', [SyllabusController::class, 'paginateSyllabusFase'])->name('syllabus.fase');
    Route::get('/paginate-syllabus-service-kelas/{curriculumName}/{curriculumId}/{faseId}', [SyllabusController::class, 'paginateSyllabusKelas'])->name('syllabus.kelas');
    Route::get('/paginate-syllabus-service-mapel/{curriculumName}/{curriculumId}/{faseId}/{kelasId}', [SyllabusController::class, 'paginateSyllabusMapel'])->name('syllabus.mapel');
    Route::get('/paginate-syllabus-service-bab/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/{mapelId}', [SyllabusController::class, 'paginateSyllabusBab'])->name('syllabus.bab');
    Route::get('/paginate-syllabus-service-sub-bab/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/{mapelId}/{babId}', [SyllabusController::class, 'paginateSyllabusSubBab'])->name('syllabus.subBab');

    // BULKUPLOAD SYLLABUS
    Route::post('/syllabus/bulkupload/syllabus', [SyllabusController::class, 'bulkUploadSyllabus'])->name('syllabus.bulkupload');

    // =========================================================
    // ROUTES SCHOOL CURRICULUM MANAGEMENT HIERARCHY
    // views
    Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/kurikulum', [SchoolSyllabusController::class, 'curriculumView'])->name('schoolCurriculumManagement.view');
    Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/{curriculumName}/{curriculumId}/fase', [SchoolSyllabusController::class, 'faseView'])->name('schoolFaseManagement.view');
    Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/{curriculumName}/{curriculumId}/{faseId}/kelas', [SchoolSyllabusController::class, 'kelasView'])->name('schoolKelasManagement.view');
    Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/mapel', [SchoolSyllabusController::class, 'mapelView'])->name('schoolMapelManagement.view');
    Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/{mapelId}/bab', [SchoolSyllabusController::class, 'babView'])->name('schoolBabManagement.view');
    Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/{mapelId}/{babId}/sub-bab', [SchoolSyllabusController::class, 'subBabView'])->name('schoolSubBabManagement.view');

    // crud mapel
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/mapel/store', [SchoolSyllabusController::class, 'mapelStore'])->name('schoolMapelManagement.store');
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/mapel/{mapelId}/edit', [SchoolSyllabusController::class, 'mapelEdit'])->name('schoolMapelManagement.edit');
    Route::put('/lms/school-subscription/{schoolName}/{schoolId}/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/mapel/{mapelId}/activate', [SchoolSyllabusController::class, 'mapelActivate'])->name('schoolMapelManagement.activate');

    // crud bab
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/{mapelId}/bab/store', [SchoolSyllabusController::class, 'babStore'])->name('schoolBabManagement.store');
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/{mapelId}/bab/{babId}/edit', [SchoolSyllabusController::class, 'babEdit'])->name('schoolBabManagement.edit');
    Route::put('/lms/school-subscription/{schoolName}/{schoolId}/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/{mapelId}/bab/{babId}/activate', [SchoolSyllabusController::class, 'schoolBabManagement.activate']);

    // crud sub bab
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/{mapelId}/{babId}/sub-bab/store', [SchoolSyllabusController::class, 'subBabStore'])->name('schoolSubBabManagement.store');
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/{mapelId}/{babId}/sub-bab/{subBabId}/edit', [SchoolSyllabusController::class, 'subBabEdit'])->name('schoolSubBabManagement.edit');
    Route::put('/lms/school-subscription/{schoolName}/{schoolId}/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/{mapelId}/{babId}/sub-bab/{subBabId}/activate', [SchoolSyllabusController::class, 'subBabActivate'])->name('schoolSubBabManagement.activate');

    // paginate
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/kurikulum/paginate', [SchoolSyllabusController::class, 'paginateCurriculum'])->name('schoolCurriculumManagement.paginate');
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/{curriculumName}/{curriculumId}/fase/paginate', [SchoolSyllabusController::class, 'paginateFase'])->name('schoolFaseManagement.paginate');
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/{curriculumName}/{curriculumId}/{faseId}/kelas/paginate', [SchoolSyllabusController::class, 'paginateKelas'])->name('schoolKelasManagement.paginate');
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/mapel/paginate', [SchoolSyllabusController::class, 'paginateMapel'])->name('schoolMapelManagement.paginate');
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/{mapelId}/bab/paginate', [SchoolSyllabusController::class, 'paginateBab'])->name('schoolBabManagement.paginate');
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/{mapelId}/{babId}/sub-bab/paginate', [SchoolSyllabusController::class, 'paginateSubBab'])->name('schoolSubBabManagement.paginate');

    // bulkupload
    Route::post('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/{curriculumName}/{curriculumId}/{kelasId}/{mapelId}/{faseId}/bab/bulkUpload', [SchoolSyllabusController::class, 'bulkUploadSchoolSyllabusBab'])->name('schoolBabManagement.bulkupload');
    Route::post('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/{curriculumName}/{curriculumId}/{kelasId}/{mapelId}/{babId}/{faseId}/sub-bab/bulkUpload', [SchoolSyllabusController::class, 'bulkUploadSchoolSyllabusSubBab'])->name('schoolSubBabManagement.bulkupload');

    // =========================================================
    // ROUTES LMS FEATURE (administrator)
    // =========================================================
    Route::get('/lms/{role}/school-subscription', [LmsController::class, 'lmsSchoolSubscriptionView'])->name('lms.schoolSubscription.view');

    // routes academic management
    Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management', [AcademicDashboardController::class, 'lmsAcademicManagementView'])->name('lms.academicManagement.view');
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/academic-management/paginate', [AcademicDashboardController::class, 'paginateLmsAcademicManagement'])->name('lms.academicManagement.paginate');

    // route management role account
    Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/management-role-account', [RoleController::class, 'lmsManagementRolesView'])->name('lms.managementRoles.view');

    // route management account
    Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/management-role-account/{managedRole}/management-accounts', [AccountController::class, 'lmsManagementAccountView'])->name('lms.managementAccount.view');

    // route parent children list management
    Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/management-role-account/{managedRole}/management-accounts/{parentId}/parent-children-list', [AccountController::class, 'lmsParentChildrenListView'])->name('lms.parentChildrenList.view');

    // route management majors
    Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/management-role-account/{managedRole}/management-majors', [MajorController::class, 'lmsManagementMajorsView'])->name('lms.managementMajors.view');

    // routes views class by major and no major
    Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/management-role-account/{managedRole}/management-majors/{majorId}/management-class', [ClassController::class, 'lmsManagementClassView'])->name('lms.managementClass.view.major');
    Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/management-role-account/{managedRole}/management-class', [ClassController::class, 'lmsManagementClassView'])->name('lms.managementClass.view.noMajor');

    // routes views students in class by major and no major
    Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/management-role-account/{managedRole}/management-class/{classId}/management-majors/{majorId}/management-students', [StudentSchoolClassController::class, 'lmsManagementStudentsView'])->name('lms.managementStudents.view.major');
    Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/management-role-account/{managedRole}/management-class/{classId}/management-students', [StudentSchoolClassController::class, 'lmsManagementStudentsView'])->name('lms.managementStudents.view.noMajor');

    // CRUD
    // routes crud majors
    Route::post('/lms/{role}/school-subscription/{schoolName}/{schoolId}/management-role-account/{managedRole}/management-majors/create', [MajorController::class, 'lmsManagementCreateMajor'])->name('lms.managementCreateMajor.store');
    Route::post('/lms/{role}/school-subscription/{schoolName}/{schoolId}/management-role-account/{managedRole}/management-majors/{majorId}/edit', [MajorController::class, 'lmsManagementEditMajor'])->name('lms.managementEditMajor.store');

    // routes create management class by major and no major
    Route::post('/lms/{role}/school-subscription/{schoolName}/{schoolId}/management-role-account/{managedRole}/management-majors/{majorId}/management-class/create', [ClassController::class, 'lmsManagementCreateClass'])->name('lms.managementCreateClass.store.major');
    Route::post('/lms/{role}/school-subscription/{schoolName}/{schoolId}/management-role-account/{managedRole}/management-class/create', [ClassController::class, 'lmsManagementCreateClass'])->name('lms.managementCreateClass.store.noMajor');

    // routes edit management class by major and no major
    Route::post('/lms/{role}/school-subscription/{schoolName}/{schoolId}/management-role-account/{managedRole}/management-class/{classId}/management-majors/{majorId}/edit', [ClassController::class, 'lmsManagementEditClass'])->name('lms.managementClassWithMajor.edit');
    Route::post('/lms/{role}/school-subscription/{schoolName}/{schoolId}/management-role-account/{managedRole}/management-class/{classId}/edit', [ClassController::class, 'lmsManagementEditClass'])->name('lms.managementClassNoMajor.edit');

    // activate
    Route::put('/lms/school-subscription/{subscriptionId}/activate', [LmsController::class, 'lmsSchoolSubscriptionActivate'])->name('lms.schoolSubscription.activate');
    Route::put('/lms/school-subscription/{schoolId}/management-account/{id}/activate-account', [AccountController::class, 'lmsActivateAccount'])->name('lms.account.activate');
    Route::put('/lms/school-subscription/management-class/{id}/activate-major', [MajorController::class, 'lmsActivateMajor'])->name('lms.major.activate');
    Route::put('/lms/school-subscription/management-class/{id}/activate-class', [ClassController::class, 'lmsActivateClass'])->name('lms.class.activate');
    Route::put('/lms/school-subscription/management-class/{id}/activate-student-in-class', [StudentSchoolClassController::class, 'lmsActivateStudentInClass'])->name('lms.studentInClass.activate');

    // routes promote class, repeat class, move class, move major
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/management-role-account/{role}/management-class/{classId}/promote-class', [StudentSchoolClassController::class, 'lmsManagementPromoteClass'])->name('lms.managementPromoteClass.create');
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/management-role-account/{role}/management-class/{classId}/repeat-class', [StudentSchoolClassController::class, 'lmsManagementRepeatClass'])->name('lms.managementRepeatClass.create');
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/management-role-account/{role}/management-class/{classId}/move-class', [StudentSchoolClassController::class, 'lmsManagementMoveClass'])->name('lms.managementMoveClass.create');
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/management-role-account/{role}/management-class/{classId}/move-major', [StudentSchoolClassController::class, 'lmsManagementMoveMajor'])->name('lms.managementMoveMajor.create');

    // get promotion next class / repeat class / move class / move major options
    // promote-to-next-class routes by major and no major
    Route::get('/lms/school/{schoolId}/{majorId}/promotion-to-next-class-options', [StudentSchoolClassController::class, 'promotionClassOptions']);
    Route::get('/lms/school/{schoolId}/promotion-to-next-class-options', [StudentSchoolClassController::class, 'promotionClassOptions']);

    // promote-to-repeat-class routes by major and no major
    Route::get('/lms/school/{schoolId}/{majorId}/repeat-class-options', [StudentSchoolClassController::class, 'repeatClassOptions']);
    Route::get('/lms/school/{schoolId}/repeat-class-options', [StudentSchoolClassController::class, 'repeatClassOptions']);

    // promote-to-move-class routes by major and no major
    Route::get('/lms/school/{schoolId}/{majorId}/move-class-options', [StudentSchoolClassController::class, 'moveClassOptions']);
    Route::get('/lms/school/{schoolId}/move-class-options', [StudentSchoolClassController::class, 'moveClassOptions']);

    // move major routes by major and no major
    Route::get('/lms/school/{schoolId}/{majorId}/move-major-options', [StudentSchoolClassController::class, 'moveMajorOptions']);
    Route::get('/lms/school/{schoolId}/move-major-options', [StudentSchoolClassController::class, 'moveMajorOptions']);

    // paginate
    Route::get('/lms/school-subscription/paginate', [LmsController::class, 'paginateLmsSchoolSubscription'])->name('lms.schoolSubscription.paginate');
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/role-account/paginate', [RoleController::class, 'paginateLmsSchoolSubscriptionRoleAccount'])->name('lms.SchoolSubscriptionRoleAccount.paginate');
    Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/management-role-account/{managedRole}/management-accounts/paginate', [AccountController::class, 'paginateLmsSchoolAccount'])->name('lms.SchoolSubscriptionAccount.paginate');
    Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/management-role-account/{managedRole}/management-accounts/{parentId}/parent-children-list/paginate', [AccountController::class, 'paginateLmsParentChildrenList'])->name('lms.SchoolSubscriptionParentChildrenList.paginate');
    Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/role-account/{managedRole}/management-majors/paginate', [MajorController::class, 'paginateLmsSchoolSubscriptionMajors'])->name('lms.SchoolSubscriptionMajors.paginate');

    // paginate class by major and no major
    Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/role-account/{managedRole}/management-majors/{majorId}/management-class/paginate', [ClassController::class, 'paginateLmsSchoolSubscriptionClass'])->name('lms.SchoolSubscriptionClass.paginate.major');
    Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/role-account/{managedRole}/management-class/paginate', [ClassController::class, 'paginateLmsSchoolSubscriptionClass'])->name('lms.SchoolSubscriptionClass.paginate.noMajor');

    // paginate users by major and no major
    Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/role-account/{managedRole}/management-class/{classId}/management-majors/{majorId}/management-students/paginate', [StudentSchoolClassController::class, 'paginateLmsSchoolSubscriptionUsers'])->name('lms.SchoolSubscriptionUsers.paginate.major');
    Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/role-account/{managedRole}/management-class/{classId}/management-students/paginate', [StudentSchoolClassController::class, 'paginateLmsSchoolSubscriptionUsers'])->name('lms.SchoolSubscriptionUsers.paginate.noMajor');

    // =========================================================
    // ROUTES QUESTION BANK MANAGEMENT
    // view
    // question bank management no school partner & school partner
    Route::get('/lms/{role}/question-bank-management', [QuestionBankController::class, 'lmsQuestionBankManagementView'])->name('lms.questionBankManagement.view.noSchoolPartner');
    Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/question-bank-management', [QuestionBankController::class, 'lmsQuestionBankManagementView'])->name('lms.questionBankManagement.view.schoolPartner');

    // review question bank no school partner & school partner
    Route::get('/lms/{role}/question-bank-management/source/{source}/review/question-type/{questionType}/question-category/{questionCategory}/{subBabId?}', [QuestionBankController::class, 'lmsDefaultQuestionBankManagementDetailView'])->name('lms.questionBankManagementDetail.view.noSchoolPartner');
    Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/question-bank-management/source/{source}/review/question-type/{questionType}/question-category/{questionCategory}/{subBabId?}', [QuestionBankController::class, 'lmsSchoolQuestionBankManagementDetailView'])->name('lms.questionBankManagementDetail.view.schoolPartner');

    // edit question bank no school partner & school partner
    Route::get('/lms/{role}/question-bank-management/source/{source}/review/question-type/{questionType}/question-category/{questionCategory}/{questionId}/edit/{subBabId?}', [QuestionBankController::class, 'lmsDefaultQuestionBankManagementEditView'])->name('lms.questionBankManagementEdit.view.noSchoolPartner');
    Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/question-bank-management/source/{source}/review/question-type/{questionType}/question-category/{questionCategory}/{questionId}/edit/{subBabId?}', [QuestionBankController::class, 'lmsSchoolQuestionBankManagementEditView'])->name('lms.questionBankManagementEdit.view.schoolPartner');

    // form question bank edit no school partner & school partner
    Route::get('/lms/question-bank-management/bank-soal/form/source/{source}/review/question-type/{questionType}/{questionId}/edit/{subBabId?}', [QuestionBankController::class, 'formEditQuestion'])->name('lms.bankSoal.form.edit.question.noSchoolPartner');
    Route::get('/lms/school-subscription/question-bank-management/bank-soal/form/source/{source}/review/question-type/{questionType}/{questionId}/{schoolName}/{schoolId}/edit/{subBabId?}', [QuestionBankController::class, 'formEditQuestion'])->name('lms.bankSoal.form.edit.question.schoolPartner');

    // crud bank soal
    // upload bank soal no school partner & school partner
    Route::post('/lms/question-bank-management/store', [QuestionBankController::class, 'lmsQuestionBankManagementStore'])->name('lms.questionBankManagement.store.noSchoolPartner');
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/question-bank-management/store', [QuestionBankController::class, 'lmsQuestionBankManagementStore'])->name('lms.questionBankManagement.store.schoolPartner');

    // edit & delete image bank soal with ckeditor
    Route::post('/lms/bank-soal/edit-image', [QuestionBankController::class, 'editImageBankSoal'])->name('lms.editImage');
    Route::post('/lms/bank-soal/delete-image/endpoint', [QuestionBankController::class, 'deleteImageBankSoal'])->name('lms.deleteImage');

    // activate question bank no school partner with sub bab & without sub bab
    Route::put('/lms/question-bank-management/source/{source}/question-type/{questionType}/question-category/{questionCategory}/{subBabId}/activate', [QuestionBankController::class, 'lmsActivateQuestionBank'])->name('lms.questionBank.activate.global.withSubBab');
    Route::put('/lms/question-bank-management/source/{source}/question-type/{questionType}/question-category/{questionCategory}/activate', [QuestionBankController::class, 'lmsActivateQuestionBankNoSubBab'])->name('lms.questionBank.activate.global.noSubBab');

    // activate question bank school partner with sub bab & without sub bab
    Route::put('/lms/school-subscription/question-bank-management/source/{source}/question-type/{questionType}/question-category/{questionCategory}/{subBabId}/{schoolName}/{schoolId}/activate', [QuestionBankController::class, 'lmsActivateQuestionBank'])->name('lms.questionBank.activate.school.withSubBab');
    Route::put('/lms/school-subscription/question-bank-management/source/{source}/question-type/{questionType}/question-category/{questionCategory}/{schoolName}/{schoolId}/activate',[QuestionBankController::class, 'lmsActivateQuestionBankNoSubBab'])->name('lms.questionBank.activate.school.noSubBab');

    // edit bank soal no school partner & school partner submit form
    Route::post('/lms/question-bank-management/{questionId}/edit', [QuestionBankController::class, 'lmsQuestionBankManagementEdit'])->name('lms.questionBankManagement.edit');

    // paginate
    // question bank management no school partner & school partner
    Route::get('/lms/question-bank-management/paginate', [QuestionBankController::class, 'paginateLmsQuestionBankManagement'])->name('lms.questionBankManagement.paginate.noSchoolPartner');
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/question-bank-management/paginate', [QuestionBankController::class, 'paginateLmsQuestionBankManagement'])->name('lms.questionBankManagement.paginate.schoolPartner');

    // paginate review question bank no school partner & school partner
    // no school no sub bab
    Route::get('/lms/question-bank-management/source/{source}/review/question-type/{questionType}/question-category/{questionCategory}/paginate/without-subbab', [QuestionBankController::class, 'paginateReviewQuestionBank']);

    // no school with sub bab
    Route::get('/lms/question-bank-management/source/{source}/review/question-type/{questionType}/question-category/{questionCategory}/paginate/{subBabId}', [QuestionBankController::class, 'paginateReviewQuestionBank']);

    // school no sub bab
    Route::get('/lms/question-bank-management/source/{source}/review/question-type/{questionType}/question-category/{questionCategory}/school-subscription/{schoolName}/{schoolId}/paginate/without-subbab', [QuestionBankController::class, 'paginateReviewQuestionBankSchool']);

    // school with sub bab
    Route::get('/lms/question-bank-management/source/{source}/review/question-type/{questionType}/question-category/{questionCategory}/school-subscription/{schoolName}/{schoolId}/paginate/{subBabId}', [QuestionBankController::class, 'paginateReviewQuestionBankSchool']);

    // =========================================================
    // ROUTES CONTENT MANAGEMENT
    // view content management no school partner & school partner
    Route::get('/lms/{role}/content-management', [ContentBankController::class, 'lmsContentManagementView'])->name('lms.contentManagement.view.noSchoolPartner');
    Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/content-management', [ContentBankController::class, 'lmsContentManagementView'])->name('lms.contentManagement.view.schoolPartner');

    // view content management edit no school partner & school partner
    Route::get('/lms/{role}/content-management/{contentId}/edit', [ContentBankController::class, 'lmsDefaultContentManagementEditView'])->name('lms.contentManagement.edit.view.noSchoolPartner');
    Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/content-management/{contentId}/edit', [ContentBankController::class, 'lmsSchoolContentManagementEditView'])->name('lms.contentManagement.edit.view.schoolPartner');

    // view content management review no school partner & school partner
    Route::get('/lms/{role}/content-management/{contentId}/review', [ContentBankController::class, 'lmsReviewContentDefault'])->name('lms.contentManagement.review.noSchoolPartner');
    Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/content-management/{contentId}/review', [ContentBankController::class, 'lmsReviewContentSchool'])->name('lms.contentManagement.review.schoolPartner');

    // form edit content
    Route::get('/lms/content-management/{contentId}/form/edit', [ContentBankController::class, 'lmsContentManagementFormEdit'])->name('lms.contentManagementForm.edit');

    // crud
    // create content management no school partner & school partner
    Route::post('/lms/content-management/store', [ContentBankController::class, 'lmsContentManagementStore'])->name('lms.contentManagement.store.noSchoolPartner');
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/content-management/store', [ContentBankController::class, 'lmsContentManagementStore'])->name('lms.contentManagement.store.schoolPartner');

    Route::post('/lms/content-management/{contentId}/edit-action', [ContentBankController::class, 'lmsContentManagementEdit'])->name('lms.contentManagement.edit');

    // routes activate content management no school partner & school partner
    Route::put('/lms/content-management/{contentId}/activate', [ContentBankController::class, 'lmsContentManagementActivate'])->name('lms.contentManagement.activate.noSchoolPartner');
    Route::put('/lms/school-subscription/content-management/{contentId}/{schoolName}/{schoolId}/activate', [ContentBankController::class, 'lmsContentManagementActivate'])->name('lms.contentManagement.activate.schoolPartner');

    // paginate content management no school partner & school partner
    Route::get('/lms/content-management/paginate', [ContentBankController::class, 'paginateLmsContentManagement'])->name('lms.contentManagement.paginate.noSchoolPartner');
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/content-management/paginate', [ContentBankController::class, 'paginateLmsContentManagement'])->name('lms.contentManagement.paginate.schoolPartner');

    // =========================================================
    // ROUTES ASSESSMENT TYPE MANAGEMENT
    // views
    Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/assessment-type-management', [AssessmentTypeController::class, 'lmsAssessmentTypeManagementView'])->name('lms.assessmentTypeManagement.view');

    // crud
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/assessment-type-management/store', [AssessmentTypeController::class, 'lmsAssessmentTypeManagementStore'])->name('lms.assessmentTypeManagement.store');
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/assessment-type-management/{assessmentTypeId}/edit', [AssessmentTypeController::class, 'lmsAssessmentTypeManagementEdit'])->name('lms.assessmentTypeManagement.edit');
    Route::put('/lms/school-subscription/{schoolName}/{schoolId}/assessment-type-management/{assessmentTypeId}/activate', [AssessmentTypeController::class, 'lmsAssessmentTypeManagementActivate'])->name('lms.assessmentTypeManagement.activate');

    // paginate
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/assessment-type-management/paginate', [AssessmentTypeController::class, 'paginateLmsAssessmentTypeManagement'])->name('lms.assessmentTypeManagement.paginate');

    // =========================================================
    // ROUTES TEACHER SUBJECT MANAGEMENT
    // views
    Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/subject-teacher-management', [TeacherSubjectController::class, 'lmsTeacherSubjectManagement'])->name('lmsTeacherSubjectManagement.view');

    // crud
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/subject-teacher-management/store', [TeacherSubjectController::class, 'lmsTeacherSubjectManagementStore'])->name('lmsTeacherSubjectManagement.store');
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/subject-teacher-management/{teacherSubjectId}/edit', [TeacherSubjectController::class, 'lmsTeacherSubjectManagementEdit'])->name('lmsTeacherSubjectManagement.edit');
    Route::put('/lms/school-subscription/{schoolName}/{schoolId}/subject-teacher-management/{teacherSubjectId}/activate', [TeacherSubjectController::class, 'lmsTeacherSubjectManagementActivate'])->name('lmsTeacherSubjectManagement.activate');

    // paginate
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/subject-teacher-management/paginate', [TeacherSubjectController::class, 'paginateLmsTeacherSubjectManagement'])->name('lmsTeacherSubjectManagement.paginate');

    // =========================================================
    // ROUTES ASSESSMENT WEIGHT MANAGEMENT
    // views
    Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/assessment-weight-management', [AssessmentWeightController::class, 'assessmentWeight'])->name('lms.assessmentWeight.view');

    // crud
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/assessment-weight-management/store', [AssessmentWeightController::class, 'assessmentWeightStore'])->name('lms.assessmentWeight.store');
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/assessment-weight-management/{assessmentWeightId}/edit', [AssessmentWeightController::class, 'assessmentWeightEdit'])->name('lms.assessmentWeight.edit');

    // paginate
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/assessment-weight-management/paginate', [AssessmentWeightController::class, 'paginateAssessmentWeight'])->name('lms.assessmentWeight.paginate');

    // =========================================================
    // ROUTES SUBJECT PASSING GRADE CRITERIA MANAGEMENT
    // views
    Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/subject-passing-grade-criteria-management', [SubjectPassingGradeCriteriaController::class, 'subjectPassingGradeCriteria'])->name('lms.subjectPassingGradeCriteria.view');

    // crud
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/subject-passing-grade-criteria-management/store', [SubjectPassingGradeCriteriaController::class, 'subjectPassingGradeCriteriaStore'])->name('lms.subjectPassingGradeCriteria.store');
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/subject-passing-grade-criteria-management/{subjectPassingGradeCriteriaId}/edit', [SubjectPassingGradeCriteriaController::class, 'subjectPassingGradeCriteriaEdit'])->name('lms.subjectPassingGradeCriteria.edit');
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/subject-passing-grade-criteria-management/bulkupload-store', [SubjectPassingGradeCriteriaController::class, 'bulkUploadSubjectPassingGradeCriteria'])->name('lms.subjectPassingGradeCriteria.bulkUpload.store');

    // paginate
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/subject-passing-grade-criteria-management/paginate', [SubjectPassingGradeCriteriaController::class, 'paginateSubjectPassingGradeCriteria'])->name('lms.subjectPassingGradeCriteria.paginate');

    // ROUTES OFFICE MANAGEMENT
    Route::get('/lms/{role}/office-management/manage-user', [OfficeManagementController::class, 'manageUserView'])->name('lms.officeManagement.manage-user.view');

    // crud
    Route::post('/lms/{role}/office-management/manage-user/store', [OfficeManagementController::class, 'manageUserStore'])->name('lms.officeManagement.manage-user.store');
    Route::post('/lms/{role}/office-management/manage-user/edit/{userAccountId}', [OfficeManagementController::class, 'manageUserEdit'])->name('lms.officeManagement.manage-user.edit');
    Route::put('/lms/{role}/office-management/manage-user/activate-account/{userAccountId}', [OfficeManagementController::class, 'manageUserActivate'])->name('lms.officeManagement.manage-user.activate');

    // paginate
    Route::get('/lms/{role}/office-management/manage-user/paginate', [OfficeManagementController::class, 'paginateManageUser'])->name('lms.officeManagement.manage-user.paginate');

    // =========================================================
    // ROUTES FINANCE

    // MANAGE CONTRACT (VIEWS)
    Route::get('/lms/{role}/manage-contract', [FinanceContractController::class, 'index'])->name('lms.finance.manage-contract.view');
    Route::get('/lms/{role}/manage-contract/schools/{schoolId}', [FinanceContractController::class, 'manageContractDetail'])->name('lms.finance.manage-contract-detail.view');
    Route::get('/lms/{role}/manage-contract/schools/{schoolId}/contract/{contractId}/payment-detail', [FinanceContractController::class, 'contractPaymentDetail'])->name('lms.finance.contract.payment.detail');
    Route::get('/lms/{role}/manage-contract/schools/{schoolId}/contract/{contractId}/payment-detail/student-list/{termId}', [FinanceContractController::class, 'studentList'])->name('lms.finance.contract.student-list.view');

    // crud manage contract detail
    Route::put('/lms/{role}/manage-contract/schools/{schoolId}/contract/{contractId}/activate', [FinanceContractController::class, 'contractDetailActivate'])->name('lms.finance.manage-contract-detail.activate');

    // crud contract payment detail
    Route::put('/lms/{role}/manage-contract/schools/{schoolId}/contract/{contractId}/payment-detail/term/{termId}/mark-status', [FinanceContractController::class, 'markContractTerm'])->name('lms.finance.contract.payment.mark-status');

    // crud manage contract term (student)
    Route::post('/lms/{role}/manage-contract/schools/{schoolId}/contract/{contractId}/payment-detail/student-list/{termId}/store', [FinanceContractController::class, 'bulkUploadContractStundents'])->name('lms.finance.contract.student-list.store');
    Route::put('/lms/{role}/manage-contract/schools/{schoolId}/contract/{contractId}/payment-detail/student-list/{termId}/student/{studentTermId}/activate', [FinanceContractController::class, 'studentListActivate'])->name('lms.finance.contract.student-list.activate');

    // paginate
    Route::get('/lms/{role}/manage/contract/paginate', [FinanceContractController::class, 'paginateManageContract'])->name('lms.finance.manage.contract.paginate');
    Route::get('/lms/{role}/manage/contract/schools/{schoolId}/paginate', [FinanceContractController::class, 'paginateManageContractDetail'])->name('lms.finance.manage.contract-detail.paginate');
    Route::get('/lms/{role}/manage-contract/schools/{schoolId}/contract/{contractId}/payment-detail/paginate', [FinanceContractController::class, 'paginateContractPaymentDetail'])->name('lms.finance.contract-payment-detail.paginate');
    Route::get('/lms/{role}/manage-contract/schools/{schoolId}/contract/{contractId}/payment-detail/student-list/{termId}/paginate', [FinanceContractController::class, 'paginateStudentList'])->name('lms.finance.contract.student-list.paginate');

    // MANAGE REVENUE (VIEWS)
    Route::get('/lms/{role}/manage/revenue', [FinanceRevenueController::class, 'index'])->name('lms.finance.manage.revenue.view');

    // data manage revenue
    Route::get('/lms/{role}/manage/revenue/load-kpi', [FinanceRevenueController::class, 'revenueManagementLoadKpi'])->name('lms.finance.manage.revenue.load-kpi');
    Route::get('/lms/{role}/manage/revenue/load-leaderboard', [FinanceRevenueController::class, 'revenueManagementLoadLeaderboard'])->name('lms.finance.manage.revenue.load-leaderboard');
    Route::get('/lms/{role}/manage/revenue/load-chart', [FinanceRevenueController::class, 'revenueManagementLoadChart'])->name('lms.finance.manage.revenue.load-chart');
    Route::get('/lms/{role}/manage/revenue/paginate/load-ranking', [FinanceRevenueController::class, 'revenueManagementLoadRanking'])->name('lms.finance.manage.revenue.load-ranking');
    
    // data dashboard
    Route::get('/lms/{role}/manage-contract/load-kpi', [FinanceDashboardController::class, 'loadKpiDashboard'])->name('lms.finance.manage-contract.load-kpi');
    Route::get('/lms/{role}/manage-contract/load-chart', [FinanceDashboardController::class, 'loadChartDashboard'])->name('lms.finance.manage-contract.load-chart');
    Route::get('/lms/{role}/manage-contract/load-top-revenue', [FinanceDashboardController::class, 'loadTopRevenueDashboard'])->name('lms.finance.manage-contract.load-chart');
    Route::get('/lms/{role}/manage-contract/load-contract-expiring', [FinanceDashboardController::class, 'loadContractExpiringDashboard'])->name('lms.finance.manage-contract.load-contract-expiring');

    // =========================================================
    // ROUTES STUDENT LMS (wildcard — harus PALING BAWAH di grup /lms/{role}/...)
    // =========================================================

    // check assessment status (spesifik, taruh sebelum wildcard)
    Route::get('/lms/check-assessment-status/{assessmentId}', [StudentAssessmentController::class, 'checkAssessmentStatus'])->name('lms.checkAssessmentStatus');

    // image essay
    Route::post('/lms/image-essay/store-image/endpoint', [StudentAssessmentExamController::class, 'storeImageEssay'])->name('assessment-test.storeImage');
    Route::post('/lms/image-essay/delete-image/endpoint', [StudentAssessmentExamController::class, 'deleteImageEssay'])->name('assessment-test.deleteImage');

    // subject progress
    Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/subject-progress', [StudentSubjectProgressController::class, 'index'])->name('lms.studentSubjectProgress.index');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/subject-progress/data', [StudentSubjectProgressController::class, 'data'])->name('lms.studentSubjectProgress.data');

    // learning routes
    Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum', [StudentLearningController::class, 'lmsStudentView'])->name('lms.student.view');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning', [StudentLearningController::class, 'studentLearning'])->name('lms.studentLearning.view');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/service/{serviceId}', [StudentLearningController::class, 'studentReviewMeeting'])->name('lms.studentReviewMeeting.view');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/service/{serviceId}/show-content/{meetingContentId}', [StudentLearningController::class, 'showStudentReviewContent'])->name('lms.studentReviewContent.show');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/service/{serviceId}/download-content/{meetingContentId}', [StudentLearningController::class, 'downloadStudentContent'])->name('lms.studentContent.download');

    // paginate learning
    Route::get('/lms/{role}/{schoolName}/{schoolId}/paginate', [StudentLearningController::class, 'paginateLmsStudent'])->name('lms.student.paginate');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/paginate', [StudentLearningController::class, 'paginateStudentLearning'])->name('lms.studentLearning.paginate');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/service/{serviceId}/semester/{semester}/paginate', [StudentLearningController::class, 'paginateStudentReviewMeeting'])->name('lms.studentReviewMeeting.paginate');

    // assessment
    Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}', [StudentAssessmentController::class, 'studentPreviewAssessment'])->name('lms.studentPreviewAssessment.view');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/mode/{mode}/{parentAssessmentId}', [StudentAssessmentController::class, 'studentPreviewAssessment'])->name('lms.studentPreviewAssessment.mode.view');

    // load assessment data by semester
    Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/semester/{semester}', [StudentAssessmentController::class, 'loadStudentPreviewAssessment'])->name('lms.loadStudentPreviewAssessment');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/semester/{semester}/mode/{mode}/{parentAssessmentId}', [StudentAssessmentController::class, 'loadStudentPreviewAssessment'])->name('lms.loadStudentPreviewAssessment.mode');
    Route::get('/lms/check-assessment-status/{assessmentId}', [StudentAssessmentController::class, 'checkAssessmentStatus'])->name('lms.checkAssessmentStatus');

    // assessment exam
    Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/semester/{semester}/test/{assessmentId}', [StudentAssessmentExamController::class, 'studentAssessmentExam'])->name('lms.studentAssessmentExan.view');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/semester/{semester}/form/{assessmentId}', [StudentAssessmentExamController::class, 'studentAssessmentExamForm'])->name('lms.studentAssessmentExan.form');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/semester/{semester}/form/{assessmentId}/start-timer', [StudentAssessmentExamController::class, 'startTimer'])->name('lms.startTimer.test');
    Route::post('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/semester/{semester}/form/{assessmentId}/report-tab-switch', [StudentAssessmentExamController::class, 'reportTabSwitch'])->name('lms.reportTabSwitch.cheating');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/semester/{semester}/form/{assessmentId}/attempt-status', [StudentAssessmentExamController::class, 'checkAttemptStatus'])->name('lms.checkAttemptStatus.cheating');
    Route::post('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/semester/{semester}/form/{assessmentId}/answer', [StudentAssessmentExamController::class, 'studentAssessmentExamAnswer'])->name('lms.studentAssessmentExan.answer');
    Route::post('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/semester/{semester}/form/{assessmentId}/project-submission', [StudentAssessmentExamController::class, 'studentProjectSubmission'])->name('lms.studentProjectSubmission.answer');
    Route::post('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/semester/{semester}/form/{assessmentId}/emd', [StudentAssessmentExamController::class, 'studentAssessmentExamEnd'])->name('lms.studentAssessmentExan.emd');

    // routes store and delete image essay
    Route::post('/lms/image-essay/store-image/endpoint', [StudentAssessmentExamController::class, 'storeImageEssay'])->name('assessment-test.storeImage');
    Route::post('/lms/image-essay/delete-image/endpoint', [StudentAssessmentExamController::class, 'deleteImageEssay'])->name('assessment-test.deleteImage');

    // results
    Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/semester/{semester}/assessment/{assessmentId}/result-test', [StudentAssessmentExamController::class, 'studentResultAssessment'])->name('lms.studentAssessment.result');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/semester/{semester}/assessment/{assessmentId}/project-result', [StudentAssessmentExamController::class, 'studentProjectResult'])->name('lms.studentProjectAssessment.result');
    Route::get('/lms/student/dashboard/cheating-history/data-paginate', [StudentDashboardController::class, 'getStudentAssessmentCheatingHistory'])->name('lms.studentAssessmentCheatingHistory.dashboard');
    Route::get('/lms/{schoolId}/teacher-schedule/get-data/{classId}', [\App\Http\Controllers\TeacherInformationController::class, 'getScheduleDataAjax']);
    
    // polling siswa
    Route::post('/lms/student/polling/vote', [StudentDashboardController::class, 'submitVote'])->name('lms.studentPolling.vote');

    // =========================================================
    // ROUTES TEACHER LMS
    // =========================================================

    // content management
    Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher/dashboard', [LmsController::class, 'lmsTeacherView'])->name('lms.teacher.view');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/beranda/cheating-history', [LmsController::class, 'getTeacherAssessmentCheatingHistory'])->name('lms.teacherAssessmentCheatingHistory.data');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-content-management', [TeacherContentController::class, 'teacherContentManagement'])->name('lms.teacherContentManagement.view');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-content-management/{contentId}/review', [TeacherContentController::class, 'teacherReviewContent'])->name('lms.teacherContentManagement.review.view');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-content-management/{contentId}/edit', [TeacherContentController::class, 'teacherEditContent'])->name('lms.teacherContentManagement.edit.view');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-content-management/paginate', [TeacherContentController::class, 'paginateTeacherContentManagement'])->name('lms.teacherContentManagement.paginate');

    // content for release
    Route::get('/lms/{role}/{schoolName}/{schoolId}/content-for-release', [TeacherContentReleaseController::class, 'teacherContentForRelease'])->name('lms.teacherContentForRelease.view');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/content-for-release/rombel-kelas/{schoolClassId}/subject/{mapelId}/semester/{semester}/service/{serviceId}/review-meetings', [TeacherContentReleaseController::class, 'teacherContentForReleaseReviewMeeting'])->name('lms.teacherContentForReleaseReviewMeeting.view');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/content-for-release/rombel-kelas/{schoolClassId}/subject/{mapelId}/semester/{semester}/service/{serviceId}/review-content/{meetingContentId}', [TeacherContentReleaseController::class, 'teacherContentForReleaseReviewContent'])->name('lms.teacherContentForReleaseReviewContent.view');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/content-for-release/form', [TeacherContentReleaseController::class, 'teacherFormContentForRelease'])->name('lms.teacherContentForRelease.form');
    Route::post('/lms/{role}/{schoolName}/{schoolId}/content-for-release/store', [TeacherContentReleaseController::class, 'teacherContentForReleaseStore'])->name('lms.teacherContentForRelease.store');
    Route::post('/lms/{role}/{schoolName}/{schoolId}/content-for-release/{meetingContentId}/edit', [TeacherContentReleaseController::class, 'teacherContentForReleaseEdit'])->name('lms.teacherContentForRelease.edit');
    Route::put('/lms/{role}/{schoolName}/{schoolId}/content-for-release/{meetingContentId}/activate', [TeacherContentReleaseController::class, 'teacherContentForReleaseActivate'])->name('lms.teacherContentForRelease.activate');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/content-for-release/paginate', [TeacherContentReleaseController::class, 'paginateTeacherContentForRelease'])->name('lms.teacherContentForRelease.paginate');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/content-for-release/rombel-kelas/{schoolClassId}/subject/{mapelId}/semester/{semester}/service/{serviceId}/review-meetings/paginate', [TeacherContentReleaseController::class, 'paginateTeacherContentForReleaseReviewMeeting'])->name('lms.teacherContentForReleaseReviewMeeting.paginate');

    // assessment management
    Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-assessment-management', [TeacherAssessmentController::class, 'teacherAssessmentManagement'])->name('lms.teacherAssessmentManagement.view');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-assessment-management/{mode}/{parentAssessmentId}', [TeacherAssessmentController::class, 'teacherAssessmentManagement'])->name('lms.teacherAssessmentManagement.mode.view');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-assessment-management/{assessmentId}/edit/view', [TeacherAssessmentController::class, 'teacherAssessmentManagementEdit'])->name('lms.teacherAssessmentManagementEdit.view');

    // form
    Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-assessment-management/form', [TeacherAssessmentController::class, 'teacherFormAssessmentManagement'])->name('lms.teacherFormAssessmentManagement.form');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-assessment-management/{assessmentId}/edit/form', [TeacherAssessmentController::class, 'teacherFormAssessmentManagementEdit'])->name('lms.teacherFormAssessmentManagement.edit');
    Route::post('/lms/{role}/{schoolName}/{schoolId}/teacher-assessment-management/validate-step-form/store', [TeacherAssessmentController::class, 'teacherFormAssessmentValidateStep'])->name('lms.teacherAssessmentManagementValidateStep.form');
    Route::post('/lms/{role}/{schoolName}/{schoolId}/teacher-assessment-management/store', [TeacherAssessmentController::class, 'teacherFormAssessmentManagementStore'])->name('lms.teacherAssessmentManagement.store');
    Route::post('/lms/{role}/{schoolName}/{schoolId}/teacher-assessment-management/{assessmentId}/edit', [TeacherAssessmentController::class, 'teacherAssessmentManagementEditSubmission'])->name('lms.teacherAssessmentManagement.edit');
    Route::put('/lms/{role}/{schoolName}/{schoolId}/teacher-assessment-management/{assessmentId}/activate', [TeacherAssessmentController::class, 'teacherFormAssessmentManagementActivate'])->name('lms.teacherAssessmentManagement.activate');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-assessment-management/paginate', [TeacherAssessmentController::class, 'paginateTeacherAssessmentManagement'])->name('lms.teacherAssessmentManagement.paginate');

    // question bank management
    Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-question-bank-management', [TeacherQuestionBankController::class, 'teacherQuestionBankManagement'])->name('lms.teacherQuestionBankManagement.view');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-question-bank-management/source/{source}/review/question-type/{questionType}/question-category/{questionCategory}/{subBabId?}', [TeacherQuestionBankController::class, 'teacherQuestionBankManagementDetail'])->name('lms.teacherQuestionBankManagement.detail.view');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-question-bank-management/source/{source}/review/question-type/{questionType}/question-category/{questionCategory}/{questionId}/edit/{subBabId?}', [TeacherQuestionBankController::class, 'teacherQuestionBankManagementEdit'])->name('lms.teacherQuestionBankManagement.edit.view');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-question-bank-management/paginate', [TeacherQuestionBankController::class, 'paginateTeacherQuestionBankManagement'])->name('lms.teacherQuestionBankManagement.paginate');

    // question bank for release
    Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-question-bank-for-release', [TeacherQuestionBankReleaseController::class, 'teacherQuestionBankForRelease'])->name('lms.teacherQuestionBankForRelease.view');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-question-bank-for-release/review/{assessmentQuestionId}', [TeacherQuestionBankReleaseController::class, 'teacherReviewQuestionBankForRelease'])->name('lms.teacherReviewQuestionBankForRelease.view');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-question-bank-for-release/form', [TeacherQuestionBankReleaseController::class, 'teacherFormQuestionBankForRelease'])->name('lms.teacherQuestionBankForRelease.form');
    Route::post('/lms/{role}/{schoolName}/{schoolId}/teacher-question-bank-for-release/store', [TeacherQuestionBankReleaseController::class, 'teacherQuestionBankForReleaseStore'])->name('lms.teacherQuestionBankForRelease.store');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-question-bank-for-release/paginate', [TeacherQuestionBankReleaseController::class, 'paginateTeacherQuestionBankForRelease'])->name('lms.teacherQuestionBankForRelease.paginate');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-question-bank-for-release/review/{assessmentQuestionId}/paginate', [TeacherQuestionBankReleaseController::class, 'paginateTeacherReviewQuestionBankForRelease'])->name('lms.teacherReviewQuestionBankForRelease.paginate');

    // teacher assessment grading
    Route::get('/lms/{role}/{schoolName}/{schoolId}/assessment-grading', [TeacherAssessmentGradingController::class, 'assessmentGradingManagement'])->name('lms.assessmentGradingManagement.view');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/assessment-grading/{assessmentId}/mode/{mode}/student-list', [TeacherAssessmentGradingController::class, 'assessmentGradingStudentList'])->name('lms.assessmentGradingStudentList.view');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/assessment-grading/{assessmentId}/mode/{mode}/student-list/{studentId}/scoring', [TeacherAssessmentGradingController::class, 'assessmentGradingStudentAnswer'])->name('lms.assessmentGradingStudentAnswer.view');

    // crud
    Route::post('/lms/{role}/{schoolName}/{schoolId}/assessment-grading/{assessmentId}/student-list/{studentId}/scoring/submission/{schoolAssessmentQuestionId}', [TeacherAssessmentGradingController::class, 'submitAssessmentStudentScore'])->name('lms.assessmentGradingStudentAnswer.submission');
    Route::post('/lms/{role}/{schoolName}/{schoolId}/assessment-grading/{assessmentId}/student-list/{studentId}/scoring/submission/{submissionId}/project', [TeacherAssessmentGradingController::class, 'submitAssessmentStudentProjectScore'])->name('lms.assessmentGradingStudentProject.submission');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/assessment-grading/paginate', [TeacherAssessmentGradingController::class, 'paginateAssessmentGrading'])->name('lms.assessmentGrading.paginate');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/assessment-grading/{assessmentId}/mode/{mode}/student-list/paginate', [TeacherAssessmentGradingController::class, 'paginateAssessmentGradingStudentList'])->name('lms.assessmentGradingStudentList.paginate');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/assessment-grading/{assessmentId}/mode/{mode}/student-list/{studentId}/scoring/paginate', [TeacherAssessmentGradingController::class, 'paginateAssessmentGradingStudentAnswer'])->name('lms.assessmentGradingStudentAnswer.paginate');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/assessment-grading/{assessmentId}/student-list/{studentId}/scoring/project', [TeacherAssessmentGradingController::class, 'paginateAssessmentGradingStudentProject'])->name('lms.assessmentGradingStudentProject');

    // TEACHER SUBJECT ATTENDANCE MANAGEMENT
    // views
    Route::get('/lms/{role}/{schoolName}/{schoolId}/subject-attendance/classes', [SubjectAttendanceController::class, 'teacherClassList'])->name('lms.teacherClassListSubjectAttendance.view');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/subject-attendance/classes/subject-teacher/{subjectTeacherId}/meeting-list', [SubjectAttendanceController::class, 'subjectAttendanceMeetingList'])->name('lms.teacherSubjectAttendanceMeetingList.view');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/subject-attendance/classes/subject-teacher/{subjectTeacherId}/meeting-list/{meetingNumber}/semester/{semester}/meeting-management', [SubjectAttendanceController::class, 'subjectAttendanceMeetingManagement'])->name('lms.teacherSubjectAttendanceMeetingManagement.view');

    // subject attendance data
    Route::get('/lms/{role}/{schoolName}/{schoolId}/subject-attendance/classes/subject-teacher/{subjectTeacherId}/data', [SubjectAttendanceController::class, 'getSubjectAttendanceData'])->name('lms.teacherSubjectAttendance.data');

    // crud
    // announcement
    Route::post('/lms/{role}/{schoolName}/{schoolId}/subject-attendance/classes/subject-teacher/{subjectTeacherId}/meeting-list/{meetingNumber}/semester/{semester}/meeting-management/announcement-store', [SubjectAttendanceController::class, 'announcementStore'])->name('lms.teacherSubjectAttendanceMeetingManagement.announcement.store');

    // submit attendance student
    Route::post('/lms/{role}/{schoolName}/{schoolId}/subject-attendance/classes/subject-teacher/{subjectTeacherId}/meeting-list/{meetingNumber}/semester/{semester}/student-attendance/store', [SubjectAttendanceController::class, 'storeStudentAttendance'])->name('lms.studentAttendance.store');

    // paginate
    Route::get('/lms/{role}/{schoolName}/{schoolId}/subject-attendance/classes/paginate', [SubjectAttendanceController::class, 'paginateTeacherClassList'])->name('lms.teacherClassListSubjectAttendance.paginate');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/subject-attendance/classes/subject-teacher/{subjectTeacherId}/meeting-list/semester/{semester}/paginate', [SubjectAttendanceController::class, 'paginateSubjectAttendanceMeetingList'])->name('lms.teacherSubjectAttendanceMeetingList.paginate');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/subject-attendance/classes/subject-teacher/{subjectTeacherId}/meeting-list/{meetingNumber}/semester/{semester}/meeting-management/announcement/paginate', [SubjectAttendanceController::class, 'paginateAnnouncementList'])->name('lms.teacherSubjectAttendanceMeetingManagement.announcement.paginate');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/subject-attendance/classes/subject-teacher/{subjectTeacherId}/meeting-list/{meetingNumber}/semester/{semester}/meeting-management/material/paginate', [SubjectAttendanceController::class, 'paginateMaterialList'])->name('lms.teacherSubjectAttendanceMeetingManagement.material.paginate');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/subject-attendance/classes/subject-teacher/{subjectTeacherId}/meeting-list/{meetingNumber}/semester/{semester}/meeting-management/assessment/paginate', [SubjectAttendanceController::class, 'paginateAssessmentList'])->name('lms.teacherSubjectAttendanceMeetingManagement.assessment.paginate');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/subject-attendance/classes/subject-teacher/{subjectTeacherId}/meeting-list/{meetingNumber}/semester/{semester}/student-attendance/paginate', [SubjectAttendanceController::class, 'paginateStudentAttendance'])->name('lms.studentAttendance.paginate');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/subject-attendance/classes/subject-teacher/{subjectTeacherId}/meeting-list/{meetingNumber}/semester/{semester}/chart', [SubjectAttendanceController::class, 'getAttendanceChart']);

    // TEACHER GRADEBOOK MANAGEMENT
    // views
    Route::get('/lms/{role}/{schoolName}/{schoolId}/gradebook/classes', [TeacherGradebookController::class, 'teacherClassList'])->name('lms.teacherClassList.view');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/gradebook/classes/subject-teacher/{subjectTeacherId}', [TeacherGradebookController::class, 'gradebookManagement'])->name('lms.teacherGradebook.view');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/gradebook/classes/subject-teacher/{subjectTeacherId}/assessment-type/{assessmentTypeId}/student/{studentId}/preview/semester/{semester}', [GradebookAssessmentController::class, 'teacherGradebookAssessmentPreview'])->name('lms.teacherGradebookAssessment.view');

    // paginate
    Route::get('/lms/{role}/{schoolName}/{schoolId}/gradebook/classes/paginate', [TeacherGradebookController::class, 'paginateTeacherClassList'])->name('lms.teacherClassList.paginate');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/gradebook/classes/subject-teacher/{subjectTeacherId}/paginate', [TeacherGradebookController::class, 'paginateGradebookManagement'])->name('lms.teacherGradebook.paginate');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/gradebook/classes/subject-teacher/{subjectTeacherId}/assessment-type/{assessmentTypeId}/student/{studentId}/preview/semester/{semester}/paginate', [GradebookAssessmentController::class, 'paginateTeacherGradebookAssessment'])->name('lms.teacherGradebookAssessment.paginate');

    // gradebook export
    Route::get('/lms/{role}/{schoolName}/{schoolId}/gradebook/classes/subject-teacher/{subjectTeacherId}/semester/{semester}/export', [TeacherGradebookController::class, 'exportGradebook']);

    // update nilai
    Route::post(
        '/lms/{role}/{schoolName}/{schoolId}/gradebook/classes/subject-teacher/{subjectTeacherId}/assessment-type/{assessmentTypeId}/student/{studentId}/semester/{semester}/bulk-update',
        [GradebookAssessmentController::class, 'bulkUpdateScore']
    );

    // TEACHER GRADE LEDGER MANAGEMENT
    // views
    Route::get('/lms/{role}/{schoolName}/{schoolId}/grade-ledger/classes', [TeacherGradeLedgerController::class, 'teacherClassList'])->name('lms.teacherClassListGradeLedger.view');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/grade-ledger/classes/subject-teacher/{subjectTeacherId}', [TeacherGradeLedgerController::class, 'teacherGradeLedger'])->name('lms.teacherGradeLedger.view');

    // paginate
    Route::get('/lms/{role}/{schoolName}/{schoolId}/grade-ledger/classes/paginate', [TeacherGradeLedgerController::class, 'paginateTeacherClassList'])->name('lms.teacherClassListGradeLedger.paginate');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/grade-ledger/classes/subject-teacher/{subjectTeacherId}/paginate', [TeacherGradeLedgerController::class, 'paginateTeacherGradeLedger'])->name('lms.teacherGradeLedger.paginate');

    // grade ledger export
    Route::get('/lms/{role}/{schoolName}/{schoolId}/grade-ledger/classes/subject-teacher/{subjectTeacherId}/semester/{semester}/export', [TeacherGradeLedgerController::class, 'exportGradeLedger']);

    // TEACHER ACADEMIC TRANSCRIPT MANAGEMENT
    // views
    Route::get('/lms/{role}/{schoolName}/{schoolId}/academic-transcript/classes', [TeacherAcademicTranscriptController::class, 'teacherClassList'])->name('lms.teacherClassListAcademicTranscript.view');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/academic-transcript/classes/subject-teacher/{subjectTeacherId}', [TeacherAcademicTranscriptController::class, 'teacherAcademicTranscript'])->name('lms.teacherAcademicTranscript.view');

    // paginate
    Route::get('/lms/{role}/{schoolName}/{schoolId}/academic-transcript/classes/paginate', [TeacherAcademicTranscriptController::class, 'paginateTeacherClassList'])->name('lms.teacherClassListAcademicTranscript.paginate');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/academic-transcript/classes/subject-teacher/{subjectTeacherId}/paginate', [TeacherAcademicTranscriptController::class, 'paginateTeacherAcademicTranscript'])->name('lms.teacherAcademicTranscript.paginate');

    // academic transcript export
    Route::get('/lms/{role}/{schoolName}/{schoolId}/academic-transcript/classes/subject-teacher/{subjectTeacherId}/export', [TeacherAcademicTranscriptController::class, 'exportAcademicTranscript']);

    // Polling Teacher
    Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-polling', [App\Http\Controllers\TeacherInformationController::class, 'teacherPollingView'])->name('lms.teacherPolling.view');
    Route::post('/lms/{role}/{schoolName}/{schoolId}/teacher-polling/save', [App\Http\Controllers\TeacherInformationController::class, 'savePollingData'])->name('lms.teacherPolling.save');
    Route::delete('/lms/{role}/{schoolName}/{schoolId}/teacher-polling/{id}', [App\Http\Controllers\TeacherInformationController::class, 'deletePoll'])->name('lms.teacherPolling.destroy');
    Route::post('/lms/{role}/{schoolName}/{schoolId}/teacher/polling/vote', [App\Http\Controllers\TeacherInformationController::class, 'submitVote'])->name('lms.teacherPolling.submitVote');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-polling/{id}/breakdown', [App\Http\Controllers\TeacherInformationController::class, 'getPollingBreakdown'])->name('lms.teacherPolling.breakdown');

    // Rute detail kelas
    Route::get('/lms/guru/api/get-students/{classId}', [\App\Http\Controllers\LmsController::class, 'getStudentsForAttendance'])
        ->name('lms.guru.get_students');
    Route::post('/lms/teacher/pengumuman/store', [App\Http\Controllers\LmsController::class, 'storePengumuman'])->name('lms.teacher.pengumuman.store');
    Route::post('/lms/student/announcement/mark-as-read', [App\Http\Controllers\LmsController::class, 'markAsRead'])->name('lms.student.announcement.read');
    Route::get('/lms/{role}/sekolah/{schoolName}/{schoolId}/kelas/{scheduleId}', [\App\Http\Controllers\LmsController::class, 'classDetailView'])
        ->name('lms.teacher.class.detail');
    Route::post('/lms/teacher/attendance/store', [App\Http\Controllers\LmsController::class, 'saveAttendance'])->name('lms.teacher.attendance.store');
    Route::get('/lms/teacher/tugas/{taskId}/submissions', [App\Http\Controllers\LmsController::class, 'getTaskSubmissions'])->name('lms.teacher.tugas.submissions');
    Route::post('/lms/teacher/tugas/grades', [App\Http\Controllers\LmsController::class, 'saveTaskGrades'])->name('lms.teacher.tugas.grades');

   // 👇 RUTE JADWAL HEADMASTER
    Route::get('/lms/{schoolId}/teacher-schedule/get-data/{classId}', [App\Http\Controllers\HeadmasterController::class, 'getScheduleDataAjax']);
    Route::post('/lms/{role}/{schoolName}/{schoolId}/teacher-schedule/save', [App\Http\Controllers\HeadmasterController::class, 'saveSchedule']);
    Route::post('/lms/{role}/{schoolName}/{schoolId}/pengumuman/store', [App\Http\Controllers\HeadmasterController::class, 'storePengumuman'])->name('lms.kepsek.pengumuman.store');

    
    // --------------------------------------------------------------------
    // ROUTES STUDENT DASHBOARD
    // --------------------------------------------------------------------
    Route::get('/lms/{role}/{schoolName}/{schoolId}/student/dashboard', [StudentDashboardController::class, 'index'])->name('lms.student.dashboard');
    Route::get('/lms/student/dashboard/cheating-history/data-paginate', [StudentDashboardController::class, 'getStudentAssessmentCheatingHistory'])->name('lms.studentAssessmentCheatingHistory.dashboard');
    Route::post('/student/announcement/mark-read', [StudentDashboardController::class, 'markAnnouncementAsRead'])->name('lms.studentAnnouncement.markRead');

    // daily reflection
    Route::get('/lms/{role}/{schoolName}/{schoolId}/student/daily-reflection/paginate', [StudentDashboardController::class, 'getDailyReflection'])->name('lms.student.daily-reflection.paginate');

    // submit form daily reflection
    Route::post('/lms/{role}/{schoolName}/{schoolId}/student/daily-reflection/store', [StudentDashboardController::class, 'dailyReflectionStore'])->name('lms.student.daily-reflection.store');

    // BLOK KEPALA SEKOLAH
    Route::get('/lms/{role}/{schoolName}/{schoolId}/headmaster/dashboard', [HeadmasterController::class, 'index'])->name('lms.headmaster.dashboard.view');
    
    // Monitoring Laporan Akademik & Aktivitas
    Route::get('/{role}/{schoolName}/{schoolId}/headmaster/laporan-akademik', [HeadmasterController::class, 'laporanAkademik'])->name('lms.headmaster.academic.report');
    Route::get('/{role}/{schoolName}/{schoolId}/headmaster/aktivitas-guru', [HeadmasterController::class, 'aktivitasGuru'])->name('lms.headmaster.teacher.activity');

    Route::get('/{role}/{schoolName}/{schoolId}/headmaster/schedule', [HeadmasterController::class, 'scheduleView'])->name('lms.headmaster.schedule.view');
    Route::get('/{role}/{schoolName}/{schoolId}/headmaster/schedule/get-data', [HeadmasterController::class, 'getScheduleDataAjax'])->name('lms.headmaster.schedule.data');
    Route::post('/{role}/{schoolName}/{schoolId}/headmaster/schedule/save', [HeadmasterController::class, 'saveSchedule'])->name('lms.headmaster.schedule.save'); 

    Route::get('/{role}/{schoolName}/{schoolId}/calendar', [HeadmasterController::class, 'CalendarView'])->name('lms.headmaster.calendar.view'); 
    Route::post('/{role}/{schoolName}/{schoolId}/calendar/save', [HeadmasterController::class, 'saveCalendarData'])->name('lms.headmaster.calendar.save');

    // =========================================================
    // POLLING MANAGEMENT (KEPALA SEKOLAH)
    // =========================================================
    Route::get('/lms/kepsek/polling', [HeadmasterController::class, 'pollingIndex'])->name('kepsek.polling.index');
    Route::post('/lms/kepsek/polling/store', [HeadmasterController::class, 'pollingStore'])->name('kepsek.polling.store');
    Route::delete('/lms/kepsek/polling/destroy/{id}', [HeadmasterController::class, 'pollingDestroy'])->name('kepsek.polling.destroy');
    Route::get('/lms/kepsek/polling/{id}/breakdown', [HeadmasterController::class, 'getPollingBreakdown']);

    // STUDENT VICE PRINCIPAL ROUTES    
    // dashboard
    Route::get('lms/{role}/{schoolName}/{schoolId}/dashboard', [StudentVicePrincipalController::class, 'index'])->name('lms.student-vice-principal.dashboard.view');

    // REFLECTION MANAGEMENT
    // view
    Route::get('/lms/{role}/{schoolName}/{schoolId}/reflection-management', [StudentVicePrincipalController::class, 'reflectionMaangement'])->name('lms.student-vice-principal.reflection-management.view');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/reflection-management/create', [StudentVicePrincipalController::class, 'createReflectionView'])->name('lms.student-vice-principal.create.reflection.view');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/reflection-management/history', [StudentVicePrincipalController::class, 'reflectionManagementHistoryView'])->name('lms.student-vice-principal.reflection-management-history.view');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/reflection-management/history-detail/{reflectionQuestionId}', [StudentVicePrincipalController::class, 'reflectionManagementHistoryDetailView'])->name('lms.student-vice-principal.reflection-management-history-detail.view');

    // form submit
    Route::get('/lms/{role}/{schoolName}/{schoolId}/reflection-management/form-submit', [StudentVicePrincipalController::class, 'reflectionManagementForm'])->name('lms.student-vice-principal.reflection.form');

    // crud
    Route::post('/lms/{role}/{schoolName}/{schoolId}/reflection-management/store', [StudentVicePrincipalController::class, 'reflectionManagementStore'])->name('lms.student-vice-principal.reflection.store');

    // paginate
    Route::get('/lms/{role}/{schoolName}/{schoolId}/dashboard/load-student-reflection-chart', [StudentVicePrincipalController::class, 'loadStudentReflectionChart'])->name('lms.student-vice-principal.dashboard.load-student-reflection-chart');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/reflection-management/history-recent', [StudentVicePrincipalController::class, 'paginateReflectionHistoryRecent'])->name('lms.student-vice-principal.reflection-management.history-recent');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/reflection-management/daily-reflection-live-preview', [StudentVicePrincipalController::class, 'paginateDailyReflectionLivePreview'])->name('lms.student-vice-principal.reflection-management.daily-reflection-live-preview');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/reflection-management/history/paginate', [StudentVicePrincipalController::class, 'paginateReflectionHistory'])->name('lms.student-vice-principal.reflection-management.history');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/reflection-management/history-detail/{reflectionQuestionId}/load-header', [StudentVicePrincipalController::class, 'loadReflectionDetailHeader'])->name('lms.student-vice-principal.reflection-management-history-detail.load-header');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/reflection-management/history-detail/{reflectionQuestionId}/load-summary', [StudentVicePrincipalController::class, 'loadReflectionDetailSummary'])->name('lms.student-vice-principal.reflection-management-history-detail.load-summary');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/reflection-management/history-detail/{reflectionQuestionId}/load-chart', [StudentVicePrincipalController::class, 'loadReflectionDetailChart'])->name('lms.student-vice-principal.reflection-management-history-detail.load-chart');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/reflection-management/history-detail/{reflectionQuestionId}/student-answer/paginate', [StudentVicePrincipalController::class, 'paginateReflectionStudentAnswer'])->name('lms.student-vice-principal.reflection-management-history-detail.student-answer.paginate');

    // =========================================================
    // ROUTES ORANG TUA
    // =========================================================
    Route::get('/lms/{role}/{schoolName}/{schoolId}/parent/dashboard', [\App\Http\Controllers\ParentController::class, 'index'])->name('lms.parent.dashboard.view');
    Route::post('/lms/parent/polling/{id}/vote', [\App\Http\Controllers\ParentController::class, 'submitPoll'])->name('lms.parent.poll.vote');
    Route::get('/ortu/laporan-nilai', [ParentController::class, 'laporanNilai'])->name('ortu.laporan-nilai');
    Route::get('/ortu/kehadiran', [ParentController::class, 'kehadiran'])->name('ortu.kehadiran');
    Route::get('/ortu/jadwal-pelajaran', [ParentController::class, 'jadwalPelajaran'])->name('ortu.jadwal-pelajaran');
    Route::get('/ortu/kalender-akademik', [ParentController::class, 'kalenderAkademik'])->name('ortu.kalender-akademik');
     
    
    // ROUTES SCHOOL PARTNER
    Route::post('/school-subcsription/store', [SchoolPartnerController::class, 'bulkUploadSchoolPartner'])->name('bulkUploadSchoolPartner.store');
    Route::post('/school-subscription/add-users/store', [SchoolPartnerController::class, 'bulkUploadAddUsers'])->name('bulkUploadAddUsers.store');
    Route::post('/lms/teacher/tugas/store', [App\Http\Controllers\LmsController::class, 'storeTugas'])->name('lms.teacher.tugas.store');

     // ROUTES LIBRARY FEATURE
    Route::get('/administrator/library', [LibraryController::class, 'index'])->name('library.index');
    Route::post('/administrator/library/store', [LibraryController::class, 'store'])->name('library.store');
    Route::post('/administrator/library/update/{id}', [LibraryController::class, 'update'])->name('library.update');
    Route::put('/administrator/library/update/{id}', [LibraryController::class, 'update'])->name('library.update');
    Route::delete('/library/delete/{id}', [LibraryController::class, 'delete'])->name('library.delete');
    Route::get('/administrator/library', [LibraryController::class, 'administrator'])->name('library.administrator');
    Route::get('/lms/student/library', [LibraryController::class, 'studentLibrary'])->name('student.library');
    Route::get('/lms/student/library/read/{id}', [LibraryController::class, 'readBook'])->name('student.library.read');
    Route::post('/lms/student/library/submit', [LibraryController::class, 'submitTask'])->name('student.library.submit');
    Route::post('/administrator/library/chapter/store', [LibraryController::class, 'storeChapter'])->name('library.chapter.store');
    Route::get('/student/library/mapel/{mapel}', [LibraryController::class, 'mapelDetail'])->name('student.library.mapel');
    Route::get('/get-bab/{mapel_id}', [LibraryController::class, 'getBab']);

    // ROUTES SCHOOL ADMIN
    // dashboard
    Route::get('/lms/{role}/{schoolName}/{schoolId}/admin/dashboard', [SchoolAdminDashboardController::class, 'index'])->name('lms.schoolAdmin.dashboard.view');

    // edit school logo
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/edit-school-logo', [LmsController::class, 'editSchoolLogo'])->name('lms.editLogo');

}); // END OF AUTH MIDDLEWARE GROUP