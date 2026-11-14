<?php

use App\Http\Controllers\ExtracurricularController;
use App\Http\Controllers\StudentVicePrincipalController;
use Illuminate\Support\Facades\Route;

// STUDENT VICE PRINCIPAL ROUTES
// dashboard
Route::get('lms/{role}/{schoolName}/{schoolId}/dashboard', [StudentVicePrincipalController::class, 'index'])->name('lms.student_vice_principal.dashboard.view');

// REFLECTION MANAGEMENT
// view
Route::get('/lms/{role}/{schoolName}/{schoolId}/reflection-management', [StudentVicePrincipalController::class, 'reflectionMaangement'])->name('lms.student_vice_principal.reflection_management.view');
Route::get('/lms/{role}/{schoolName}/{schoolId}/reflection-management/create', [StudentVicePrincipalController::class, 'createReflectionView'])->name('lms.student_vice_principal.create.reflection.view');
Route::get('/lms/{role}/{schoolName}/{schoolId}/reflection-management/history', [StudentVicePrincipalController::class, 'reflectionManagementHistoryView'])->name('lms.student_vice_principal.reflection_management_history.view');
Route::get('/lms/{role}/{schoolName}/{schoolId}/reflection-management/history-detail/{reflectionQuestionId}', [StudentVicePrincipalController::class, 'reflectionManagementHistoryDetailView'])->name('lms.student_vice_principal.reflection_management_history_detail.view');

// form submit
Route::get('/lms/{role}/{schoolName}/{schoolId}/reflection-management/form-submit', [StudentVicePrincipalController::class, 'reflectionManagementForm'])->name('lms.student_vice_principal.reflection.form');

// crud
Route::post('/lms/{role}/{schoolName}/{schoolId}/reflection-management/store', [StudentVicePrincipalController::class, 'reflectionManagementStore'])->name('lms.student_vice_principal.reflection.store');

// paginate
Route::get('/lms/{role}/{schoolName}/{schoolId}/dashboard/load-student-reflection-chart', [StudentVicePrincipalController::class, 'loadStudentReflectionChart'])->name('lms.student_vice_principal.dashboard.load_student_reflection_chart');
Route::get('/lms/{role}/{schoolName}/{schoolId}/reflection-management/history-recent', [StudentVicePrincipalController::class, 'paginateReflectionHistoryRecent'])->name('lms.student_vice_principal.reflection_management.history_recent');
Route::get('/lms/{role}/{schoolName}/{schoolId}/reflection-management/daily-reflection-live-preview', [StudentVicePrincipalController::class, 'paginateDailyReflectionLivePreview'])->name('lms.student_vice_principal.reflection_management.daily_reflection_live_preview');
Route::get('/lms/{role}/{schoolName}/{schoolId}/reflection-management/history/paginate', [StudentVicePrincipalController::class, 'paginateReflectionHistory'])->name('lms.student_vice_principal.reflection_management.history');
Route::get('/lms/{role}/{schoolName}/{schoolId}/reflection-management/history-detail/{reflectionQuestionId}/load-header', [StudentVicePrincipalController::class, 'loadReflectionDetailHeader'])->name('lms.student_vice_principal.reflection_management_history_detail.load_header');
Route::get('/lms/{role}/{schoolName}/{schoolId}/reflection-management/history-detail/{reflectionQuestionId}/load-summary', [StudentVicePrincipalController::class, 'loadReflectionDetailSummary'])->name('lms.student_vice_principal.reflection_management_history_detail.load_summary');
Route::get('/lms/{role}/{schoolName}/{schoolId}/reflection-management/history-detail/{reflectionQuestionId}/load-chart', [StudentVicePrincipalController::class, 'loadReflectionDetailChart'])->name('lms.student_vice_principal.reflection_management_history_detail.load_chart');
Route::get('/lms/{role}/{schoolName}/{schoolId}/reflection-management/history-detail/{reflectionQuestionId}/student-answer/paginate', [StudentVicePrincipalController::class, 'paginateReflectionStudentAnswer'])->name('lms.student_vice_principal.reflection_management_history_detail.student_answer.paginate');

// =========================================
// EXTRACURRICULAR MANAGEMENT
// =========================================

// LIST
Route::get(
    '/lms/{role}/{schoolName}/{schoolId}/extracurricular-management',
    [ExtracurricularController::class, 'index']
)->name('lms.student_vice_principal.extracurricular_management.view');

// DOWNLOAD TEMPLATE
Route::get(
    '/lms/{role}/{schoolName}/{schoolId}/extracurricular-management/download-template',
    [ExtracurricularController::class, 'downloadTemplate']
)->name('lms.student_vice_principal.extracurricular_management.download_template');

// PAGINATION
Route::get(
    '/lms/{role}/{schoolName}/{schoolId}/extracurricular-management/paginate',
    [ExtracurricularController::class, 'paginate']
)->name('lms.student_vice_principal.extracurricular_management.paginate');

// KELENGKAPAN
Route::get(
    '/lms/{role}/{schoolName}/{schoolId}/extracurricular-management/kelengkapan',
    [ExtracurricularController::class, 'kelengkapanView']
)->name('lms.student_vice_principal.extracurricular_management.kelengkapan');

Route::get(
    '/lms/{role}/{schoolName}/{schoolId}/extracurricular-management/kelengkapan/{extracurricularId}',
    [ExtracurricularController::class, 'kelengkapanDetail']
)->name('lms.student_vice_principal.extracurricular_management.kelengkapan.detail');

// CRUD
Route::post(
    '/lms/{role}/{schoolName}/{schoolId}/extracurricular-management/store',
    [ExtracurricularController::class, 'store']
)->name('lms.student_vice_principal.extracurricular_management.store');

Route::post(
    '/lms/{role}/{schoolName}/{schoolId}/extracurricular-management/update/{id}',
    [ExtracurricularController::class, 'update']
)->name('lms.student_vice_principal.extracurricular_management.update');

Route::delete(
    '/lms/{role}/{schoolName}/{schoolId}/extracurricular-management/delete/{id}',
    [ExtracurricularController::class, 'destroy']
)->name('lms.student_vice_principal.extracurricular_management.delete');

// IMPORT
Route::post(
    '/lms/{role}/{schoolName}/{schoolId}/extracurricular-management/import',
    [ExtracurricularController::class, 'importExcel']
)->name('lms.student_vice_principal.extracurricular_management.import');

// BULK MEMBER
Route::post(
    '/lms/{role}/{schoolName}/{schoolId}/extracurricular-management/{extracurricularId}/upload-member',
    [ExtracurricularController::class, 'importMember']
)->name('lms.student_vice_principal.extracurricular_management.upload_member');

// BULK ATTENDANCE
Route::post(
    '/lms/{role}/{schoolName}/{schoolId}/extracurricular-management/{extracurricularId}/upload-attendance',
    [ExtracurricularController::class, 'uploadAttendance']
)->name('lms.student_vice_principal.extracurricular_management.upload_attendance');

// DETAIL (HARUS PALING BAWAH)
Route::get(
    '/lms/{role}/{schoolName}/{schoolId}/extracurricular-management/{extracurricularId}',
    [ExtracurricularController::class, 'detail']
)->name('lms.student_vice_principal.extracurricular_management.detail');

// / =========================================
// MEMBER
// =========================================

// Simpan 1 member
Route::post(
    '/lms/{role}/{schoolName}/{schoolId}/extracurricular-management/{extracurricularId}/member/store',
    [ExtracurricularController::class, 'storeMember']
)->name('lms.student_vice_principal.extracurricular_management.member.store');

// Import Member Excel
Route::post(
    '/lms/{role}/{schoolName}/{schoolId}/extracurricular-management/{extracurricularId}/member/import',
    [ExtracurricularController::class, 'importMember']
)->name('lms.student_vice_principal.extracurricular_management.member.import');

// Download Template Member
Route::get(
    '/lms/{role}/{schoolName}/{schoolId}/extracurricular-management/{extracurricularId}/member/download-template',
    [ExtracurricularController::class, 'downloadMemberTemplate']
)->name('lms.student_vice_principal.extracurricular_management.member.download_template');

// Hapus Member
Route::delete(
    '/lms/{role}/{schoolName}/{schoolId}/extracurricular-management/{extracurricularId}/member/{memberId}/delete',
    [ExtracurricularController::class, 'destroyMember']
)->name('lms.student_vice_principal.extracurricular_management.member.destroy');

// =========================================================
