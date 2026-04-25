<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
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
use App\Http\Controllers\Lms\SubjectPassingGradeCriteria\SubjectPassingGradeCriteriaController;
use App\Http\Controllers\Lms\TeacherSubject\TeacherSubjectController;
use App\Http\Controllers\Lms\UserManagement\AccountController;
use App\Http\Controllers\Lms\UserManagement\RoleController;
use App\Http\Controllers\SchoolAdminDashboardController;
use App\Http\Controllers\StudentDashboardController;
use App\Http\Controllers\TeacherAcademicTranscriptController;
use App\Http\Controllers\TeacherGradeLedgerController;
use App\Http\Controllers\TeacherInformationController;
use App\Http\Middleware\AuthMiddleware;
use App\Http\Middleware\RedirectIfAuthenticated;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// ROUTE FALLBACK
Route::fallback(function () {
    // Sudah login → arahkan ke dashboard
    if (Auth::check()) {
        return redirect()->route('beranda');
    }

    // Belum login → arahkan ke login
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
Route::get('/kelas/{id}', [MasterAcademicController::class, 'getKelas']); // kelas by fase

// service for school partner & non school partner
Route::get('/kurikulum/{curriculumId}/service', [MasterAcademicController::class, 'getServiceByKurikulum']); // service by kurikulum
Route::get('/kurikulum/{curriculumId}/{schoolId}/service', [MasterAcademicController::class, 'getServiceByKurikulum']); // service by kurikulum

// kelas for school partner & non school partner
Route::get('/kurikulum/{curriculumId}/kelas', [MasterAcademicController::class, 'getKelasByKurikulum']); // kelas by kurikulum
Route::get('/kurikulum/{curriculumId}/{schoolId}/kelas', [MasterAcademicController::class, 'getKelasByKurikulum']); // kelas by kurikulum

// route dependent dropdown mapel by kelas non school partner & school partner
Route::get('/kelas/{kelasId}/mapel', [MasterAcademicController::class, 'getMapelByKelas']); // mapel by kelas
Route::get('/kelas/{kelasId}/{schoolId}/mapel', [MasterAcademicController::class, 'getMapelByKelas']); // mapel by kelas

// route dependent dropdown rombel kelas by kelas
Route::get('/kelas/{kelasId}/rombel-kelas/{schoolId}', [MasterAcademicController::class, 'getRombelByKelas']); // rombel kelas by kelas

Route::get('/mapel/{mapelId}/bab', [MasterAcademicController::class, 'getBabByMapel']); // bab by mapel
Route::get('/bab/{babId}/sub-bab', [MasterAcademicController::class, 'getSubBabByBab']); // sub bab by bab

Route::get('/service/{service}/rules', [ServiceRuleController::class, 'index']); // rules by service

// MIDDLEWARE LOGIN
Route::middleware([AuthMiddleware::class])->group(function () {
    // DASHBOARD
    Route::get('/beranda', [DashboardController::class, 'index'])->name('beranda');

    //ROUTES SYLLABUS-SERVICES
    // VIEWS
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

    // ROUTES SCHOOL CURRICULUM MANAGEMENT HIERARCHY
    // views
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/academic-management/kurikulum', [SchoolSyllabusController::class, 'curriculumView'])->name('schoolCurriculumManagement.view');
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/academic-management/{curriculumName}/{curriculumId}/fase', [SchoolSyllabusController::class, 'faseView'])->name('schoolFaseManagement.view');
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/academic-management/{curriculumName}/{curriculumId}/{faseId}/kelas', [SchoolSyllabusController::class, 'kelasView'])->name('schoolKelasManagement.view');
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/academic-management/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/mapel', [SchoolSyllabusController::class, 'mapelView'])->name('schoolMapelManagement.view');
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/academic-management/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/{mapelId}/bab', [SchoolSyllabusController::class, 'babView'])->name('schoolBabManagement.view');
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/academic-management/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/{mapelId}/{babId}/sub-bab', [SchoolSyllabusController::class, 'subBabView'])->name('schoolSubBabManagement.view');

    // crud mapel
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/mapel/store', [SchoolSyllabusController::class, 'mapelStore'])->name('schoolMapelManagement.store');
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/mapel/{mapelId}/edit', [SchoolSyllabusController::class, 'mapelEdit'])->name('schoolMapelManagement.edit');
    Route::put('/lms/school-subscription/{schoolName}/{schoolId}/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/mapel/{mapelId}/activate', [SchoolSyllabusController::class, 'mapelActivate'])->name('schoolMapelManagement.activate');

    // crud bab
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/{mapelId}/bab/store', [SchoolSyllabusController::class, 'babStore'])->name('schoolBabManagement.store');
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/{mapelId}/bab/{babId}/edit', [SchoolSyllabusController::class, 'babEdit'])->name('schoolBabManagement.edit');
    Route::put('/lms/school-subscription/{schoolName}/{schoolId}/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/{mapelId}/bab/{babId}/activate', [SchoolSyllabusController::class, 'babActivate'])->name('schoolBabManagement.activate');

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

    // ROUTES LMS FEATURE
    // views (administrator)
    Route::get('/lms/school-subscription', [LmsController::class, 'lmsSchoolSubscriptionView'])->name('lms.schoolSubscription.view');

    // routes academic management
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/academic-management', [AcademicDashboardController::class, 'lmsAcademicManagementView'])->name('lms.academicManagement.view');
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/academic-management/paginate', [AcademicDashboardController::class, 'paginateLmsAcademicManagement'])->name('lms.academicManagement.paginate');

    // route management role account
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/academic-management/management-role-account', [RoleController::class, 'lmsManagementRolesView'])->name('lms.managementRoles.view');

    // route management account
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/academic-management/management-role-account/{role}/management-accounts', [AccountController::class, 'lmsManagementAccountView'])->name('lms.managementAccount.view');

    // route management majors
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/academic-management/management-role-account/{role}/management-majors', [MajorController::class, 'lmsManagementMajorsView'])->name('lms.managementMajors.view');

    // routes views class by major and no major
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/academic-management/management-role-account/{role}/management-majors/{majorId}/management-class', [ClassController::class, 'lmsManagementClassView'])->name('lms.managementClass.view.major');
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/academic-management/management-role-account/{role}/management-class', [ClassController::class, 'lmsManagementClassView'])->name('lms.managementClass.view.noMajor');

    // routes views students in class by major and no major
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/academic-management/management-role-account/{role}/management-class/{classId}/management-majors/{majorId}/management-students', [StudentSchoolClassController::class, 'lmsManagementStudentsView'])->name('lms.managementStudents.view.major');
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/academic-management/management-role-account/{role}/management-class/{classId}/management-students', [StudentSchoolClassController::class, 'lmsManagementStudentsView'])->name('lms.managementStudents.view.noMajor');

    // CRUD
    // routes crud majors
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/management-role-account/{role}/management-majors/create', [MajorController::class, 'lmsManagementCreateMajor'])->name('lms.managementCreateMajor.store');
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/management-role-account/{role}/management-majors/{majorId}/edit', [MajorController::class, 'lmsManagementEditMajor'])->name('lms.managementEditMajor.store');

    // routes create management class by major and no major
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/management-role-account/{role}/management-majors/{majorId}/management-class/create', [ClassController::class, 'lmsManagementCreateClass'])->name('lms.managementCreateClass.store.major');
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/management-role-account/{role}/management-class/create', [ClassController::class, 'lmsManagementCreateClass'])->name('lms.managementCreateClass.store.noMajor');

    // routes edit management class by major and no major
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/management-role-account/{role}/management-class/{classId}/management-majors/{majorId}/edit', [ClassController::class, 'lmsManagementEditClass'])->name('lms.managementClassWithMajor.edit');
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/management-role-account/{role}/management-class/{classId}/edit', [ClassController::class, 'lmsManagementEditClass'])->name('lms.managementClassNoMajor.edit');

    // routes activate school subscription, account, major, class, student in class
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
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/management-role-account/{role}/management-accounts/paginate', [AccountController::class, 'paginateLmsSchoolAccount'])->name('lms.SchoolSubscriptionAccount.paginate');
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/role-account/{role}/management-majors/paginate', [MajorController::class, 'paginateLmsSchoolSubscriptionMajors'])->name('lms.SchoolSubscriptionMajors.paginate');

    // paginate class by major and no major
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/role-account/{role}/management-majors/{majorId}/management-class/paginate', [ClassController::class, 'paginateLmsSchoolSubscriptionClass'])->name('lms.SchoolSubscriptionClass.paginate.major');
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/role-account/{role}/management-class/paginate', [ClassController::class, 'paginateLmsSchoolSubscriptionClass'])->name('lms.SchoolSubscriptionClass.paginate.noMajor');

    // paginate users by major and no major
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/role-account/{role}/management-class/{classId}/management-majors/{majorId}/management-students/paginate', [StudentSchoolClassController::class, 'paginateLmsSchoolSubscriptionUsers'])->name('lms.SchoolSubscriptionUsers.paginate.major');
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/role-account/{role}/management-class/{classId}/management-students/paginate', [StudentSchoolClassController::class, 'paginateLmsSchoolSubscriptionUsers'])->name('lms.SchoolSubscriptionUsers.paginate.noMajor');

    // ROUTES QUESTION BANK MANAGEMENT
    // view
    // question bank management no school partner & school partner
    Route::get('/lms/question-bank-management', [QuestionBankController::class, 'lmsQuestionBankManagementView'])->name('lms.questionBankManagement.view.noSchoolPartner');
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/academic-management/question-bank-management', [QuestionBankController::class, 'lmsQuestionBankManagementView'])->name('lms.questionBankManagement.view.schoolPartner');

    // review question bank no school partner & school partner
    Route::get('/lms/question-bank-management/source/{source}/review/question-type/{questionType}/{subBabId}', [QuestionBankController::class, 'lmsDefaultQuestionBankManagementDetailView'])->name('lms.questionBankManagementDetail.view.noSchoolPartner');
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/academic-management/question-bank-management/source/{source}/review/question-type/{questionType}/{subBabId}', [QuestionBankController::class, 'lmsSchoolQuestionBankManagementDetailView'])->name('lms.questionBankManagementDetail.view.schoolPartner');

    // edit question bank no school partner & school partner
    Route::get('/lms/question-bank-management/source/{source}/review/question-type/{questionType}/{subBabId}/{questionId}/edit', [QuestionBankController::class, 'lmsDefaultQuestionBankManagementEditView'])->name('lms.questionBankManagementEdit.view.noSchoolPartner');
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/academic-management/question-bank-management/source/{source}/review/question-type/{questionType}/{subBabId}/{questionId}/edit', [QuestionBankController::class, 'lmsSchoolQuestionBankManagementEditView'])->name('lms.questionBankManagementEdit.view.schoolPartner');

    // form question bank edit no school partner & school partner
    Route::get('/lms/question-bank-management/bank-soal/form/source/{source}/review/question-type/{questionType}/{subBabId}/{questionId}/edit', [QuestionBankController::class, 'formEditQuestion'])->name('lms.bankSoal.form.edit.question.noSchoolPartner');
    Route::get('/lms/school-subscription/question-bank-management/bank-soal/form/source/{source}/review/question-type/{questionType}/{subBabId}/{questionId}/{schoolName}/{schoolId}/edit', [QuestionBankController::class, 'formEditQuestion'])->name('lms.bankSoal.form.edit.question.schoolPartner');

    // crud bank soal
    // upload bank soal no school partner & school partner
    Route::post('/lms/question-bank-management/store', [QuestionBankController::class, 'lmsQuestionBankManagementStore'])->name('lms.questionBankManagement.store.noSchoolPartner');
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/question-bank-management/store', [QuestionBankController::class, 'lmsQuestionBankManagementStore'])->name('lms.questionBankManagement.store.schoolPartner');

    // edit & delete image bank soal with ckeditor
    Route::post('/lms/bank-soal/edit-image', [QuestionBankController::class, 'editImageBankSoal'])->name('lms.editImage');
    Route::post('/lms/bank-soal/delete-image/endpoint', [QuestionBankController::class, 'deleteImageBankSoal'])->name('lms.deleteImage');

    // activate question bank no school partner & school partner
    Route::put('/lms/question-bank-management/{subBabId}/source/{source}/question-type/{questionType}/activate', [QuestionBankController::class, 'lmsActivateQuestionBank'])->name('lms.questionBank.activate.noSchoolPartner');
    Route::put('/lms/school-subscription/question-bank-management/{subBabId}/source/{source}/question-type/{questionType}/{schoolName}/{schoolId}/activate', [QuestionBankController::class, 'lmsActivateQuestionBank'])->name('lms.questionBank.activate.schoolPartner');

    // edit bank soal no school partner & school partner submit form
    Route::post('/lms/question-bank-management/{questionId}/edit', [QuestionBankController::class, 'lmsQuestionBankManagementEdit'])->name('lms.questionBankManagement.edit');

    // paginate
    // question bank management no school partner & school partner
    Route::get('/lms/question-bank-management/paginate', [QuestionBankController::class, 'paginateLmsQuestionBankManagement'])->name('lms.questionBankManagement.paginate.noSchoolPartner');
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/question-bank-management/paginate', [QuestionBankController::class, 'paginateLmsQuestionBankManagement'])->name('lms.questionBankManagement.paginate.schoolPartner');

    // paginate review question bank no school partner & school partner
    Route::get('/lms/question-bank-management/source/{source}/review/question-type/{questionType}/{subBabId}/paginate', [QuestionBankController::class, 'paginateReviewQuestionBank'])->name('lms.questionBankManagementDetail.paginate.noSchoolPartner');
    Route::get('/lms/question-bank-management/source/{source}/review/question-type/{questionType}/{subBabId}/school-subscription/{schoolName}/{schoolId}/paginate', [QuestionBankController::class, 'paginateReviewQuestionBank'])->name('lms.reviewQuestionBank.paginate.schoolPartner');

    // ROUTES CONTENT MANAGEMENT
    // view content management no school partner & school partner
    Route::get('/lms/content-management', [ContentBankController::class, 'lmsContentManagementView'])->name('lms.contentManagement.view.noSchoolPartner');
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/academic-management/content-management', [ContentBankController::class, 'lmsContentManagementView'])->name('lms.contentManagement.view.schoolPartner');

    // view content management edit no school partner & school partner
    Route::get('/lms/content-management/{contentId}/edit', [ContentBankController::class, 'lmsDefaultContentManagementEditView'])->name('lms.contentManagement.edit.view.noSchoolPartner');
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/academic-management/content-management/{contentId}/edit', [ContentBankController::class, 'lmsSchoolContentManagementEditView'])->name('lms.contentManagement.edit.view.schoolPartner');

    // view content management review no school partner & school partner
    Route::get('/lms/content-management/{contentId}/review', [ContentBankController::class, 'lmsReviewContentDefault'])->name('lms.contentManagement.review.noSchoolPartner');
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/academic-management/content-management/{contentId}/review', [ContentBankController::class, 'lmsReviewContentSchool'])->name('lms.contentManagement.review.schoolPartner');

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

    // ROUTES ASSESSMENT TYPE MANAGEMENT
    // views
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/academic-management/assessment-type-management', [AssessmentTypeController::class, 'lmsAssessmentTypeManagementView'])->name('lms.assessmentTypeManagement.view');

    // crud
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/assessment-type-management/store', [AssessmentTypeController::class, 'lmsAssessmentTypeManagementStore'])->name('lms.assessmentTypeManagement.store');
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/assessment-type-management/{assessmentTypeId}/edit', [AssessmentTypeController::class, 'lmsAssessmentTypeManagementEdit'])->name('lms.assessmentTypeManagement.edit');
    Route::put('/lms/school-subscription/{schoolName}/{schoolId}/assessment-type-management/{assessmentTypeId}/activate', [AssessmentTypeController::class, 'lmsAssessmentTypeManagementActivate'])->name('lms.assessmentTypeManagement.activate');

    // paginate
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/assessment-type-management/paginate', [AssessmentTypeController::class, 'paginateLmsAssessmentTypeManagement'])->name('lms.assessmentTypeManagement.paginate');

    // ROUTES TEACHER SUBJECT MANAGEMENT
    // views
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/academic-management/subject-teacher-management', [TeacherSubjectController::class, 'lmsTeacherSubjectManagement'])->name('lmsTeacherSubjectManagement.view');

    // crud
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/subject-teacher-management/store', [TeacherSubjectController::class, 'lmsTeacherSubjectManagementStore'])->name('lmsTeacherSubjectManagement.store');
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/subject-teacher-management/{teacherSubjectId}/edit', [TeacherSubjectController::class, 'lmsTeacherSubjectManagementEdit'])->name('lmsTeacherSubjectManagement.edit');
    Route::put('/lms/school-subscription/{schoolName}/{schoolId}/subject-teacher-management/{teacherSubjectId}/activate', [TeacherSubjectController::class, 'lmsTeacherSubjectManagementActivate'])->name('lmsTeacherSubjectManagement.activate');

    // paginate
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/subject-teacher-management/paginate', [TeacherSubjectController::class, 'paginateLmsTeacherSubjectManagement'])->name('lmsTeacherSubjectManagement.paginate');

    // ROUTES ASSESSMENT WEIGHT MANAGEMENT
    // views
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/academic-management/assessment-weight-management', [AssessmentWeightController::class, 'assessmentWeight'])->name('lms.assessmentWeight.view');

    // crud
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/assessment-weight-management/store', [AssessmentWeightController::class, 'assessmentWeightStore'])->name('lms.assessmentWeight.store');
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/assessment-weight-management/{assessmentWeightId}/edit', [AssessmentWeightController::class, 'assessmentWeightEdit'])->name('lms.assessmentWeight.edit');

    // paginate
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/assessment-weight-management/paginate', [AssessmentWeightController::class, 'paginateAssessmentWeight'])->name('lms.assessmentWeight.paginate');

    // ROUTES SUBJECT PASSING GRADE CRITERIA MANAGEMENT
    // views
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/academic-management/subject-passing-grade-criteria-management', [SubjectPassingGradeCriteriaController::class, 'subjectPassingGradeCriteria'])->name('lms.subjectPassingGradeCriteria.view');

    // crud
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/subject-passing-grade-criteria-management/store', [SubjectPassingGradeCriteriaController::class, 'subjectPassingGradeCriteriaStore'])->name('lms.subjectPassingGradeCriteria.store');
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/subject-passing-grade-criteria-management/{subjectPassingGradeCriteriaId}/edit', [SubjectPassingGradeCriteriaController::class, 'subjectPassingGradeCriteriaEdit'])->name('lms.subjectPassingGradeCriteria.edit');
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/subject-passing-grade-criteria-management/bulkupload-store', [SubjectPassingGradeCriteriaController::class, 'bulkUploadSubjectPassingGradeCriteria'])->name('lms.subjectPassingGradeCriteria.bulkUpload.store');

    // paginate
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/subject-passing-grade-criteria-management/paginate', [SubjectPassingGradeCriteriaController::class, 'paginateSubjectPassingGradeCriteria'])->name('lms.subjectPassingGradeCriteria.paginate');

    // ROUTES STUDENT LMS
    // components routes
    Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/subject-progress', [StudentSubjectProgressController::class, 'index'])->name('lms.studentSubjectProgress.index');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/subject-progress/data', [StudentSubjectProgressController::class, 'data'])->name('lms.studentSubjectProgress.data');

    // learning routes
    Route::get('/lms/{role}/{schoolName}/{schoolId}', [StudentLearningController::class, 'lmsStudentView'])->name('lms.student.view');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning', [StudentLearningController::class, 'studentLearning'])->name('lms.studentLearning.view');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/service/{serviceId}', [StudentLearningController::class, 'studentReviewMeeting'])->name('lms.studentReviewMeeting.view');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/service/{serviceId}/show-content/{meetingContentId}', [StudentLearningController::class, 'showStudentReviewContent'])->name('lms.studentReviewContent.show');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/service/{serviceId}/download-content/{meetingContentId}', [StudentLearningController::class, 'downloadStudentContent'])->name('lms.studentContent.download');

    // paginate
    Route::get('/lms/{role}/{schoolName}/{schoolId}/paginate', [StudentLearningController::class, 'paginateLmsStudent'])->name('lms.student.paginate');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/paginate', [StudentLearningController::class, 'paginateStudentLearning'])->name('lms.studentLearning.paginate');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/service/{serviceId}/semester/{semester}/paginate', [StudentLearningController::class, 'paginateStudentReviewMeeting'])->name('lms.studentReviewMeeting.paginate');

    // preview assessment routes
    Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}', [StudentAssessmentController::class, 'studentPreviewAssessment'])->name('lms.studentPreviewAssessment.view');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/mode/{mode}/{parentAssessmentId}', [StudentAssessmentController::class, 'studentPreviewAssessment'])->name('lms.studentPreviewAssessment.mode.view');

    // load assessment data by semester
    Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/semester/{semester}', [StudentAssessmentController::class, 'loadStudentPreviewAssessment'])->name('lms.loadStudentPreviewAssessment');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/semester/{semester}/mode/{mode}/{parentAssessmentId}', [StudentAssessmentController::class, 'loadStudentPreviewAssessment'])->name('lms.loadStudentPreviewAssessment.mode');
    Route::get('/lms/check-assessment-status/{assessmentId}', [StudentAssessmentController::class, 'checkAssessmentStatus'])->name('lms.checkAssessmentStatus');

    // assessment (exam)
    Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/semester/{semester}/test/{assessmentId}', [StudentAssessmentExamController::class, 'studentAssessmentExam'])->name('lms.studentAssessmentExan.view');

    // form
    Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/semester/{semester}/form/{assessmentId}', [StudentAssessmentExamController::class, 'studentAssessmentExamForm'])->name('lms.studentAssessmentExan.form');

    // routes timer
    Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/semester/{semester}/form/{assessmentId}/start-timer', [StudentAssessmentExamController::class, 'startTimer'])->name('lms.startTimer.test');

    // routes report tab switch (cheating detection)
    Route::post('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/semester/{semester}/form/{assessmentId}/report-tab-switch', [StudentAssessmentExamController::class, 'reportTabSwitch'])->name('lms.reportTabSwitch.cheating');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/semester/{semester}/form/{assessmentId}/attempt-status', [StudentAssessmentExamController::class, 'checkAttemptStatus'])->name('lms.checkAttemptStatus.cheating');

    // answer
    Route::post('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/semester/{semester}/form/{assessmentId}/answer', [StudentAssessmentExamController::class, 'studentAssessmentExamAnswer'])->name('lms.studentAssessmentExan.answer');
    Route::post('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/semester/{semester}/form/{assessmentId}/project-submission', [StudentAssessmentExamController::class, 'studentProjectSubmission'])->name('lms.studentProjectSubmission.answer');

    // end assessment
    Route::post('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/semester/{semester}/form/{assessmentId}/emd', [StudentAssessmentExamController::class, 'studentAssessmentExamEnd'])->name('lms.studentAssessmentExan.emd');

    // routes store and delete image essay
    Route::post('/lms/image-essay/store-image/endpoint', [StudentAssessmentExamController::class, 'storeImageEssay'])->name('assessment-test.storeImage');
    Route::post('/lms/image-essay/delete-image/endpoint', [StudentAssessmentExamController::class, 'deleteImageEssay'])->name('assessment-test.deleteImage');

    // results
    Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/semester/{semester}/assessment/{assessmentId}/result-test', [StudentAssessmentExamController::class, 'studentResultAssessment'])->name('lms.studentAssessment.result');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/semester/{semester}/assessment/{assessmentId}/project-result', [StudentAssessmentExamController::class, 'studentProjectResult'])->name('lms.studentProjectAssessment.result');
    Route::get('/lms/student/dashboard', [StudentDashboardController::class, 'index'])->name('lms.student.dashboard');
    Route::get('/lms/{schoolId}/teacher-schedule/get-data/{classId}', [\App\Http\Controllers\TeacherInformationController::class, 'getScheduleDataAjax']);
    // polling
    Route::post('/lms/student/polling/vote', [StudentDashboardController::class, 'submitVote'])->name('lms.studentPolling.vote');

    // ROUTES TEACHER LMS
    // content management
    Route::get('/lms/Guru/{schoolName}/{schoolId}/beranda', [LmsController::class, 'lmsTeacherView'])->name('lms.teacher.view');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-content-management', [TeacherContentController::class, 'teacherContentManagement'])->name('lms.teacherContentManagement.view');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-content-management/{contentId}/review', [TeacherContentController::class, 'teacherReviewContent'])->name('lms.teacherContentManagement.review.view');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-content-management/{contentId}/edit', [TeacherContentController::class, 'teacherEditContent'])->name('lms.teacherContentManagement.edit.view');

    // paginate
    Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-content-management/paginate', [TeacherContentController::class, 'paginateTeacherContentManagement'])->name('lms.teacherContentManagement.paginate');

    // content for release
    // views
    Route::get('/lms/{role}/{schoolName}/{schoolId}/content-for-release', [TeacherContentReleaseController::class, 'teacherContentForRelease'])->name('lms.teacherContentForRelease.view');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/content-for-release/rombel-kelas/{schoolClassId}/subject/{mapelId}/semester/{semester}/service/{serviceId}/review-meetings', [TeacherContentReleaseController::class, 'teacherContentForReleaseReviewMeeting'])->name('lms.teacherContentForReleaseReviewMeeting.view');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/content-for-release/rombel-kelas/{schoolClassId}/subject/{mapelId}/semester/{semester}/service/{serviceId}/review-content/{meetingContentId}', [TeacherContentReleaseController::class, 'teacherContentForReleaseReviewContent'])->name('lms.teacherContentForReleaseReviewContent.view');

    // crud
    Route::get('/lms/{role}/{schoolName}/{schoolId}/content-for-release/form', [TeacherContentReleaseController::class, 'teacherFormContentForRelease'])->name('lms.teacherContentForRelease.form');
    Route::post('/lms/{role}/{schoolName}/{schoolId}/content-for-release/store', [TeacherContentReleaseController::class, 'teacherContentForReleaseStore'])->name('lms.teacherContentForRelease.store');
    Route::post('/lms/{role}/{schoolName}/{schoolId}/content-for-release/{meetingContentId}/edit', [TeacherContentReleaseController::class, 'teacherContentForReleaseEdit'])->name('lms.teacherContentForRelease.edit');
    Route::put('/lms/{role}/{schoolName}/{schoolId}/content-for-release/{meetingContentId}/activate', [TeacherContentReleaseController::class, 'teacherContentForReleaseActivate'])->name('lms.teacherContentForRelease.activate');

    // paginate
    Route::get('/lms/{role}/{schoolName}/{schoolId}/content-for-release/paginate', [TeacherContentReleaseController::class, 'paginateTeacherContentForRelease'])->name('lms.teacherContentForRelease.paginate');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/content-for-release/rombel-kelas/{schoolClassId}/subject/{mapelId}/semester/{semester}/service/{serviceId}/review-meetings/paginate', [TeacherContentReleaseController::class, 'paginateTeacherContentForReleaseReviewMeeting'])->name('lms.teacherContentForReleaseReviewMeeting.paginate');

    // assessment management
    // views
    Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-assessment-management', [TeacherAssessmentController::class, 'teacherAssessmentManagement'])->name('lms.teacherAssessmentManagement.view');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-assessment-management/{mode}/{parentAssessmentId}', [TeacherAssessmentController::class, 'teacherAssessmentManagement'])->name('lms.teacherAssessmentManagement.mode.view');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-assessment-management/{assessmentId}/edit/view', [TeacherAssessmentController::class, 'teacherAssessmentManagementEdit'])->name('lms.teacherAssessmentManagementEdit.view');

    // form
    Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-assessment-management/form', [TeacherAssessmentController::class, 'teacherFormAssessmentManagement'])->name('lms.teacherFormAssessmentManagement.form');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-assessment-management/{assessmentId}/edit/form', [TeacherAssessmentController::class, 'teacherFormAssessmentManagementEdit'])->name('lms.teacherFormAssessmentManagement.edit');

    // crud
    Route::post('/lms/{role}/{schoolName}/{schoolId}/teacher-assessment-management/validate-step-form/store', [TeacherAssessmentController::class, 'teacherFormAssessmentValidateStep'])->name('lms.teacherAssessmentManagementValidateStep.form');
    Route::post('/lms/{role}/{schoolName}/{schoolId}/teacher-assessment-management/store', [TeacherAssessmentController::class, 'teacherFormAssessmentManagementStore'])->name('lms.teacherAssessmentManagement.store');
    Route::post('/lms/{role}/{schoolName}/{schoolId}/teacher-assessment-management/{assessmentId}/edit', [TeacherAssessmentController::class, 'teacherAssessmentManagementEditSubmission'])->name('lms.teacherAssessmentManagement.edit');
    Route::put('/lms/{role}/{schoolName}/{schoolId}/teacher-assessment-management/{assessmentId}/activate', [TeacherAssessmentController::class, 'teacherFormAssessmentManagementActivate'])->name('lms.teacherAssessmentManagement.activate');

    // paginate
    Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-assessment-management/paginate', [TeacherAssessmentController::class, 'paginateTeacherAssessmentManagement'])->name('lms.teacherAssessmentManagement.paginate');

    // question bank management
    // views
    Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-question-bank-management', [TeacherQuestionBankController::class, 'teacherQuestionBankManagement'])->name('lms.teacherQuestionBankManagement.view');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-question-bank-management/source/{source}/review/question-type/{questionType}/{subBabId}', [TeacherQuestionBankController::class, 'teacherQuestionBankManagementDetail'])->name('lms.teacherQuestionBankManagement.detail.view');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-question-bank-management/source/{source}/review/question-type/{questionType}/{subBabId}/{questionId}/edit', [TeacherQuestionBankController::class, 'teacherQuestionBankManagementEdit'])->name('lms.teacherQuestionBankManagement.edit.view');

    // paginate
    Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-question-bank-management/paginate', [TeacherQuestionBankController::class, 'paginateTeacherQuestionBankManagement'])->name('lms.teacherQuestionBankManagement.paginate');

    // question bank for release
    // views
    Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-question-bank-for-release', [TeacherQuestionBankReleaseController::class, 'teacherQuestionBankForRelease'])->name('lms.teacherQuestionBankForRelease.view');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-question-bank-for-release/review/{assessmentQuestionId}', [TeacherQuestionBankReleaseController::class, 'teacherReviewQuestionBankForRelease'])->name('lms.teacherReviewQuestionBankForRelease.view');

    // form
    Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-question-bank-for-release/form', [TeacherQuestionBankReleaseController::class, 'teacherFormQuestionBankForRelease'])->name('lms.teacherQuestionBankForRelease.form');

    // crud
    Route::post('/lms/{role}/{schoolName}/{schoolId}/teacher-question-bank-for-release/store', [TeacherQuestionBankReleaseController::class, 'teacherQuestionBankForReleaseStore'])->name('lms.teacherQuestionBankForRelease.store');

    // paginate
    Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-question-bank-for-release/paginate', [TeacherQuestionBankReleaseController::class, 'paginateTeacherQuestionBankForRelease'])->name('lms.teacherQuestionBankForRelease.paginate');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-question-bank-for-release/review/{assessmentQuestionId}/paginate', [TeacherQuestionBankReleaseController::class, 'paginateTeacherReviewQuestionBankForRelease'])->name('lms.teacherReviewQuestionBankForRelease.paginate');

    // TEACHER ASSESSMENT GRADING
    // assessment grading management
    // views
    Route::get('/lms/{role}/{schoolName}/{schoolId}/assessment-grading', [TeacherAssessmentGradingController::class, 'assessmentGradingManagement'])->name('lms.assessmentGradingManagement.view');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/assessment-grading/{assessmentId}/mode/{mode}/student-list', [TeacherAssessmentGradingController::class, 'assessmentGradingStudentList'])->name('lms.assessmentGradingStudentList.view');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/assessment-grading/{assessmentId}/mode/{mode}/student-list/{studentId}/scoring', [TeacherAssessmentGradingController::class, 'assessmentGradingStudentAnswer'])->name('lms.assessmentGradingStudentAnswer.view');

    // crud
    Route::post('/lms/{role}/{schoolName}/{schoolId}/assessment-grading/{assessmentId}/student-list/{studentId}/scoring/submission/{schoolAssessmentQuestionId}', [TeacherAssessmentGradingController::class, 'submitAssessmentStudentScore'])->name('lms.assessmentGradingStudentAnswer.submission');
    Route::post('/lms/{role}/{schoolName}/{schoolId}/assessment-grading/{assessmentId}/student-list/{studentId}/scoring/submission/{submissionId}/project', [TeacherAssessmentGradingController::class, 'submitAssessmentStudentProjectScore'])->name('lms.assessmentGradingStudentProject.submission');

    // paginate
    Route::get('/lms/{role}/{schoolName}/{schoolId}/assessment-grading/paginate', [TeacherAssessmentGradingController::class, 'paginateAssessmentGrading'])->name('lms.assessmentGrading.paginate');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/assessment-grading/{assessmentId}/mode/{mode}/student-list/paginate', [TeacherAssessmentGradingController::class, 'paginateAssessmentGradingStudentList'])->name('lms.assessmentGradingStudentList.paginate');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/assessment-grading/{assessmentId}/mode/{mode}/student-list/{studentId}/scoring/paginate', [TeacherAssessmentGradingController::class, 'paginateAssessmentGradingStudentAnswer'])->name('lms.assessmentGradingStudentAnswer.paginate');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/assessment-grading/{assessmentId}/student-list/{studentId}/scoring/project', [TeacherAssessmentGradingController::class, 'paginateAssessmentGradingStudentProject'])->name('lms.assessmentGradingStudentProject');

    // TEACHER GRADEBOOK MANAGEMENT
    // views
    Route::get('/lms/{role}/{schoolName}/{schoolId}/gradebook/classes', [TeacherGradebookController::class, 'teacherClassList'])->name('lms.teacherClassList.view');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/gradebook/classes/subject-teacher/{subjectTeacherId}', [TeacherGradebookController::class, 'gradebookManagement'])->name('lms.teacherGradebook.view');

    // paginate
    Route::get('/lms/{role}/{schoolName}/{schoolId}/gradebook/classes/paginate', [TeacherGradebookController::class, 'paginateTeacherClassList'])->name('lms.teacherClassList.paginate');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/gradebook/classes/subject-teacher/{subjectTeacherId}/paginate', [TeacherGradebookController::class, 'paginateGradebookManagement'])->name('lms.teacherGradebook.paginate');

    // gradebook export
    Route::get('/lms/{role}/{schoolName}/{schoolId}/gradebook/classes/subject-teacher/{subjectTeacherId}/semester/{semester}/export', [TeacherGradebookController::class, 'exportGradebook']);

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

    // Information
    // Calender
    Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-academic-calendar', [TeacherInformationController::class, 'teacherCalendarView'])->name('lms.teacherCalendar.view');
    Route::post('/lms/{role}/{schoolName}/{schoolId}/teacher-academic-calendar/save', [TeacherInformationController::class, 'saveCalendarData'])->name('lms.teacherCalendar.save');

    // Jadwal
    Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-schedule', [TeacherInformationController::class, 'scheduleView'])->name('lms.teacherSchedule.view');
    Route::post('/lms/{role}/{schoolName}/{schoolId}/teacher-schedule/save', [TeacherInformationController::class, 'saveSchedule'])->name('lms.teacherSchedule.save');
    Route::get('/lms/{schoolId}/teacher-schedule/get-data/{className}', [TeacherInformationController::class, 'getScheduleData'])->name('lms.teacherSchedule.get');

    // Polling
    Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-polling', [TeacherInformationController::class, 'teacherPollingView'])->name('lms.teacherPolling.view');
    Route::post('/lms/{role}/{schoolName}/{schoolId}/teacher-polling/save', [TeacherInformationController::class, 'savePollingData'])->name('lms.teacherPolling.save');

    // Rute detail kelas
    Route::get('/lms/guru/api/get-students/{classId}', [LmsController::class, 'getStudentsForAttendance'])->name('lms.guru.get_students');
    Route::post('/lms/teacher/pengumuman/store', [LmsController::class, 'storePengumuman'])->name('lms.teacher.pengumuman.store');
    Route::post('/lms/student/announcement/mark-as-read', [LmsController::class, 'markAsRead'])->name('lms.student.announcement.read');
    Route::get('/lms/guru/sekolah/{schoolName}/{schoolId}/kelas/{scheduleId}', [LmsController::class, 'classDetailView'])->name('lms.teacher.class.detail');
    Route::post('/lms/teacher/attendance/store', [LmsController::class, 'saveAttendance'])->name('lms.teacher.attendance.store');
    Route::get('/lms/teacher/tugas/{taskId}/submissions', [LmsController::class, 'getTaskSubmissions'])->name('lms.teacher.tugas.submissions');
    Route::post('/lms/teacher/tugas/grades', [LmsController::class, 'saveTaskGrades'])->name('lms.teacher.tugas.grades');
    Route::post('/lms/teacher/tugas/store', [LmsController::class, 'storeTugas'])->name('lms.teacher.tugas.store');

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
    Route::get('/lms/{role}/{schoolName}/{schoolId}/beranda', [SchoolAdminDashboardController::class, 'index'])->name('lms.schoolAdmin.dashboard.view');

    // edit school logo
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/edit-school-logo', [LmsController::class, 'editSchoolLogo'])->name('lms.editLogo');

    // ROUTES SCHOOL PARTNER
    Route::post('/school-subcsription/store', [SchoolPartnerController::class, 'bulkUploadSchoolPartner'])->name('bulkUploadSchoolPartner.store');
    Route::post('/school-subscription/add-users/store', [SchoolPartnerController::class, 'bulkUploadAddUsers'])->name('bulkUploadAddUsers.store');
});