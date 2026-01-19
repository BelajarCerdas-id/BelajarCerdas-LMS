<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LmsController;
use App\Http\Controllers\MasterAcademicController;
use App\Http\Controllers\SchoolPartnerController;
use App\Http\Controllers\SyllabusController;
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

// ROUTES DROPDOWN KELAS, DLL
Route::get('/kelas/{id}', [MasterAcademicController::class, 'getKelas']); // kelas by fase

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

    // ROUTES LMS FEATURE
    // views (administrator)
    Route::get('/lms/school-subscription', [LmsController::class, 'lmsSchoolSubscriptionView'])->name('lms.schoolSubscription.view');

    // route management role account
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/management-role-account', [LmsController::class, 'lmsManagementRolesView'])->name('lms.managementRoles.view');

    // route management account
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/management-role-account/{role}/management-accounts', [LmsController::class, 'lmsManagementAccountView'])->name('lms.managementAccount.view');

    // route management majors
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/management-role-account/{role}/management-majors', [LmsController::class, 'lmsManagementMajorsView'])->name('lms.managementMajors.view');

    // routes views class by major and no major
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/management-role-account/{role}/management-majors/{majorId}/management-class', [LmsController::class, 'lmsManagementClassView'])->name('lms.managementClass.view.major');
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/management-role-account/{role}/management-class', [LmsController::class, 'lmsManagementClassView'])->name('lms.managementClass.view.noMajor');

    // routes views students in class by major and no major
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/management-role-account/{role}/management-class/{classId}/management-majors/{majorId}/management-students', [LmsController::class, 'lmsManagementStudentsView'])->name('lms.managementStudents.view.major');
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/management-role-account/{role}/management-class/{classId}/management-students', [LmsController::class, 'lmsManagementStudentsView'])->name('lms.managementStudents.view.noMajor');

    // CRUD
    // routes crud majors
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/management-role-account/{role}/management-majors/create', [LmsController::class, 'lmsManagementCreateMajor'])->name('lms.managementCreateMajor.store');
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/management-role-account/{role}/management-majors/{majorId}/edit', [LmsController::class, 'lmsManagementEditMajor'])->name('lms.managementEditMajor.store');

    // routes create management class by major and no major
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/management-role-account/{role}/management-majors/{majorId}/management-class/create', [LmsController::class, 'lmsManagementCreateClass'])->name('lms.managementCreateClass.store.major');
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/management-role-account/{role}/management-class/create', [LmsController::class, 'lmsManagementCreateClass'])->name('lms.managementCreateClass.store.noMajor');

    // routes edit management class by major and no major
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/management-role-account/{role}/management-class/{classId}/management-majors/{majorId}/edit', [LmsController::class, 'lmsManagementEditClass'])->name('lms.managementClassWithMajor.edit');
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/management-role-account/{role}/management-class/{classId}/edit', [LmsController::class, 'lmsManagementEditClass'])->name('lms.managementClassNoMajor.edit');

    // routes activate school subscription, account, major, class, student in class
    Route::put('/lms/school-subscription/{subscriptionId}/activate', [LmsController::class, 'lmsSchoolSubscriptionActivate'])->name('lms.schoolSubscription.activate');
    Route::put('/lms/school-subscription/{schoolId}/management-account/{id}/activate-account', [LmsController::class, 'lmsActivateAccount'])->name('lms.account.activate');
    Route::put('/lms/school-subscription/management-class/{id}/activate-major', [LmsController::class, 'lmsActivateMajor'])->name('lms.major.activate');
    Route::put('/lms/school-subscription/management-class/{id}/activate-class', [LmsController::class, 'lmsActivateClass'])->name('lms.class.activate');
    Route::put('/lms/school-subscription/management-class/{id}/activate-student-in-class', [LmsController::class, 'lmsActivateStudentInClass'])->name('lms.studentInClass.activate');

    // routes promote class, repeat class, move class, move major
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/management-role-account/{role}/management-class/{classId}/promote-class', [LmsController::class, 'lmsManagementPromoteClass'])->name('lms.managementPromoteClass.create');
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/management-role-account/{role}/management-class/{classId}/repeat-class', [LmsController::class, 'lmsManagementRepeatClass'])->name('lms.managementRepeatClass.create');
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/management-role-account/{role}/management-class/{classId}/move-class', [LmsController::class, 'lmsManagementMoveClass'])->name('lms.managementMoveClass.create');
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/management-role-account/{role}/management-class/{classId}/move-major', [LmsController::class, 'lmsManagementMoveMajor'])->name('lms.managementMoveMajor.create');

    // get promotion next class / repeat class / move class / move major options
    // promote-to-next-class routes by major and no major
    Route::get('/lms/school/{schoolId}/{majorId}/promotion-to-next-class-options',[LmsController::class, 'promotionClassOptions']);
    Route::get('/lms/school/{schoolId}/promotion-to-next-class-options',[LmsController::class, 'promotionClassOptions']);

    // promote-to-repeat-class routes by major and no major
    Route::get('/lms/school/{schoolId}/{majorId}/repeat-class-options',[LmsController::class, 'repeatClassOptions']);
    Route::get('/lms/school/{schoolId}/repeat-class-options',[LmsController::class, 'repeatClassOptions']);

    // promote-to-move-class routes by major and no major
    Route::get('/lms/school/{schoolId}/{majorId}/move-class-options',[LmsController::class, 'moveClassOptions']);
    Route::get('/lms/school/{schoolId}/move-class-options',[LmsController::class, 'moveClassOptions']);

    // move major routes by major and no major
    Route::get('/lms/school/{schoolId}/{majorId}/move-major-options',[LmsController::class, 'moveMajorOptions']);
    Route::get('/lms/school/{schoolId}/move-major-options',[LmsController::class, 'moveMajorOptions']);

    // paginate
    Route::get('/lms/school-subscription/paginate', [LmsController::class, 'paginateLmsSchoolSubscription'])->name('lms.schoolSubscription.paginate');
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/role-account/paginate', [LmsController::class, 'paginateLmsSchoolSubscriptionRoleAccount'])->name('lms.SchoolSubscriptionRoleAccount.paginate');
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/management-role-account/{role}/management-accounts/paginate', [LmsController::class, 'paginateLmsSchoolAccount'])->name('lms.SchoolSubscriptionAccount.paginate');
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/role-account/{role}/management-majors/paginate', [LmsController::class, 'paginateLmsSchoolSubscriptionMajors'])->name('lms.SchoolSubscriptionMajors.paginate');

    // paginate class by major and no major
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/role-account/{role}/management-majors/{majorId}/management-class/paginate', [LmsController::class, 'paginateLmsSchoolSubscriptionClass'])->name('lms.SchoolSubscriptionClass.paginate.major');
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/role-account/{role}/management-class/paginate', [LmsController::class, 'paginateLmsSchoolSubscriptionClass'])->name('lms.SchoolSubscriptionClass.paginate.noMajor');

    // paginate users by major and no major
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/role-account/{role}/management-class/{classId}/management-majors/{majorId}/management-students/paginate', [LmsController::class, 'paginateLmsSchoolSubscriptionUsers'])->name('lms.SchoolSubscriptionUsers.paginate.major');
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/role-account/{role}/management-class/{classId}/management-students/paginate', [LmsController::class, 'paginateLmsSchoolSubscriptionUsers'])->name('lms.SchoolSubscriptionUsers.paginate.noMajor');
});

// ROUTES SCHOOL PARTNER
Route::post('/school-subcsription/store', [SchoolPartnerController::class, 'bulkUploadSchoolPartner'])->name('bulkUploadSchoolPartner.store');
Route::post('/school-subscription/add-users/store', [SchoolPartnerController::class, 'bulkUploadAddUsers'])->name('bulkUploadAddUsers.store');