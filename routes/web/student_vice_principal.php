<?php

use App\Http\Controllers\ExtracurricularController;
use App\Http\Controllers\StudentVicePrincipalController;
use Illuminate\Support\Facades\Route;

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

// =========================================
// EXTRACURRICULAR MANAGEMENT
// =========================================

// LIST
Route::get(
    '/lms/{role}/{schoolName}/{schoolId}/extracurricular-management',
    [ExtracurricularController::class, 'index']
)->name('lms.student-vice-principal.extracurricular-management.view');

// DOWNLOAD TEMPLATE
Route::get(
    '/lms/{role}/{schoolName}/{schoolId}/extracurricular-management/download-template',
    [ExtracurricularController::class, 'downloadTemplate']
)->name('lms.student-vice-principal.extracurricular-management.download-template');

// PAGINATION
Route::get(
    '/lms/{role}/{schoolName}/{schoolId}/extracurricular-management/paginate',
    [ExtracurricularController::class, 'paginate']
)->name('lms.student-vice-principal.extracurricular-management.paginate');

// KELENGKAPAN
Route::get(
    '/lms/{role}/{schoolName}/{schoolId}/extracurricular-management/kelengkapan',
    [ExtracurricularController::class, 'kelengkapanView']
)->name('lms.student-vice-principal.extracurricular-management.kelengkapan');

Route::get(
    '/lms/{role}/{schoolName}/{schoolId}/extracurricular-management/kelengkapan/{extracurricularId}',
    [ExtracurricularController::class, 'kelengkapanDetail']
)->name('lms.student-vice-principal.extracurricular-management.kelengkapan.detail');

// CRUD
Route::post(
    '/lms/{role}/{schoolName}/{schoolId}/extracurricular-management/store',
    [ExtracurricularController::class, 'store']
)->name('lms.student-vice-principal.extracurricular-management.store');

Route::post(
    '/lms/{role}/{schoolName}/{schoolId}/extracurricular-management/update/{id}',
    [ExtracurricularController::class, 'update']
)->name('lms.student-vice-principal.extracurricular-management.update');

Route::delete(
    '/lms/{role}/{schoolName}/{schoolId}/extracurricular-management/delete/{id}',
    [ExtracurricularController::class, 'destroy']
)->name('lms.student-vice-principal.extracurricular-management.delete');

// IMPORT
Route::post(
    '/lms/{role}/{schoolName}/{schoolId}/extracurricular-management/import',
    [ExtracurricularController::class, 'importExcel']
)->name('lms.student-vice-principal.extracurricular-management.import');

// BULK MEMBER
Route::post(
    '/lms/{role}/{schoolName}/{schoolId}/extracurricular-management/{extracurricularId}/upload-member',
    [ExtracurricularController::class, 'importMember']
)->name('lms.student-vice-principal.extracurricular-management.upload-member');

// BULK ATTENDANCE
Route::post(
    '/lms/{role}/{schoolName}/{schoolId}/extracurricular-management/{extracurricularId}/upload-attendance',
    [ExtracurricularController::class, 'uploadAttendance']
)->name('lms.student-vice-principal.extracurricular-management.upload-attendance');

// DETAIL (HARUS PALING BAWAH)
Route::get(
    '/lms/{role}/{schoolName}/{schoolId}/extracurricular-management/{extracurricularId}',
    [ExtracurricularController::class, 'detail']
)->name('lms.student-vice-principal.extracurricular-management.detail');

// / =========================================
// MEMBER
// =========================================

// Simpan 1 member
Route::post(
    '/lms/{role}/{schoolName}/{schoolId}/extracurricular-management/{extracurricularId}/member/store',
    [ExtracurricularController::class, 'storeMember']
)->name(
    'lms.student-vice-principal.extracurricular-management.member.store'
);

// Import Member Excel
Route::post(
    '/lms/{role}/{schoolName}/{schoolId}/extracurricular-management/{extracurricularId}/member/import',
    [ExtracurricularController::class, 'importMember']
)->name(
    'lms.student-vice-principal.extracurricular-management.member.import'
);

// Download Template Member
Route::get(
    '/lms/{role}/{schoolName}/{schoolId}/extracurricular-management/{extracurricularId}/member/download-template',
    [ExtracurricularController::class, 'downloadMemberTemplate']
)->name(
    'lms.student-vice-principal.extracurricular-management.member.download-template'
);

// Hapus Member
Route::delete(
    '/lms/{role}/{schoolName}/{schoolId}/extracurricular-management/{extracurricularId}/member/{memberId}/delete',
    [ExtracurricularController::class, 'destroyMember']
)->name(
    'lms.student-vice-principal.extracurricular-management.member.destroy'
);

// =========================================================
