<?php

use App\Http\Controllers\Lms\Academic\AcademicDashboardController;
use App\Http\Controllers\Lms\Academic\ClassController;
use App\Http\Controllers\Lms\Academic\MajorController;
use App\Http\Controllers\Lms\Academic\StudentSchoolClassController;
use App\Http\Controllers\Lms\UserManagement\AccountController;
use App\Http\Controllers\Lms\UserManagement\RoleController;
use App\Http\Controllers\LmsController;
use App\Http\Controllers\SchoolSyllabusController;
use App\Http\Controllers\SyllabusController;
use Illuminate\Support\Facades\Route;

// PAGINATE SYLLABUS-SERVICES
Route::get('/paginate-syllabus-service-kurikulum', [SyllabusController::class, 'paginateSyllabusCuriculum'])->name('Syllabus.Kurikulum');
Route::get('/paginate-syllabus-service-fase/{curriculumName}/{curriculumId}', [SyllabusController::class, 'paginateSyllabusFase'])->name('Syllabus.Fase');
Route::get('/paginate-syllabus-service-kelas/{curriculumName}/{curriculumId}/{faseId}', [SyllabusController::class, 'paginateSyllabusKelas'])->name('Syllabus.Kelas');
Route::get('/paginate-syllabus-service-mapel/{curriculumName}/{curriculumId}/{faseId}/{kelasId}', [SyllabusController::class, 'paginateSyllabusMapel'])->name('Syllabus.Mapel');
Route::get('/paginate-syllabus-service-bab/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/{mapelId}', [SyllabusController::class, 'paginateSyllabusBab'])->name('Syllabus.Bab');
Route::get('/paginate-syllabus-service-sub-bab/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/{mapelId}/{babId}', [SyllabusController::class, 'paginateSyllabusSubBab'])->name('Syllabus.SubBab');

// BULKUPLOAD SYLLABUS
// validate
Route::post('/syllabus/bulkupload/syllabus/validate', [SyllabusController::class, 'bulkUploadSyllabusValidate'])->name('Syllabus.Bulkupload.Validate');

// store
Route::post('/syllabus/bulkupload/syllabus', [SyllabusController::class, 'bulkUploadSyllabus'])->name('Syllabus.Bulkupload');

// =========================================================
// ROUTES SCHOOL CURRICULUM MANAGEMENT HIERARCHY
// views
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/kurikulum', [SchoolSyllabusController::class, 'curriculumView'])->name('SchoolCurriculumManagement.View');
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/{curriculumName}/{curriculumId}/fase', [SchoolSyllabusController::class, 'faseView'])->name('SchoolFaseManagement.View');
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/{curriculumName}/{curriculumId}/{faseId}/kelas', [SchoolSyllabusController::class, 'kelasView'])->name('SchoolKelasManagement.View');
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/mapel', [SchoolSyllabusController::class, 'mapelView'])->name('SchoolMapelManagement.View');
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/{mapelId}/bab', [SchoolSyllabusController::class, 'babView'])->name('SchoolBabManagement.View');
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/{mapelId}/{babId}/sub-bab', [SchoolSyllabusController::class, 'subBabView'])->name('SchoolSubBabManagement.View');

// crud mapel
Route::post('/lms/school-subscription/{schoolName}/{schoolId}/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/mapel/store', [SchoolSyllabusController::class, 'mapelStore'])->name('SchoolMapelManagement.Store');
Route::post('/lms/school-subscription/{schoolName}/{schoolId}/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/mapel/{mapelId}/edit', [SchoolSyllabusController::class, 'mapelEdit'])->name('SchoolMapelManagement.Edit');
Route::put('/lms/school-subscription/{schoolName}/{schoolId}/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/mapel/{mapelId}/activate', [SchoolSyllabusController::class, 'mapelActivate'])->name('SchoolMapelManagement.Activate');

// crud bab
Route::post('/lms/school-subscription/{schoolName}/{schoolId}/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/{mapelId}/bab/store', [SchoolSyllabusController::class, 'babStore'])->name('SchoolBabManagement.Store');
Route::post('/lms/school-subscription/{schoolName}/{schoolId}/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/{mapelId}/bab/{babId}/edit', [SchoolSyllabusController::class, 'babEdit'])->name('SchoolBabManagement.Edit');
Route::put('/lms/school-subscription/{schoolName}/{schoolId}/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/{mapelId}/bab/{babId}/activate', [SchoolSyllabusController::class, 'babActivate'])->name('SchoolBabManagement.Activate');

// crud sub bab
Route::post('/lms/school-subscription/{schoolName}/{schoolId}/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/{mapelId}/{babId}/sub-bab/store', [SchoolSyllabusController::class, 'subBabStore'])->name('SchoolSubBabManagement.Store');
Route::post('/lms/school-subscription/{schoolName}/{schoolId}/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/{mapelId}/{babId}/sub-bab/{subBabId}/edit', [SchoolSyllabusController::class, 'subBabEdit'])->name('SchoolSubBabManagement.Edit');
Route::put('/lms/school-subscription/{schoolName}/{schoolId}/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/{mapelId}/{babId}/sub-bab/{subBabId}/activate', [SchoolSyllabusController::class, 'subBabActivate'])->name('SchoolSubBabManagement.Activate');

    // paginate
Route::get('/lms/school-subscription/{schoolName}/{schoolId}/kurikulum/paginate', [SchoolSyllabusController::class, 'paginateCurriculum'])->name('SchoolCurriculumManagement.Paginate');
Route::get('/lms/school-subscription/{schoolName}/{schoolId}/{curriculumName}/{curriculumId}/fase/paginate', [SchoolSyllabusController::class, 'paginateFase'])->name('SchoolFaseManagement.Paginate');
Route::get('/lms/school-subscription/{schoolName}/{schoolId}/{curriculumName}/{curriculumId}/{faseId}/kelas/paginate', [SchoolSyllabusController::class, 'paginateKelas'])->name('SchoolKelasManagement.Paginate');
Route::get('/lms/school-subscription/{schoolName}/{schoolId}/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/mapel/paginate', [SchoolSyllabusController::class, 'paginateMapel'])->name('SchoolMapelManagement.Paginate');
Route::get('/lms/school-subscription/{schoolName}/{schoolId}/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/{mapelId}/bab/paginate', [SchoolSyllabusController::class, 'paginateBab'])->name('SchoolBabManagement.Paginate');
Route::get('/lms/school-subscription/{schoolName}/{schoolId}/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/{mapelId}/{babId}/sub-bab/paginate', [SchoolSyllabusController::class, 'paginateSubBab'])->name('SchoolSubBabManagement.Paginate');

// bulkupload
// validate
Route::post('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/{curriculumName}/{curriculumId}/{faseId}/bulkupload-silabus/validate', [SchoolSyllabusController::class, 'bulkUploadSchoolSyllabusValidate'])->name('SchoolKelasManagement.Bulkupload.Validate');

// store
Route::post('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/{curriculumName}/{curriculumId}/{faseId}/bulkupload-silabus', [SchoolSyllabusController::class, 'bulkUploadSchoolSyllabus'])->name('SchoolKelasManagement.Bulkupload');

// =========================================================
// ROUTES LMS FEATURE (administrator)
// =========================================================
Route::get('/lms/{role}/school-subscription', [LmsController::class, 'lmsSchoolSubscriptionView'])->name('Lms.SchoolSubscription.View');

// routes academic management
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management', [AcademicDashboardController::class, 'lmsAcademicManagementView'])->name('Lms.AcademicManagement.View');
Route::get('/lms/school-subscription/{schoolName}/{schoolId}/academic-management/paginate', [AcademicDashboardController::class, 'paginateLmsAcademicManagement'])->name('Lms.AcademicManagement.Paginate');

// route management role account
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/management-role-account', [RoleController::class, 'lmsManagementRolesView'])->name('Lms.ManagementRoles.View');

// route management account
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/management-role-account/{managedRole}/management-accounts', [AccountController::class, 'lmsManagementAccountView'])->name('Lms.ManagementAccount.View');

// route parent children list management
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/management-role-account/{managedRole}/management-accounts/{parentId}/parent-children-list', [AccountController::class, 'lmsParentChildrenListView'])->name('Lms.ParentChildrenList.View');

// route management majors
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/management-role-account/{managedRole}/management-majors', [MajorController::class, 'lmsManagementMajorsView'])->name('Lms.ManagementMajors.View');

// routes views class by major and no major
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/management-role-account/{managedRole}/management-majors/{majorId}/management-class', [ClassController::class, 'lmsManagementClassView'])->name('Lms.ManagementClass.View.Major');
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/management-role-account/{managedRole}/management-class', [ClassController::class, 'lmsManagementClassView'])->name('Lms.ManagementClass.View.NoMajor');

// routes views students in class by major and no major
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/management-role-account/{managedRole}/management-class/{classId}/management-majors/{majorId}/management-students', [StudentSchoolClassController::class, 'lmsManagementStudentsView'])->name('Lms.ManagementStudents.View.Major');
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/management-role-account/{managedRole}/management-class/{classId}/management-students', [StudentSchoolClassController::class, 'lmsManagementStudentsView'])->name('Lms.ManagementStudents.View.NoMajor');

// CRUD
// routes crud majors
Route::post('/lms/{role}/school-subscription/{schoolName}/{schoolId}/management-role-account/{managedRole}/management-majors/create', [MajorController::class, 'lmsManagementCreateMajor'])->name('Lms.ManagementCreateMajor.Store');
Route::post('/lms/{role}/school-subscription/{schoolName}/{schoolId}/management-role-account/{managedRole}/management-majors/{majorId}/edit', [MajorController::class, 'lmsManagementEditMajor'])->name('Lms.ManagementEditMajor.Store');

// routes create management class by major and no major
Route::post('/lms/{role}/school-subscription/{schoolName}/{schoolId}/management-role-account/{managedRole}/management-majors/{majorId}/management-class/create', [ClassController::class, 'lmsManagementCreateClass'])->name('Lms.ManagementCreateClass.Store.Major');
Route::post('/lms/{role}/school-subscription/{schoolName}/{schoolId}/management-role-account/{managedRole}/management-class/create', [ClassController::class, 'lmsManagementCreateClass'])->name('Lms.ManagementCreateClass.Store.NoMajor');

// routes edit management class by major and no major
Route::post('/lms/{role}/school-subscription/{schoolName}/{schoolId}/management-role-account/{managedRole}/management-class/{classId}/management-majors/{majorId}/edit', [ClassController::class, 'lmsManagementEditClass'])->name('Lms.ManagementClassWithMajor.Edit');
Route::post('/lms/{role}/school-subscription/{schoolName}/{schoolId}/management-role-account/{managedRole}/management-class/{classId}/edit', [ClassController::class, 'lmsManagementEditClass'])->name('Lms.ManagementClassNoMajor.Edit');

// activate
Route::put('/lms/school-subscription/{subscriptionId}/activate', [LmsController::class, 'lmsSchoolSubscriptionActivate'])->name('Lms.SchoolSubscription.Activate');
Route::put('/lms/school-subscription/{schoolId}/management-account/{id}/activate-account', [AccountController::class, 'lmsActivateAccount'])->name('Lms.Account.Activate');
Route::put('/lms/school-subscription/management-class/{id}/activate-major', [MajorController::class, 'lmsActivateMajor'])->name('Lms.Major.Activate');
Route::put('/lms/school-subscription/management-class/{id}/activate-class', [ClassController::class, 'lmsActivateClass'])->name('Lms.Class.Activate');
Route::put('/lms/school-subscription/management-class/{id}/activate-student-in-class', [StudentSchoolClassController::class, 'lmsActivateStudentInClass'])->name('Lms.StudentInClass.Activate');

// routes load subject conversion
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/management-role-account/{managedRole}/management-class/{classId}/management-students/load-subject-conversion', [StudentSchoolClassController::class, 'loadSubjectConversion'])->name('Lms.ManagementStudents.LoadSubjectConversion');

// routes promote class, repeat class, move class, move major
Route::post('/lms/school-subscription/{schoolName}/{schoolId}/management-role-account/{role}/management-class/{classId}/promote-class', [StudentSchoolClassController::class, 'lmsManagementPromoteClass'])->name('Lms.ManagementPromoteClass.Create');
Route::post('/lms/school-subscription/{schoolName}/{schoolId}/management-role-account/{role}/management-class/{classId}/repeat-class', [StudentSchoolClassController::class, 'lmsManagementRepeatClass'])->name('Lms.ManagementRepeatClass.Create');
Route::post('/lms/school-subscription/{schoolName}/{schoolId}/management-role-account/{role}/management-class/{classId}/move-class', [StudentSchoolClassController::class, 'lmsManagementMoveClass'])->name('Lms.ManagementMoveClass.Create');
Route::post('/lms/school-subscription/{schoolName}/{schoolId}/management-role-account/{role}/management-class/{classId}/move-major', [StudentSchoolClassController::class, 'lmsManagementMoveMajor'])->name('Lms.ManagementMoveMajor.Create');

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
Route::get('/lms/school-subscription/paginate', [LmsController::class, 'paginateLmsSchoolSubscription'])->name('Lms.SchoolSubscription.Paginate');
Route::get('/lms/school-subscription/{schoolName}/{schoolId}/role-account/paginate', [RoleController::class, 'paginateLmsSchoolSubscriptionRoleAccount'])->name('Lms.SchoolSubscriptionRoleAccount.Paginate');
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/management-role-account/{managedRole}/management-accounts/paginate', [AccountController::class, 'paginateLmsSchoolAccount'])->name('Lms.SchoolSubscriptionAccount.Paginate');
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/management-role-account/{managedRole}/management-accounts/{parentId}/parent-children-list/paginate', [AccountController::class, 'paginateLmsParentChildrenList'])->name('Lms.SchoolSubscriptionParentChildrenList.Paginate');
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/role-account/{managedRole}/management-majors/paginate', [MajorController::class, 'paginateLmsSchoolSubscriptionMajors'])->name('Lms.SchoolSubscriptionMajors.Paginate');

// paginate class by major and no major
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/role-account/{managedRole}/management-majors/{majorId}/management-class/paginate', [ClassController::class, 'paginateLmsSchoolSubscriptionClass'])->name('Lms.SchoolSubscriptionClass.Paginate.Major');
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/role-account/{managedRole}/management-class/paginate', [ClassController::class, 'paginateLmsSchoolSubscriptionClass'])->name('Lms.SchoolSubscriptionClass.Paginate.NoMajor');

// paginate users by major and no major
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/role-account/{managedRole}/management-class/{classId}/management-majors/{majorId}/management-students/paginate', [StudentSchoolClassController::class, 'paginateLmsSchoolSubscriptionUsers'])->name('Lms.SchoolSubscriptionUsers.Paginate.Major');
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/role-account/{managedRole}/management-class/{classId}/management-students/paginate', [StudentSchoolClassController::class, 'paginateLmsSchoolSubscriptionUsers'])->name('Lms.SchoolSubscriptionUsers.Paginate.NoMajor');
