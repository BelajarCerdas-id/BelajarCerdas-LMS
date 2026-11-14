<?php

use App\Http\Controllers\Administrator\FoundationManagementController;
use App\Http\Controllers\Lms\AssessmentManagement\AssessmentTypeController;
use App\Http\Controllers\Lms\AssessmentManagement\AssessmentWeightController;
use App\Http\Controllers\Lms\ContentBank\ContentBankController;
use App\Http\Controllers\Lms\QuestionBank\QuestionBankController;
use App\Http\Controllers\Lms\SubjectPassingGradeCriteria\SubjectPassingGradeCriteriaController;
use App\Http\Controllers\Lms\TeacherSubject\TeacherSubjectController;
use App\Http\Controllers\OfficeManagementController;
use App\Http\Controllers\PrivateFileController;
use Illuminate\Support\Facades\Route;

// =========================================================
// PRIVATE FILE STREAMING (uploads moved off the webroot)
// URL paths match the ones stored in the DB/JSON so old links keep working;
// files live on the local disk (storage/app) and require an authenticated session.
// shortcut: any authenticated user can read; per-file ACL comes with the refactored demo.
// =========================================================
Route::get('/lms-assessment-submission/assessment-test/{filename}', [PrivateFileController::class, 'show'])
    ->defaults('zone', 'lms-assessment-submission/assessment-test');
Route::get('/assessment/assessment-file/{filename}', [PrivateFileController::class, 'show'])
    ->defaults('zone', 'assessment/assessment-file');
Route::get('/assessment/assessment-file-submission/{filename}', [PrivateFileController::class, 'show'])
    ->defaults('zone', 'assessment/assessment-file-submission');


// NO SCHOOL PARTNER
// no bab & no sub bab
Route::get('/lms/question-bank-management/kurikulum/{kurikulumId}/kelas/{kelasId}/mapel/{mapelId}/source/{source}/review/question-type/{questionType}/question-category/{questionCategory}/paginate/without-bab', [QuestionBankController::class, 'paginateReviewQuestionBank']);

// bab only
Route::get('/lms/question-bank-management/kurikulum/{kurikulumId}/kelas/{kelasId}/mapel/{mapelId}/source/{source}/review/question-type/{questionType}/question-category/{questionCategory}/paginate/bab/{babId?}', [QuestionBankController::class, 'paginateReviewQuestionBank']);

// bab + sub bab
Route::get('/lms/question-bank-management/kurikulum/{kurikulumId}/kelas/{kelasId}/mapel/{mapelId}/source/{source}/review/question-type/{questionType}/question-category/{questionCategory}/paginate/bab/{babId?}/sub-bab/{subBabId?}', [QuestionBankController::class, 'paginateReviewQuestionBank']);

// SCHOOL PARTNER
// no bab & no sub bab
Route::get('/lms/question-bank-management/kurikulum/{kurikulumId}/kelas/{kelasId}/mapel/{mapelId}/source/{source}/review/question-type/{questionType}/question-category/{questionCategory}/school-subscription/{schoolName}/{schoolId}/paginate/without-bab', [QuestionBankController::class, 'paginateReviewQuestionBankSchool']);

// bab only
Route::get('/lms/question-bank-management/kurikulum/{kurikulumId}/kelas/{kelasId}/mapel/{mapelId}/source/{source}/review/question-type/{questionType}/question-category/{questionCategory}/school-subscription/{schoolName}/{schoolId}/paginate/bab/{babId?}', [QuestionBankController::class, 'paginateReviewQuestionBankSchool']);

// bab + sub bab
Route::get('/lms/question-bank-management/kurikulum/{kurikulumId}/kelas/{kelasId}/mapel/{mapelId}/source/{source}/review/question-type/{questionType}/question-category/{questionCategory}/school-subscription/{schoolName}/{schoolId}/paginate/bab/{babId?}/sub-bab/{subBabId?}', [QuestionBankController::class, 'paginateReviewQuestionBankSchool']);

// =========================================================
// ROUTES CONTENT MANAGEMENT
// view content management no school partner & school partner
Route::get('/lms/{role}/content-management', [ContentBankController::class, 'lmsContentManagementView'])->name('lms.content_management.view.no_school_partner');
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/content-management', [ContentBankController::class, 'lmsContentManagementView'])->name('lms.content_management.view.school_partner');

// view content management edit no school partner & school partner
Route::get('/lms/{role}/content-management/{contentId}/edit', [ContentBankController::class, 'lmsDefaultContentManagementEditView'])->name('lms.content_management.edit.view.no_school_partner');
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/content-management/{contentId}/edit', [ContentBankController::class, 'lmsSchoolContentManagementEditView'])->name('lms.content_management.edit.view.school_partner');

// view content management review no school partner & school partner
Route::get('/lms/{role}/content-management/{contentId}/review', [ContentBankController::class, 'lmsReviewContentDefault'])->name('lms.content_management.review.no_school_partner');
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/content-management/{contentId}/review', [ContentBankController::class, 'lmsReviewContentSchool'])->name('lms.content_management.review.school_partner');

// form edit content
Route::get('/lms/content-management/{contentId}/form/edit', [ContentBankController::class, 'lmsContentManagementFormEdit'])->name('lms.content_management_form.edit');

// crud
// validation content management no school partner & school partner
Route::post('/lms/content-management/validate', [ContentBankController::class, 'lmsContentManagementValidate'])->name('lms.content_management.validate');
Route::post('/lms/school-subscription/{schoolName}/{schoolId}/content-management/validate', [ContentBankController::class, 'lmsContentManagementValidate'])->name('lms.content_management.validate.school_partner');

// create content management no school partner & school partner
Route::post('/lms/content-management/store', [ContentBankController::class, 'lmsContentManagementStore'])->name('lms.content_management.store.no_school_partner');
Route::post('/lms/school-subscription/{schoolName}/{schoolId}/content-management/store', [ContentBankController::class, 'lmsContentManagementStore'])->name('lms.content_management.store.school_partner');

Route::post('/lms/content-management/{contentId}/edit-action', [ContentBankController::class, 'lmsContentManagementEdit'])->name('lms.content_management.edit');

// routes activate content management no school partner & school partner
Route::put('/lms/content-management/{contentId}/activate', [ContentBankController::class, 'lmsContentManagementActivate'])->name('lms.content_management.activate.no_school_partner');
Route::put('/lms/school-subscription/content-management/{contentId}/{schoolName}/{schoolId}/activate', [ContentBankController::class, 'lmsContentManagementActivate'])->name('lms.content_management.activate.school_partner');

// paginate content management no school partner & school partner
Route::get('/lms/content-management/paginate', [ContentBankController::class, 'paginateLmsContentManagement'])->name('lms.content_management.paginate.no_school_partner');
Route::get('/lms/school-subscription/{schoolName}/{schoolId}/content-management/paginate', [ContentBankController::class, 'paginateLmsContentManagement'])->name('lms.content_management.paginate.school_partner');

// =========================================================
// ROUTES ASSESSMENT TYPE MANAGEMENT
// views
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/assessment-type-management', [AssessmentTypeController::class, 'lmsAssessmentTypeManagementView'])->name('lms.assessment_type_management.view');

// crud
Route::post('/lms/school-subscription/{schoolName}/{schoolId}/assessment-type-management/store', [AssessmentTypeController::class, 'lmsAssessmentTypeManagementStore'])->name('lms.assessment_type_management.store');
Route::post('/lms/school-subscription/{schoolName}/{schoolId}/assessment-type-management/{assessmentTypeId}/edit', [AssessmentTypeController::class, 'lmsAssessmentTypeManagementEdit'])->name('lms.assessment_type_management.edit');
Route::put('/lms/school-subscription/{schoolName}/{schoolId}/assessment-type-management/{assessmentTypeId}/activate', [AssessmentTypeController::class, 'lmsAssessmentTypeManagementActivate'])->name('lms.assessment_type_management.activate');

// paginate
Route::get('/lms/school-subscription/{schoolName}/{schoolId}/assessment-type-management/paginate', [AssessmentTypeController::class, 'paginateLmsAssessmentTypeManagement'])->name('lms.assessment_type_management.paginate');

// =========================================================
// ROUTES TEACHER SUBJECT MANAGEMENT
// views
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/subject-teacher-management', [TeacherSubjectController::class, 'lmsTeacherSubjectManagement'])->name('lms_teacher_subject_management.view');

// crud
Route::post('/lms/school-subscription/{schoolName}/{schoolId}/subject-teacher-management/store', [TeacherSubjectController::class, 'lmsTeacherSubjectManagementStore'])->name('lms_teacher_subject_management.store');
Route::post('/lms/school-subscription/{schoolName}/{schoolId}/subject-teacher-management/{teacherSubjectId}/edit', [TeacherSubjectController::class, 'lmsTeacherSubjectManagementEdit'])->name('lms_teacher_subject_management.edit');
Route::put('/lms/school-subscription/{schoolName}/{schoolId}/subject-teacher-management/{teacherSubjectId}/activate', [TeacherSubjectController::class, 'lmsTeacherSubjectManagementActivate'])->name('lms_teacher_subject_management.activate');

// paginate
Route::get('/lms/school-subscription/{schoolName}/{schoolId}/subject-teacher-management/paginate', [TeacherSubjectController::class, 'paginateLmsTeacherSubjectManagement'])->name('lms_teacher_subject_management.paginate');

// =========================================================
// ROUTES ASSESSMENT WEIGHT MANAGEMENT
// views
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/assessment-weight-management', [AssessmentWeightController::class, 'assessmentWeight'])->name('lms.assessment_weight.view');

// crud
Route::post('/lms/school-subscription/{schoolName}/{schoolId}/assessment-weight-management/store', [AssessmentWeightController::class, 'assessmentWeightStore'])->name('lms.assessment_weight.store');
Route::post('/lms/school-subscription/{schoolName}/{schoolId}/assessment-weight-management/{assessmentWeightId}/edit', [AssessmentWeightController::class, 'assessmentWeightEdit'])->name('lms.assessment_weight.edit');

// paginate
Route::get('/lms/school-subscription/{schoolName}/{schoolId}/assessment-weight-management/paginate', [AssessmentWeightController::class, 'paginateAssessmentWeight'])->name('lms.assessment_weight.paginate');

// =========================================================
// ROUTES SUBJECT PASSING GRADE CRITERIA MANAGEMENT
// views
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/subject-passing-grade-criteria-management', [SubjectPassingGradeCriteriaController::class, 'subjectPassingGradeCriteria'])->name('lms.subject_passing_grade_criteria.view');

// crud
Route::post('/lms/school-subscription/{schoolName}/{schoolId}/subject-passing-grade-criteria-management/store', [SubjectPassingGradeCriteriaController::class, 'subjectPassingGradeCriteriaStore'])->name('lms.subject_passing_grade_criteria.store');
Route::post('/lms/school-subscription/{schoolName}/{schoolId}/subject-passing-grade-criteria-management/{subjectPassingGradeCriteriaId}/edit', [SubjectPassingGradeCriteriaController::class, 'subjectPassingGradeCriteriaEdit'])->name('lms.subject_passing_grade_criteria.edit');
Route::post('/lms/school-subscription/{schoolName}/{schoolId}/subject-passing-grade-criteria-management/bulkupload-store', [SubjectPassingGradeCriteriaController::class, 'bulkUploadSubjectPassingGradeCriteria'])->name('lms.subject_passing_grade_criteria.bulk_upload.store');

// paginate
Route::get('/lms/school-subscription/{schoolName}/{schoolId}/subject-passing-grade-criteria-management/paginate', [SubjectPassingGradeCriteriaController::class, 'paginateSubjectPassingGradeCriteria'])->name('lms.subject_passing_grade_criteria.paginate');

// ROUTES OFFICE MANAGEMENT
Route::get('/lms/{role}/office-management/manage-user', [OfficeManagementController::class, 'manageUserView'])->name('lms.office_management.manage_user.view');

// crud
Route::post('/lms/{role}/office-management/manage-user/store', [OfficeManagementController::class, 'manageUserStore'])->name('lms.office_management.manage_user.store');
Route::post('/lms/{role}/office-management/manage-user/edit/{userAccountId}', [OfficeManagementController::class, 'manageUserEdit'])->name('lms.office_management.manage_user.edit');
Route::put('/lms/{role}/office-management/manage-user/activate-account/{userAccountId}', [OfficeManagementController::class, 'manageUserActivate'])->name('lms.office_management.manage_user.activate');

// paginate
Route::get('/lms/{role}/office-management/manage-user/paginate', [OfficeManagementController::class, 'paginateManageUser'])->name('lms.office_management.manage_user.paginate');

// ROUTES SCHOOL FOUNDATION MANAGEMENT (In Administrator)
// views
Route::get('/lms/{role}/school-foundation/manage', [FoundationManagementController::class, 'index'])->name('lms.school_foundation.manage.view');
Route::get('/lms/{role}/school-foundation/manage/form', [FoundationManagementController::class, 'viewSchoolFoundationForm'])->name('lms.school_foundation.form.view');
Route::get('/lms/{role}/school-foundation/manage/edit-form/{schoolFoundationId}', [FoundationManagementController::class, 'viewSchoolFoundationEditForm'])->name('lms.school_foundation.form.edit.view');
Route::get('/lms/{role}/school-foundation/manage/access-control/{schoolFoundationId}', [FoundationManagementController::class, 'viewSchoolFoundationAccessControl'])->name('lms.school_foundation.access_control.view');
Route::get('/lms/{role}/school-foundation/manage/finance-access-control/{schoolFoundationId}', [FoundationManagementController::class, 'viewSchoolFoundationFinanceAccess'])->name('lms.school_foundation.finance_access_control.view');

// crud
Route::post('/lms/{role}/school-foundation/{schoolFoundationId}/manage/school-to-foundation/submit-form', [FoundationManagementController::class, 'addSchoolToFoundation'])->name('lms.add_school_to_foundation.form.submit');
Route::post('/lms/{role}/school-foundation/{schoolFoundationId}/manage/remove-school-to-foundation/{schoolId}', [FoundationManagementController::class, 'removeSchoolFromFoundation'])->name('lms.remove_school_from_foundation.form.submit');
Route::post('/lms/{role}/school-foundation/manage/submit-form', [FoundationManagementController::class, 'schoolFoundationSubmitForm'])->name('lms.school_foundation.form.submit');
Route::post('/lms/{role}/school-foundation/manage/edit-form/{schoolFoundationId}/submit', [FoundationManagementController::class, 'editSchoolFoundationSubmitForm'])->name('lms.school_foundation.form.edit.submit');
Route::put('/lms/{role}/school-foundation/manage/access-control/{schoolFoundationId}/activate-account/{userId}', [FoundationManagementController::class, 'schoolFoundationAccessControlActivate'])->name('lms.school_foundation.access_control.activate');
Route::put('/lms/{role}/school-foundation/manage/access-control/{schoolFoundationId}/toggle-access/{profileId}', [FoundationManagementController::class, 'toggleSchoolFoundationAccessControl'])->name('lms.school_foundation.access_control.toggle');
Route::post('/lms/{role}/school-foundation/manage/access-control/{schoolFoundationId}/create-user-account', [FoundationManagementController::class, 'foundationCreateUser'])->name('lms.school_foundation.access_control.create_user');
Route::post('/lms/{role}/school-foundation/manage/access-control/{schoolFoundationId}/assign-user-account', [FoundationManagementController::class, 'foundationAssignUser'])->name('lms.school_foundation.access_control.assign_user');
Route::put('/lms/{role}/school-foundation/manage/access-control/{schoolFoundationId}/assign-user/{userId}', [FoundationManagementController::class, 'assignExistingAccount']);
Route::post('/lms/{role}/school-foundation/manage/finance-access-control/{schoolFoundationId}/create-link', [FoundationManagementController::class, 'schoolFoundationFinanceAccessStore'])->name('lms.school_foundation.finance_access_control.store');
Route::put('/lms/{role}/school-foundation/manage/finance-access-control/{schoolFoundationId}/status-access-control/activate/{linkId}', [FoundationManagementController::class, 'schoolFoundationFinanceAccessActivate'])->name('lms.school_foundation.finance_access_control.activate');
Route::post('/lms/{role}/school-foundation/manage/finance-access-control/{schoolFoundationId}/status-access-control/edit/{linkId}', [FoundationManagementController::class, 'schoolFoundationFinanceAccessEdit'])->name('lms.school_foundation.finance_access_control.edit');

// load data edit
Route::get('/lms/{role}/school-foundation/manage/edit-form/{schoolFoundationId}/load-data', [FoundationManagementController::class, 'editSchoolFoundationForm'])->name('lms.school_foundation.form.edit');

// paginate
Route::get('/lms/{role}/school-foundation/paginate', [FoundationManagementController::class, 'paginateSchoolFoundation'])->name('lms.school_foundation.manage.paginate');
Route::get('/lms/{role}/school-foundation/manage/form/paginate-school-list', [FoundationManagementController::class, 'paginateSchoolList'])->name('lms.school_foundation.school_list.paginate');
Route::get('/lms/{role}/school-foundation/{foundationId}/manage/form/paginate-school-list', [FoundationManagementController::class, 'paginateSchoolList'])->name('lms.school_foundation.school_list.foundation.paginate');
Route::get('/lms/{role}/school-foundation/manage/access-control/{schoolFoundationId}/paginate', [FoundationManagementController::class, 'paginateSchoolFoundationAccessControl'])->name('lms.school_foundation.access_control.paginate');
Route::get('/lms/{role}/school-foundation/manage/access-control/{schoolFoundationId}/existing-account', [FoundationManagementController::class, 'loadExistingAccounts']);
Route::get('/lms/{role}/school-foundation/manage/finance-access-control/{schoolFoundationId}/paginate', [FoundationManagementController::class, 'paginateSchoolFoundationFinanceAccess'])->name('lms.school_foundation.finance_access_control.paginate');

// =========================================================
// ROUTES FINANCE
