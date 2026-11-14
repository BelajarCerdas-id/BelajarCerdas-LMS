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
Route::get('/lms/{role}/content-management', [ContentBankController::class, 'lmsContentManagementView'])->name('Lms.ContentManagement.View.NoSchoolPartner');
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/content-management', [ContentBankController::class, 'lmsContentManagementView'])->name('Lms.ContentManagement.View.SchoolPartner');

// view content management edit no school partner & school partner
Route::get('/lms/{role}/content-management/{contentId}/edit', [ContentBankController::class, 'lmsDefaultContentManagementEditView'])->name('Lms.ContentManagement.Edit.View.NoSchoolPartner');
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/content-management/{contentId}/edit', [ContentBankController::class, 'lmsSchoolContentManagementEditView'])->name('Lms.ContentManagement.Edit.View.SchoolPartner');

// view content management review no school partner & school partner
Route::get('/lms/{role}/content-management/{contentId}/review', [ContentBankController::class, 'lmsReviewContentDefault'])->name('Lms.ContentManagement.Review.NoSchoolPartner');
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/content-management/{contentId}/review', [ContentBankController::class, 'lmsReviewContentSchool'])->name('Lms.ContentManagement.Review.SchoolPartner');

// form edit content
Route::get('/lms/content-management/{contentId}/form/edit', [ContentBankController::class, 'lmsContentManagementFormEdit'])->name('Lms.ContentManagementForm.Edit');

// crud
// validation content management no school partner & school partner
Route::post('/lms/content-management/validate', [ContentBankController::class, 'lmsContentManagementValidate'])->name('Lms.ContentManagement.Validate');
Route::post('/lms/school-subscription/{schoolName}/{schoolId}/content-management/validate', [ContentBankController::class, 'lmsContentManagementValidate'])->name('Lms.ContentManagement.Validate.SchoolPartner');

// create content management no school partner & school partner
Route::post('/lms/content-management/store', [ContentBankController::class, 'lmsContentManagementStore'])->name('Lms.ContentManagement.Store.NoSchoolPartner');
Route::post('/lms/school-subscription/{schoolName}/{schoolId}/content-management/store', [ContentBankController::class, 'lmsContentManagementStore'])->name('Lms.ContentManagement.Store.SchoolPartner');

Route::post('/lms/content-management/{contentId}/edit-action', [ContentBankController::class, 'lmsContentManagementEdit'])->name('Lms.ContentManagement.Edit');

// routes activate content management no school partner & school partner
Route::put('/lms/content-management/{contentId}/activate', [ContentBankController::class, 'lmsContentManagementActivate'])->name('Lms.ContentManagement.Activate.NoSchoolPartner');
Route::put('/lms/school-subscription/content-management/{contentId}/{schoolName}/{schoolId}/activate', [ContentBankController::class, 'lmsContentManagementActivate'])->name('Lms.ContentManagement.Activate.SchoolPartner');

// paginate content management no school partner & school partner
Route::get('/lms/content-management/paginate', [ContentBankController::class, 'paginateLmsContentManagement'])->name('Lms.ContentManagement.Paginate.NoSchoolPartner');
Route::get('/lms/school-subscription/{schoolName}/{schoolId}/content-management/paginate', [ContentBankController::class, 'paginateLmsContentManagement'])->name('Lms.ContentManagement.Paginate.SchoolPartner');

// =========================================================
// ROUTES ASSESSMENT TYPE MANAGEMENT
// views
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/assessment-type-management', [AssessmentTypeController::class, 'lmsAssessmentTypeManagementView'])->name('Lms.AssessmentTypeManagement.View');

// crud
Route::post('/lms/school-subscription/{schoolName}/{schoolId}/assessment-type-management/store', [AssessmentTypeController::class, 'lmsAssessmentTypeManagementStore'])->name('Lms.AssessmentTypeManagement.Store');
Route::post('/lms/school-subscription/{schoolName}/{schoolId}/assessment-type-management/{assessmentTypeId}/edit', [AssessmentTypeController::class, 'lmsAssessmentTypeManagementEdit'])->name('Lms.AssessmentTypeManagement.Edit');
Route::put('/lms/school-subscription/{schoolName}/{schoolId}/assessment-type-management/{assessmentTypeId}/activate', [AssessmentTypeController::class, 'lmsAssessmentTypeManagementActivate'])->name('Lms.AssessmentTypeManagement.Activate');

// paginate
Route::get('/lms/school-subscription/{schoolName}/{schoolId}/assessment-type-management/paginate', [AssessmentTypeController::class, 'paginateLmsAssessmentTypeManagement'])->name('Lms.AssessmentTypeManagement.Paginate');

// =========================================================
// ROUTES TEACHER SUBJECT MANAGEMENT
// views
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/subject-teacher-management', [TeacherSubjectController::class, 'lmsTeacherSubjectManagement'])->name('LmsTeacherSubjectManagement.View');

// crud
Route::post('/lms/school-subscription/{schoolName}/{schoolId}/subject-teacher-management/store', [TeacherSubjectController::class, 'lmsTeacherSubjectManagementStore'])->name('LmsTeacherSubjectManagement.Store');
Route::post('/lms/school-subscription/{schoolName}/{schoolId}/subject-teacher-management/{teacherSubjectId}/edit', [TeacherSubjectController::class, 'lmsTeacherSubjectManagementEdit'])->name('LmsTeacherSubjectManagement.Edit');
Route::put('/lms/school-subscription/{schoolName}/{schoolId}/subject-teacher-management/{teacherSubjectId}/activate', [TeacherSubjectController::class, 'lmsTeacherSubjectManagementActivate'])->name('LmsTeacherSubjectManagement.Activate');

// paginate
Route::get('/lms/school-subscription/{schoolName}/{schoolId}/subject-teacher-management/paginate', [TeacherSubjectController::class, 'paginateLmsTeacherSubjectManagement'])->name('LmsTeacherSubjectManagement.Paginate');

// =========================================================
// ROUTES ASSESSMENT WEIGHT MANAGEMENT
// views
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/assessment-weight-management', [AssessmentWeightController::class, 'assessmentWeight'])->name('Lms.AssessmentWeight.View');

// crud
Route::post('/lms/school-subscription/{schoolName}/{schoolId}/assessment-weight-management/store', [AssessmentWeightController::class, 'assessmentWeightStore'])->name('Lms.AssessmentWeight.Store');
Route::post('/lms/school-subscription/{schoolName}/{schoolId}/assessment-weight-management/{assessmentWeightId}/edit', [AssessmentWeightController::class, 'assessmentWeightEdit'])->name('Lms.AssessmentWeight.Edit');

// paginate
Route::get('/lms/school-subscription/{schoolName}/{schoolId}/assessment-weight-management/paginate', [AssessmentWeightController::class, 'paginateAssessmentWeight'])->name('Lms.AssessmentWeight.Paginate');

// =========================================================
// ROUTES SUBJECT PASSING GRADE CRITERIA MANAGEMENT
// views
Route::get('/lms/{role}/school-subscription/{schoolName}/{schoolId}/academic-management/subject-passing-grade-criteria-management', [SubjectPassingGradeCriteriaController::class, 'subjectPassingGradeCriteria'])->name('Lms.SubjectPassingGradeCriteria.View');

// crud
Route::post('/lms/school-subscription/{schoolName}/{schoolId}/subject-passing-grade-criteria-management/store', [SubjectPassingGradeCriteriaController::class, 'subjectPassingGradeCriteriaStore'])->name('Lms.SubjectPassingGradeCriteria.Store');
Route::post('/lms/school-subscription/{schoolName}/{schoolId}/subject-passing-grade-criteria-management/{subjectPassingGradeCriteriaId}/edit', [SubjectPassingGradeCriteriaController::class, 'subjectPassingGradeCriteriaEdit'])->name('Lms.SubjectPassingGradeCriteria.Edit');
Route::post('/lms/school-subscription/{schoolName}/{schoolId}/subject-passing-grade-criteria-management/bulkupload-store', [SubjectPassingGradeCriteriaController::class, 'bulkUploadSubjectPassingGradeCriteria'])->name('Lms.SubjectPassingGradeCriteria.BulkUpload.Store');

// paginate
Route::get('/lms/school-subscription/{schoolName}/{schoolId}/subject-passing-grade-criteria-management/paginate', [SubjectPassingGradeCriteriaController::class, 'paginateSubjectPassingGradeCriteria'])->name('Lms.SubjectPassingGradeCriteria.Paginate');

// ROUTES OFFICE MANAGEMENT
Route::get('/lms/{role}/office-management/manage-user', [OfficeManagementController::class, 'manageUserView'])->name('Lms.OfficeManagement.ManageUser.View');

// crud
Route::post('/lms/{role}/office-management/manage-user/store', [OfficeManagementController::class, 'manageUserStore'])->name('Lms.OfficeManagement.ManageUser.Store');
Route::post('/lms/{role}/office-management/manage-user/edit/{userAccountId}', [OfficeManagementController::class, 'manageUserEdit'])->name('Lms.OfficeManagement.ManageUser.Edit');
Route::put('/lms/{role}/office-management/manage-user/activate-account/{userAccountId}', [OfficeManagementController::class, 'manageUserActivate'])->name('Lms.OfficeManagement.ManageUser.Activate');

// paginate
Route::get('/lms/{role}/office-management/manage-user/paginate', [OfficeManagementController::class, 'paginateManageUser'])->name('Lms.OfficeManagement.ManageUser.Paginate');

// ROUTES SCHOOL FOUNDATION MANAGEMENT (In Administrator)
// views
Route::get('/lms/{role}/school-foundation/manage', [FoundationManagementController::class, 'index'])->name('Lms.SchoolFoundation.Manage.View');
Route::get('/lms/{role}/school-foundation/manage/form', [FoundationManagementController::class, 'viewSchoolFoundationForm'])->name('Lms.SchoolFoundation.Form.View');
Route::get('/lms/{role}/school-foundation/manage/edit-form/{schoolFoundationId}', [FoundationManagementController::class, 'viewSchoolFoundationEditForm'])->name('Lms.SchoolFoundation.Form.Edit.View');
Route::get('/lms/{role}/school-foundation/manage/access-control/{schoolFoundationId}', [FoundationManagementController::class, 'viewSchoolFoundationAccessControl'])->name('Lms.SchoolFoundation.AccessControl.View');
Route::get('/lms/{role}/school-foundation/manage/finance-access-control/{schoolFoundationId}', [FoundationManagementController::class, 'viewSchoolFoundationFinanceAccess'])->name('Lms.SchoolFoundation.FinanceAccessControl.View');

// crud
Route::post('/lms/{role}/school-foundation/{schoolFoundationId}/manage/school-to-foundation/submit-form', [FoundationManagementController::class, 'addSchoolToFoundation'])->name('Lms.AddSchoolToFoundation.Form.Submit');
Route::post('/lms/{role}/school-foundation/{schoolFoundationId}/manage/remove-school-to-foundation/{schoolId}', [FoundationManagementController::class, 'removeSchoolFromFoundation'])->name('Lms.RemoveSchoolFromFoundation.Form.Submit');
Route::post('/lms/{role}/school-foundation/manage/submit-form', [FoundationManagementController::class, 'schoolFoundationSubmitForm'])->name('Lms.SchoolFoundation.Form.Submit');
Route::post('/lms/{role}/school-foundation/manage/edit-form/{schoolFoundationId}/submit', [FoundationManagementController::class, 'editSchoolFoundationSubmitForm'])->name('Lms.SchoolFoundation.Form.Edit.Submit');
Route::put('/lms/{role}/school-foundation/manage/access-control/{schoolFoundationId}/activate-account/{userId}', [FoundationManagementController::class, 'schoolFoundationAccessControlActivate'])->name('Lms.SchoolFoundation.AccessControl.Activate');
Route::put('/lms/{role}/school-foundation/manage/access-control/{schoolFoundationId}/toggle-access/{profileId}', [FoundationManagementController::class, 'toggleSchoolFoundationAccessControl'])->name('Lms.SchoolFoundation.AccessControl.Toggle');
Route::post('/lms/{role}/school-foundation/manage/access-control/{schoolFoundationId}/create-user-account', [FoundationManagementController::class, 'foundationCreateUser'])->name('Lms.SchoolFoundation.AccessControl.CreateUser');
Route::post('/lms/{role}/school-foundation/manage/access-control/{schoolFoundationId}/assign-user-account', [FoundationManagementController::class, 'foundationAssignUser'])->name('Lms.SchoolFoundation.AccessControl.AssignUser');
Route::put('/lms/{role}/school-foundation/manage/access-control/{schoolFoundationId}/assign-user/{userId}', [FoundationManagementController::class, 'assignExistingAccount']);
Route::post('/lms/{role}/school-foundation/manage/finance-access-control/{schoolFoundationId}/create-link', [FoundationManagementController::class, 'schoolFoundationFinanceAccessStore'])->name('Lms.SchoolFoundation.FinanceAccessControl.Store');
Route::put('/lms/{role}/school-foundation/manage/finance-access-control/{schoolFoundationId}/status-access-control/activate/{linkId}', [FoundationManagementController::class, 'schoolFoundationFinanceAccessActivate'])->name('Lms.SchoolFoundation.FinanceAccessControl.Activate');
Route::post('/lms/{role}/school-foundation/manage/finance-access-control/{schoolFoundationId}/status-access-control/edit/{linkId}', [FoundationManagementController::class, 'schoolFoundationFinanceAccessEdit'])->name('Lms.SchoolFoundation.FinanceAccessControl.Edit');

// load data edit
Route::get('/lms/{role}/school-foundation/manage/edit-form/{schoolFoundationId}/load-data', [FoundationManagementController::class, 'editSchoolFoundationForm'])->name('Lms.SchoolFoundation.Form.Edit');

// paginate
Route::get('/lms/{role}/school-foundation/paginate', [FoundationManagementController::class, 'paginateSchoolFoundation'])->name('Lms.SchoolFoundation.Manage.Paginate');
Route::get('/lms/{role}/school-foundation/manage/form/paginate-school-list', [FoundationManagementController::class, 'paginateSchoolList'])->name('Lms.SchoolFoundation.SchoolList.Paginate');
Route::get('/lms/{role}/school-foundation/{foundationId}/manage/form/paginate-school-list', [FoundationManagementController::class, 'paginateSchoolList'])->name('Lms.SchoolFoundation.SchoolList.Foundation.Paginate');
Route::get('/lms/{role}/school-foundation/manage/access-control/{schoolFoundationId}/paginate', [FoundationManagementController::class, 'paginateSchoolFoundationAccessControl'])->name('Lms.SchoolFoundation.AccessControl.Paginate');
Route::get('/lms/{role}/school-foundation/manage/access-control/{schoolFoundationId}/existing-account', [FoundationManagementController::class, 'loadExistingAccounts']);
Route::get('/lms/{role}/school-foundation/manage/finance-access-control/{schoolFoundationId}/paginate', [FoundationManagementController::class, 'paginateSchoolFoundationFinanceAccess'])->name('Lms.SchoolFoundation.FinanceAccessControl.Paginate');

// =========================================================
// ROUTES FINANCE
