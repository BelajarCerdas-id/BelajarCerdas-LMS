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
Route::get('/paginate-syllabus-service-kurikulum', [SyllabusController::class, 'paginateSyllabusCuriculum'])->name('syllabus.kurikulum');
Route::get('/paginate-syllabus-service-fase/{curriculumName}/{curriculumId}', [SyllabusController::class, 'paginateSyllabusFase'])->name('syllabus.fase');
Route::get('/paginate-syllabus-service-kelas/{curriculumName}/{curriculumId}/{faseId}', [SyllabusController::class, 'paginateSyllabusKelas'])->name('syllabus.kelas');
Route::get('/paginate-syllabus-service-mapel/{curriculumName}/{curriculumId}/{faseId}/{kelasId}', [SyllabusController::class, 'paginateSyllabusMapel'])->name('syllabus.mapel');
Route::get('/paginate-syllabus-service-bab/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/{mapelId}', [SyllabusController::class, 'paginateSyllabusBab'])->name('syllabus.bab');
Route::get('/paginate-syllabus-service-sub-bab/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/{mapelId}/{babId}', [SyllabusController::class, 'paginateSyllabusSubBab'])->name('syllabus.sub_bab');

// BULKUPLOAD SYLLABUS
// validate
Route::post('/syllabus/bulkupload/syllabus/validate', [SyllabusController::class, 'bulkUploadSyllabusValidate'])->name('syllabus.bulkupload.validate');

// store
Route::post('/syllabus/bulkupload/syllabus', [SyllabusController::class, 'bulkUploadSyllabus'])->name('syllabus.bulkupload');

// =========================================================
// ROUTES SCHOOL CURRICULUM MANAGEMENT HIERARCHY
// views
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/kurikulum', [SchoolSyllabusController::class, 'curriculumView'])->name('school_curriculum_management.view');
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/{curriculumName}/{curriculumId}/fase', [SchoolSyllabusController::class, 'faseView'])->name('school_fase_management.view');
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/{curriculumName}/{curriculumId}/{faseId}/kelas', [SchoolSyllabusController::class, 'kelasView'])->name('school_kelas_management.view');
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/mapel', [SchoolSyllabusController::class, 'mapelView'])->name('school_mapel_management.view');
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/{mapelId}/bab', [SchoolSyllabusController::class, 'babView'])->name('school_bab_management.view');
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/{mapelId}/{babId}/sub-bab', [SchoolSyllabusController::class, 'subBabView'])->name('school_sub_bab_management.view');

// crud mapel
Route::post('/lms/school-subscription/{schoolName}/{schoolId}/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/mapel/store', [SchoolSyllabusController::class, 'mapelStore'])->name('school_mapel_management.store');
Route::post('/lms/school-subscription/{schoolName}/{schoolId}/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/mapel/{mapelId}/edit', [SchoolSyllabusController::class, 'mapelEdit'])->name('school_mapel_management.edit');
Route::put('/lms/school-subscription/{schoolName}/{schoolId}/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/mapel/{mapelId}/activate', [SchoolSyllabusController::class, 'mapelActivate'])->name('school_mapel_management.activate');

// crud bab
Route::post('/lms/school-subscription/{schoolName}/{schoolId}/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/{mapelId}/bab/store', [SchoolSyllabusController::class, 'babStore'])->name('school_bab_management.store');
Route::post('/lms/school-subscription/{schoolName}/{schoolId}/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/{mapelId}/bab/{babId}/edit', [SchoolSyllabusController::class, 'babEdit'])->name('school_bab_management.edit');
Route::put('/lms/school-subscription/{schoolName}/{schoolId}/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/{mapelId}/bab/{babId}/activate', [SchoolSyllabusController::class, 'babActivate'])->name('school_bab_management.activate');

// crud sub bab
Route::post('/lms/school-subscription/{schoolName}/{schoolId}/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/{mapelId}/{babId}/sub-bab/store', [SchoolSyllabusController::class, 'subBabStore'])->name('school_sub_bab_management.store');
Route::post('/lms/school-subscription/{schoolName}/{schoolId}/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/{mapelId}/{babId}/sub-bab/{subBabId}/edit', [SchoolSyllabusController::class, 'subBabEdit'])->name('school_sub_bab_management.edit');
Route::put('/lms/school-subscription/{schoolName}/{schoolId}/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/{mapelId}/{babId}/sub-bab/{subBabId}/activate', [SchoolSyllabusController::class, 'subBabActivate'])->name('school_sub_bab_management.activate');

    // paginate
Route::get('/lms/school-subscription/{schoolName}/{schoolId}/kurikulum/paginate', [SchoolSyllabusController::class, 'paginateCurriculum'])->name('school_curriculum_management.paginate');
Route::get('/lms/school-subscription/{schoolName}/{schoolId}/{curriculumName}/{curriculumId}/fase/paginate', [SchoolSyllabusController::class, 'paginateFase'])->name('school_fase_management.paginate');
Route::get('/lms/school-subscription/{schoolName}/{schoolId}/{curriculumName}/{curriculumId}/{faseId}/kelas/paginate', [SchoolSyllabusController::class, 'paginateKelas'])->name('school_kelas_management.paginate');
Route::get('/lms/school-subscription/{schoolName}/{schoolId}/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/mapel/paginate', [SchoolSyllabusController::class, 'paginateMapel'])->name('school_mapel_management.paginate');
Route::get('/lms/school-subscription/{schoolName}/{schoolId}/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/{mapelId}/bab/paginate', [SchoolSyllabusController::class, 'paginateBab'])->name('school_bab_management.paginate');
Route::get('/lms/school-subscription/{schoolName}/{schoolId}/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/{mapelId}/{babId}/sub-bab/paginate', [SchoolSyllabusController::class, 'paginateSubBab'])->name('school_sub_bab_management.paginate');

// bulkupload
// validate
Route::post('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/{curriculumName}/{curriculumId}/{faseId}/bulkupload-silabus/validate', [SchoolSyllabusController::class, 'bulkUploadSchoolSyllabusValidate'])->name('school_kelas_management.bulkupload.validate');

// store
Route::post('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/{curriculumName}/{curriculumId}/{faseId}/bulkupload-silabus', [SchoolSyllabusController::class, 'bulkUploadSchoolSyllabus'])->name('school_kelas_management.bulkupload');

// =========================================================
// ROUTES LMS FEATURE (administrator)
// =========================================================
Route::get('/lms/{role}/school-subscription', [LmsController::class, 'lmsSchoolSubscriptionView'])->name('lms.school_subscription.view');

// routes academic management
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management', [AcademicDashboardController::class, 'lmsAcademicManagementView'])->name('lms.academic_management.view');
Route::get('/lms/school-subscription/{schoolName}/{schoolId}/academic-management/paginate', [AcademicDashboardController::class, 'paginateLmsAcademicManagement'])->name('lms.academic_management.paginate');

// route management role account
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/management-role-account', [RoleController::class, 'lmsManagementRolesView'])->name('lms.management_roles.view');

// route management account
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/management-role-account/{managedRole}/management-accounts', [AccountController::class, 'lmsManagementAccountView'])->name('lms.management_account.view');

// route parent children list management
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/management-role-account/{managedRole}/management-accounts/{parentId}/parent-children-list', [AccountController::class, 'lmsParentChildrenListView'])->name('lms.parent_children_list.view');

// route management majors
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/management-role-account/{managedRole}/management-majors', [MajorController::class, 'lmsManagementMajorsView'])->name('lms.management_majors.view');

// routes views class by major and no major
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/management-role-account/{managedRole}/management-majors/{majorId}/management-class', [ClassController::class, 'lmsManagementClassView'])->name('lms.management_class.view.major');
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/management-role-account/{managedRole}/management-class', [ClassController::class, 'lmsManagementClassView'])->name('lms.management_class.view.no_major');

// routes views students in class by major and no major
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/management-role-account/{managedRole}/management-class/{classId}/management-majors/{majorId}/management-students', [StudentSchoolClassController::class, 'lmsManagementStudentsView'])->name('lms.management_students.view.major');
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/management-role-account/{managedRole}/management-class/{classId}/management-students', [StudentSchoolClassController::class, 'lmsManagementStudentsView'])->name('lms.management_students.view.no_major');

// CRUD
// routes crud majors
Route::post('/lms/{role}/school-subscription/{schoolName}/{schoolId}/management-role-account/{managedRole}/management-majors/create', [MajorController::class, 'lmsManagementCreateMajor'])->name('lms.management_create_major.store');
Route::post('/lms/{role}/school-subscription/{schoolName}/{schoolId}/management-role-account/{managedRole}/management-majors/{majorId}/edit', [MajorController::class, 'lmsManagementEditMajor'])->name('lms.management_edit_major.store');

// routes create management class by major and no major
Route::post('/lms/{role}/school-subscription/{schoolName}/{schoolId}/management-role-account/{managedRole}/management-majors/{majorId}/management-class/create', [ClassController::class, 'lmsManagementCreateClass'])->name('lms.management_create_class.store.major');
Route::post('/lms/{role}/school-subscription/{schoolName}/{schoolId}/management-role-account/{managedRole}/management-class/create', [ClassController::class, 'lmsManagementCreateClass'])->name('lms.management_create_class.store.no_major');

// routes edit management class by major and no major
Route::post('/lms/{role}/school-subscription/{schoolName}/{schoolId}/management-role-account/{managedRole}/management-class/{classId}/management-majors/{majorId}/edit', [ClassController::class, 'lmsManagementEditClass'])->name('lms.management_class_with_major.edit');
Route::post('/lms/{role}/school-subscription/{schoolName}/{schoolId}/management-role-account/{managedRole}/management-class/{classId}/edit', [ClassController::class, 'lmsManagementEditClass'])->name('lms.management_class_no_major.edit');

// activate
Route::put('/lms/school-subscription/{subscriptionId}/activate', [LmsController::class, 'lmsSchoolSubscriptionActivate'])->name('lms.school_subscription.activate');
Route::put('/lms/school-subscription/{schoolId}/management-account/{id}/activate-account', [AccountController::class, 'lmsActivateAccount'])->name('lms.account.activate');
Route::put('/lms/school-subscription/management-class/{id}/activate-major', [MajorController::class, 'lmsActivateMajor'])->name('lms.major.activate');
Route::put('/lms/school-subscription/management-class/{id}/activate-class', [ClassController::class, 'lmsActivateClass'])->name('lms.class.activate');
Route::put('/lms/school-subscription/management-class/{id}/activate-student-in-class', [StudentSchoolClassController::class, 'lmsActivateStudentInClass'])->name('lms.student_in_class.activate');

// routes load subject conversion
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/management-role-account/{managedRole}/management-class/{classId}/management-students/load-subject-conversion', [StudentSchoolClassController::class, 'loadSubjectConversion'])->name('lms.management_students.load_subject_conversion');

// routes promote class, repeat class, move class, move major
Route::post('/lms/school-subscription/{schoolName}/{schoolId}/management-role-account/{role}/management-class/{classId}/promote-class', [StudentSchoolClassController::class, 'lmsManagementPromoteClass'])->name('lms.management_promote_class.create');
Route::post('/lms/school-subscription/{schoolName}/{schoolId}/management-role-account/{role}/management-class/{classId}/repeat-class', [StudentSchoolClassController::class, 'lmsManagementRepeatClass'])->name('lms.management_repeat_class.create');
Route::post('/lms/school-subscription/{schoolName}/{schoolId}/management-role-account/{role}/management-class/{classId}/move-class', [StudentSchoolClassController::class, 'lmsManagementMoveClass'])->name('lms.management_move_class.create');
Route::post('/lms/school-subscription/{schoolName}/{schoolId}/management-role-account/{role}/management-class/{classId}/move-major', [StudentSchoolClassController::class, 'lmsManagementMoveMajor'])->name('lms.management_move_major.create');

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
Route::get('/lms/school-subscription/paginate', [LmsController::class, 'paginateLmsSchoolSubscription'])->name('lms.school_subscription.paginate');
Route::get('/lms/school-subscription/{schoolName}/{schoolId}/role-account/paginate', [RoleController::class, 'paginateLmsSchoolSubscriptionRoleAccount'])->name('lms.school_subscription_role_account.paginate');
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/management-role-account/{managedRole}/management-accounts/paginate', [AccountController::class, 'paginateLmsSchoolAccount'])->name('lms.school_subscription_account.paginate');
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/management-role-account/{managedRole}/management-accounts/{parentId}/parent-children-list/paginate', [AccountController::class, 'paginateLmsParentChildrenList'])->name('lms.school_subscription_parent_children_list.paginate');
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/role-account/{managedRole}/management-majors/paginate', [MajorController::class, 'paginateLmsSchoolSubscriptionMajors'])->name('lms.school_subscription_majors.paginate');

// paginate class by major and no major
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/role-account/{managedRole}/management-majors/{majorId}/management-class/paginate', [ClassController::class, 'paginateLmsSchoolSubscriptionClass'])->name('lms.school_subscription_class.paginate.major');
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/role-account/{managedRole}/management-class/paginate', [ClassController::class, 'paginateLmsSchoolSubscriptionClass'])->name('lms.school_subscription_class.paginate.no_major');

// paginate users by major and no major
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/role-account/{managedRole}/management-class/{classId}/management-majors/{majorId}/management-students/paginate', [StudentSchoolClassController::class, 'paginateLmsSchoolSubscriptionUsers'])->name('lms.school_subscription_users.paginate.major');
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/role-account/{managedRole}/management-class/{classId}/management-students/paginate', [StudentSchoolClassController::class, 'paginateLmsSchoolSubscriptionUsers'])->name('lms.school_subscription_users.paginate.no_major');
