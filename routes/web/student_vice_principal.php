<?php

use App\Http\Controllers\ExtracurricularController;
use App\Http\Controllers\StudentVicePrincipalController;
use Illuminate\Support\Facades\Route;

// STUDENT VICE PRINCIPAL ROUTES
// dashboard
Route::get('lms/{role}/{schoolName}/{schoolId}/dashboard', [StudentVicePrincipalController::class, 'index'])->name('Lms.StudentVicePrincipal.Dashboard.View');

// REFLECTION MANAGEMENT
// view
Route::get('/lms/{role}/{schoolName}/{schoolId}/reflection-management', [StudentVicePrincipalController::class, 'reflectionMaangement'])->name('Lms.StudentVicePrincipal.ReflectionManagement.View');
Route::get('/lms/{role}/{schoolName}/{schoolId}/reflection-management/create', [StudentVicePrincipalController::class, 'createReflectionView'])->name('Lms.StudentVicePrincipal.Create.Reflection.View');
Route::get('/lms/{role}/{schoolName}/{schoolId}/reflection-management/history', [StudentVicePrincipalController::class, 'reflectionManagementHistoryView'])->name('Lms.StudentVicePrincipal.ReflectionManagementHistory.View');
Route::get('/lms/{role}/{schoolName}/{schoolId}/reflection-management/history-detail/{reflectionQuestionId}', [StudentVicePrincipalController::class, 'reflectionManagementHistoryDetailView'])->name('Lms.StudentVicePrincipal.ReflectionManagementHistoryDetail.View');

// form submit
Route::get('/lms/{role}/{schoolName}/{schoolId}/reflection-management/form-submit', [StudentVicePrincipalController::class, 'reflectionManagementForm'])->name('Lms.StudentVicePrincipal.Reflection.Form');

// crud
Route::post('/lms/{role}/{schoolName}/{schoolId}/reflection-management/store', [StudentVicePrincipalController::class, 'reflectionManagementStore'])->name('Lms.StudentVicePrincipal.Reflection.Store');

// paginate
Route::get('/lms/{role}/{schoolName}/{schoolId}/dashboard/load-student-reflection-chart', [StudentVicePrincipalController::class, 'loadStudentReflectionChart'])->name('Lms.StudentVicePrincipal.Dashboard.LoadStudentReflectionChart');
Route::get('/lms/{role}/{schoolName}/{schoolId}/reflection-management/history-recent', [StudentVicePrincipalController::class, 'paginateReflectionHistoryRecent'])->name('Lms.StudentVicePrincipal.ReflectionManagement.HistoryRecent');
Route::get('/lms/{role}/{schoolName}/{schoolId}/reflection-management/daily-reflection-live-preview', [StudentVicePrincipalController::class, 'paginateDailyReflectionLivePreview'])->name('Lms.StudentVicePrincipal.ReflectionManagement.DailyReflectionLivePreview');
Route::get('/lms/{role}/{schoolName}/{schoolId}/reflection-management/history/paginate', [StudentVicePrincipalController::class, 'paginateReflectionHistory'])->name('Lms.StudentVicePrincipal.ReflectionManagement.History');
Route::get('/lms/{role}/{schoolName}/{schoolId}/reflection-management/history-detail/{reflectionQuestionId}/load-header', [StudentVicePrincipalController::class, 'loadReflectionDetailHeader'])->name('Lms.StudentVicePrincipal.ReflectionManagementHistoryDetail.LoadHeader');
Route::get('/lms/{role}/{schoolName}/{schoolId}/reflection-management/history-detail/{reflectionQuestionId}/load-summary', [StudentVicePrincipalController::class, 'loadReflectionDetailSummary'])->name('Lms.StudentVicePrincipal.ReflectionManagementHistoryDetail.LoadSummary');
Route::get('/lms/{role}/{schoolName}/{schoolId}/reflection-management/history-detail/{reflectionQuestionId}/load-chart', [StudentVicePrincipalController::class, 'loadReflectionDetailChart'])->name('Lms.StudentVicePrincipal.ReflectionManagementHistoryDetail.LoadChart');
Route::get('/lms/{role}/{schoolName}/{schoolId}/reflection-management/history-detail/{reflectionQuestionId}/student-answer/paginate', [StudentVicePrincipalController::class, 'paginateReflectionStudentAnswer'])->name('Lms.StudentVicePrincipal.ReflectionManagementHistoryDetail.StudentAnswer.Paginate');

// =========================================
// EXTRACURRICULAR MANAGEMENT
// =========================================

// LIST
Route::get(
    '/lms/{role}/{schoolName}/{schoolId}/extracurricular-management',
    [ExtracurricularController::class, 'index']
)->name('Lms.StudentVicePrincipal.ExtracurricularManagement.View');

// DOWNLOAD TEMPLATE
Route::get(
    '/lms/{role}/{schoolName}/{schoolId}/extracurricular-management/download-template',
    [ExtracurricularController::class, 'downloadTemplate']
)->name('Lms.StudentVicePrincipal.ExtracurricularManagement.DownloadTemplate');

// PAGINATION
Route::get(
    '/lms/{role}/{schoolName}/{schoolId}/extracurricular-management/paginate',
    [ExtracurricularController::class, 'paginate']
)->name('Lms.StudentVicePrincipal.ExtracurricularManagement.Paginate');

// KELENGKAPAN
Route::get(
    '/lms/{role}/{schoolName}/{schoolId}/extracurricular-management/kelengkapan',
    [ExtracurricularController::class, 'kelengkapanView']
)->name('Lms.StudentVicePrincipal.ExtracurricularManagement.Kelengkapan');

Route::get(
    '/lms/{role}/{schoolName}/{schoolId}/extracurricular-management/kelengkapan/{extracurricularId}',
    [ExtracurricularController::class, 'kelengkapanDetail']
)->name('Lms.StudentVicePrincipal.ExtracurricularManagement.Kelengkapan.Detail');

// CRUD
Route::post(
    '/lms/{role}/{schoolName}/{schoolId}/extracurricular-management/store',
    [ExtracurricularController::class, 'store']
)->name('Lms.StudentVicePrincipal.ExtracurricularManagement.Store');

Route::post(
    '/lms/{role}/{schoolName}/{schoolId}/extracurricular-management/update/{id}',
    [ExtracurricularController::class, 'update']
)->name('Lms.StudentVicePrincipal.ExtracurricularManagement.Update');

Route::delete(
    '/lms/{role}/{schoolName}/{schoolId}/extracurricular-management/delete/{id}',
    [ExtracurricularController::class, 'destroy']
)->name('Lms.StudentVicePrincipal.ExtracurricularManagement.Delete');

// IMPORT
Route::post(
    '/lms/{role}/{schoolName}/{schoolId}/extracurricular-management/import',
    [ExtracurricularController::class, 'importExcel']
)->name('Lms.StudentVicePrincipal.ExtracurricularManagement.Import');

// BULK MEMBER
Route::post(
    '/lms/{role}/{schoolName}/{schoolId}/extracurricular-management/{extracurricularId}/upload-member',
    [ExtracurricularController::class, 'importMember']
)->name('Lms.StudentVicePrincipal.ExtracurricularManagement.UploadMember');

// BULK ATTENDANCE
Route::post(
    '/lms/{role}/{schoolName}/{schoolId}/extracurricular-management/{extracurricularId}/upload-attendance',
    [ExtracurricularController::class, 'uploadAttendance']
)->name('Lms.StudentVicePrincipal.ExtracurricularManagement.UploadAttendance');

// DETAIL (HARUS PALING BAWAH)
Route::get(
    '/lms/{role}/{schoolName}/{schoolId}/extracurricular-management/{extracurricularId}',
    [ExtracurricularController::class, 'detail']
)->name('Lms.StudentVicePrincipal.ExtracurricularManagement.Detail');

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
