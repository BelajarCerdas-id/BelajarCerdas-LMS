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
Route::get('/lms/{role}/manage-contract/load-top-revenue', [FinanceDashboardController::class, 'loadTopRevenueDashboard'])->name('lms.finance.manage-contract.load-top-revenue');
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
Route::post('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/service/{serviceId}/read-content/{meetingContentId}', [StudentLearningController::class, 'readStudentReviewContent'])->name('lms.studentReviewContent.read');
Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/service/{serviceId}/download-content/{meetingContentId}', [StudentLearningController::class, 'downloadStudentContent'])->name('lms.studentContent.download');

// paginate learning
Route::get('/lms/{role}/{schoolName}/{schoolId}/paginate', [StudentLearningController::class, 'paginateLmsStudent'])->name('lms.student.paginate');
Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/paginate', [StudentLearningController::class, 'paginateStudentLearning'])->name('lms.studentLearning.paginate');
Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/service/{serviceId}/semester/{semester}/paginate', [StudentLearningController::class, 'paginateStudentReviewMeeting'])->name('lms.studentReviewMeeting.paginate');
