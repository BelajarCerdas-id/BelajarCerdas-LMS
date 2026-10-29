<?php

use App\Http\Controllers\Administrator\FoundationManagementController;
use App\Http\Controllers\Lms\AssessmentManagement\AssessmentTypeController;
use App\Http\Controllers\Lms\AssessmentManagement\AssessmentWeightController;
use App\Http\Controllers\Lms\ContentBank\ContentBankController;
use App\Http\Controllers\Lms\QuestionBank\QuestionBankController;
use App\Http\Controllers\Lms\SubjectPassingGradeCriteria\SubjectPassingGradeCriteriaController;
use App\Http\Controllers\Lms\TeacherSubject\TeacherSubjectController;
use App\Http\Controllers\OfficeManagementController;
use Illuminate\Support\Facades\Route;

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
// validation content management no school partner & school partner
Route::post('/lms/content-management/validate', [ContentBankController::class, 'lmsContentManagementValidate'])->name('lms.contentManagement.validate');
Route::post('/lms/school-subscription/{schoolName}/{schoolId}/content-management/validate', [ContentBankController::class, 'lmsContentManagementValidate'])->name('lms.contentManagement.validate.schoolPartner');

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

// ROUTES SCHOOL FOUNDATION MANAGEMENT (In Administrator)
// views
Route::get('/lms/{role}/school-foundation/manage', [FoundationManagementController::class, 'index'])->name('lms.schoolFoundation.manage.view');
Route::get('/lms/{role}/school-foundation/manage/form', [FoundationManagementController::class, 'viewSchoolFoundationForm'])->name('lms.schoolFoundation.form.view');
Route::get('/lms/{role}/school-foundation/manage/edit-form/{schoolFoundationId}', [FoundationManagementController::class, 'viewSchoolFoundationEditForm'])->name('lms.schoolFoundation.form.edit.view');
Route::get('/lms/{role}/school-foundation/manage/access-control/{schoolFoundationId}', [FoundationManagementController::class, 'viewSchoolFoundationAccessControl'])->name('lms.schoolFoundation.access-control.view');
Route::get('/lms/{role}/school-foundation/manage/finance-access-control/{schoolFoundationId}', [FoundationManagementController::class, 'viewSchoolFoundationFinanceAccess'])->name('lms.schoolFoundation.finance-access-control.view');

// crud
Route::post('/lms/{role}/school-foundation/{schoolFoundationId}/manage/school-to-foundation/submit-form', [FoundationManagementController::class, 'addSchoolToFoundation'])->name('lms.addSchoolToFoundation.form.submit');
Route::post('/lms/{role}/school-foundation/{schoolFoundationId}/manage/remove-school-to-foundation/{schoolId}', [FoundationManagementController::class, 'removeSchoolFromFoundation'])->name('lms.removeSchoolFromFoundation.form.submit');
Route::post('/lms/{role}/school-foundation/manage/submit-form', [FoundationManagementController::class, 'schoolFoundationSubmitForm'])->name('lms.schoolFoundation.form.submit');
Route::post('/lms/{role}/school-foundation/manage/edit-form/{schoolFoundationId}/submit', [FoundationManagementController::class, 'editSchoolFoundationSubmitForm'])->name('lms.schoolFoundation.form.edit.submit');
Route::put('/lms/{role}/school-foundation/manage/access-control/{schoolFoundationId}/activate-account/{userId}', [FoundationManagementController::class, 'schoolFoundationAccessControlActivate'])->name('lms.schoolFoundation.access-control.activate');
Route::put('/lms/{role}/school-foundation/manage/access-control/{schoolFoundationId}/toggle-access/{profileId}', [FoundationManagementController::class, 'toggleSchoolFoundationAccessControl'])->name('lms.school-foundation.access-control.toggle');
Route::post('/lms/{role}/school-foundation/manage/access-control/{schoolFoundationId}/create-user-account', [FoundationManagementController::class, 'foundationCreateUser'])->name('lms.schoolFoundation.access-control.create-user');
Route::post('/lms/{role}/school-foundation/manage/access-control/{schoolFoundationId}/assign-user-account', [FoundationManagementController::class, 'foundationAssignUser'])->name('lms.schoolFoundation.access-control.assign-user');
Route::put('/lms/{role}/school-foundation/manage/access-control/{schoolFoundationId}/assign-user/{userId}', [FoundationManagementController::class, 'assignExistingAccount']);
Route::post('/lms/{role}/school-foundation/manage/finance-access-control/{schoolFoundationId}/create-link', [FoundationManagementController::class, 'schoolFoundationFinanceAccessStore'])->name('lms.schoolFoundation.finance-access-control.store');
Route::put('/lms/{role}/school-foundation/manage/finance-access-control/{schoolFoundationId}/status-access-control/activate/{linkId}', [FoundationManagementController::class, 'schoolFoundationFinanceAccessActivate'])->name('lms.schoolFoundation.finance-access-control.activate');
Route::post('/lms/{role}/school-foundation/manage/finance-access-control/{schoolFoundationId}/status-access-control/edit/{linkId}', [FoundationManagementController::class, 'schoolFoundationFinanceAccessEdit'])->name('lms.schoolFoundation.finance-access-control.edit');

// load data edit
Route::get('/lms/{role}/school-foundation/manage/edit-form/{schoolFoundationId}/load-data', [FoundationManagementController::class, 'editSchoolFoundationForm'])->name('lms.schoolFoundation.form.edit');

// paginate
Route::get('/lms/{role}/school-foundation/paginate', [FoundationManagementController::class, 'paginateSchoolFoundation'])->name('lms.schoolFoundation.manage.paginate');
Route::get('/lms/{role}/school-foundation/manage/form/paginate-school-list', [FoundationManagementController::class, 'paginateSchoolList'])->name('lms.schoolFoundation.school-list.paginate');
Route::get('/lms/{role}/school-foundation/{foundationId}/manage/form/paginate-school-list', [FoundationManagementController::class, 'paginateSchoolList'])->name('lms.schoolFoundation.school-list.foundation.paginate');
Route::get('/lms/{role}/school-foundation/manage/access-control/{schoolFoundationId}/paginate', [FoundationManagementController::class, 'paginateSchoolFoundationAccessControl'])->name('lms.schoolFoundation.access-control.paginate');
Route::get('/lms/{role}/school-foundation/manage/access-control/{schoolFoundationId}/existing-account', [FoundationManagementController::class, 'loadExistingAccounts']);
Route::get('/lms/{role}/school-foundation/manage/finance-access-control/{schoolFoundationId}/paginate', [FoundationManagementController::class, 'paginateSchoolFoundationFinanceAccess'])->name('lms.schoolFoundation.finance-access-control.paginate');

// =========================================================
// ROUTES FINANCE
