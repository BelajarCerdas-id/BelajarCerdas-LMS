<?php

use App\Http\Controllers\Finance\FinanceContractController;
use App\Http\Controllers\Finance\FinanceDashboardController;
use App\Http\Controllers\Finance\FinanceRevenueController;
use App\Http\Controllers\StudentAssessmentController;
use App\Http\Controllers\StudentAssessmentExamController;
use App\Http\Controllers\StudentLearningController;
use App\Http\Controllers\StudentSubjectProgressController;
use Illuminate\Support\Facades\Route;

// MANAGE CONTRACT (VIEWS)
Route::get('/lms/{role}/manage-contract', [FinanceContractController::class, 'index'])->name('Lms.Finance.ManageContract.View');
Route::get('/lms/{role}/manage-contract/schools/{schoolId}', [FinanceContractController::class, 'manageContractDetail'])->name('Lms.Finance.ManageContractDetail.View');
Route::get('/lms/{role}/manage-contract/schools/{schoolId}/contract/{contractId}/payment-detail', [FinanceContractController::class, 'contractPaymentDetail'])->name('Lms.Finance.Contract.Payment.Detail');
Route::get('/lms/{role}/manage-contract/schools/{schoolId}/contract/{contractId}/payment-detail/student-list/{termId}', [FinanceContractController::class, 'studentList'])->name('Lms.Finance.Contract.StudentList.View');

// crud manage contract detail
Route::put('/lms/{role}/manage-contract/schools/{schoolId}/contract/{contractId}/activate', [FinanceContractController::class, 'contractDetailActivate'])->name('Lms.Finance.ManageContractDetail.Activate');

// crud contract payment detail
Route::put('/lms/{role}/manage-contract/schools/{schoolId}/contract/{contractId}/payment-detail/term/{termId}/mark-status', [FinanceContractController::class, 'markContractTerm'])->name('Lms.Finance.Contract.Payment.MarkStatus');

// crud manage contract term (student)
Route::post('/lms/{role}/manage-contract/schools/{schoolId}/contract/{contractId}/payment-detail/student-list/{termId}/store', [FinanceContractController::class, 'bulkUploadContractStundents'])->name('Lms.Finance.Contract.StudentList.Store');
Route::put('/lms/{role}/manage-contract/schools/{schoolId}/contract/{contractId}/payment-detail/student-list/{termId}/student/{studentTermId}/activate', [FinanceContractController::class, 'studentListActivate'])->name('Lms.Finance.Contract.StudentList.Activate');

// paginate
Route::get('/lms/{role}/manage/contract/paginate', [FinanceContractController::class, 'paginateManageContract'])->name('Lms.Finance.Manage.Contract.Paginate');
Route::get('/lms/{role}/manage/contract/schools/{schoolId}/paginate', [FinanceContractController::class, 'paginateManageContractDetail'])->name('Lms.Finance.Manage.ContractDetail.Paginate');
Route::get('/lms/{role}/manage-contract/schools/{schoolId}/contract/{contractId}/payment-detail/paginate', [FinanceContractController::class, 'paginateContractPaymentDetail'])->name('Lms.Finance.ContractPaymentDetail.Paginate');
Route::get('/lms/{role}/manage-contract/schools/{schoolId}/contract/{contractId}/payment-detail/student-list/{termId}/paginate', [FinanceContractController::class, 'paginateStudentList'])->name('Lms.Finance.Contract.StudentList.Paginate');

// MANAGE REVENUE (VIEWS)
Route::get('/lms/{role}/manage/revenue', [FinanceRevenueController::class, 'index'])->name('Lms.Finance.Manage.Revenue.View');

// data manage revenue
Route::get('/lms/{role}/manage/revenue/load-kpi', [FinanceRevenueController::class, 'revenueManagementLoadKpi'])->name('Lms.Finance.Manage.Revenue.LoadKpi');
Route::get('/lms/{role}/manage/revenue/load-leaderboard', [FinanceRevenueController::class, 'revenueManagementLoadLeaderboard'])->name('Lms.Finance.Manage.Revenue.LoadLeaderboard');
Route::get('/lms/{role}/manage/revenue/load-chart', [FinanceRevenueController::class, 'revenueManagementLoadChart'])->name('Lms.Finance.Manage.Revenue.LoadChart');
Route::get('/lms/{role}/manage/revenue/paginate/load-ranking', [FinanceRevenueController::class, 'revenueManagementLoadRanking'])->name('Lms.Finance.Manage.Revenue.LoadRanking');

// data dashboard
Route::get('/lms/{role}/manage-contract/load-kpi', [FinanceDashboardController::class, 'loadKpiDashboard'])->name('Lms.Finance.ManageContract.LoadKpi');
Route::get('/lms/{role}/manage-contract/load-chart', [FinanceDashboardController::class, 'loadChartDashboard'])->name('Lms.Finance.ManageContract.LoadChart');
Route::get('/lms/{role}/manage-contract/load-top-revenue', [FinanceDashboardController::class, 'loadTopRevenueDashboard'])->name('Lms.Finance.ManageContract.LoadTopRevenue');
Route::get('/lms/{role}/manage-contract/load-contract-expiring', [FinanceDashboardController::class, 'loadContractExpiringDashboard'])->name('Lms.Finance.ManageContract.LoadContractExpiring');

// =========================================================
// ROUTES STUDENT LMS (wildcard — harus PALING BAWAH di grup /lms/{role}/...)
// =========================================================

// check assessment status (spesifik, taruh sebelum wildcard)
Route::get('/lms/check-assessment-status/{assessmentId}', [StudentAssessmentController::class, 'checkAssessmentStatus'])->name('Lms.CheckAssessmentStatus');

// image essay
Route::post('/lms/image-essay/store-image/endpoint', [StudentAssessmentExamController::class, 'storeImageEssay'])->name('AssessmentTest.StoreImage');
Route::post('/lms/image-essay/delete-image/endpoint', [StudentAssessmentExamController::class, 'deleteImageEssay'])->name('AssessmentTest.DeleteImage');

// subject progress
Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/subject-progress', [StudentSubjectProgressController::class, 'index'])->name('Lms.StudentSubjectProgress.Index');
Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/subject-progress/data', [StudentSubjectProgressController::class, 'data'])->name('Lms.StudentSubjectProgress.Data');

// learning routes
Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum', [StudentLearningController::class, 'lmsStudentView'])->name('Lms.Student.View');
Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning', [StudentLearningController::class, 'studentLearning'])->name('Lms.StudentLearning.View');
Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/service/{serviceId}', [StudentLearningController::class, 'studentReviewMeeting'])->name('Lms.StudentReviewMeeting.View');
Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/service/{serviceId}/show-content/{meetingContentId}', [StudentLearningController::class, 'showStudentReviewContent'])->name('Lms.StudentReviewContent.Show');
Route::post('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/service/{serviceId}/read-content/{meetingContentId}', [StudentLearningController::class, 'readStudentReviewContent'])->name('Lms.StudentReviewContent.Read');
Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/service/{serviceId}/download-content/{meetingContentId}', [StudentLearningController::class, 'downloadStudentContent'])->name('Lms.StudentContent.Download');

// paginate learning
Route::get('/lms/{role}/{schoolName}/{schoolId}/paginate', [StudentLearningController::class, 'paginateLmsStudent'])->name('Lms.Student.Paginate');
Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/paginate', [StudentLearningController::class, 'paginateStudentLearning'])->name('Lms.StudentLearning.Paginate');
Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/service/{serviceId}/semester/{semester}/paginate', [StudentLearningController::class, 'paginateStudentReviewMeeting'])->name('Lms.StudentReviewMeeting.Paginate');
