<?php

use App\Http\Controllers\Foundation\FoundationDashboardController;
use App\Http\Controllers\Foundation\SchoolUserController;
use App\Http\Controllers\Foundation\StudentReflectionController;
use App\Http\Controllers\Foundation\TeacherPerformanceController;
use App\Http\Controllers\HeadmasterController;
use Illuminate\Support\Facades\Route;

// BLOK KEPALA SEKOLAH
Route::get('/lms/{role}/{schoolName}/{schoolId}/headmaster/dashboard', [HeadmasterController::class, 'index'])->name('lms.headmaster.dashboard.view');

// Monitoring Laporan Akademik & Aktivitas
Route::get('/{role}/{schoolName}/{schoolId}/headmaster/laporan-akademik', [HeadmasterController::class, 'laporanAkademik'])->name('lms.headmaster.academic.report');
Route::get('/{role}/{schoolName}/{schoolId}/headmaster/aktivitas-guru', [HeadmasterController::class, 'aktivitasGuru'])->name('lms.headmaster.teacher.activity');

Route::get('/{role}/{schoolName}/{schoolId}/headmaster/schedule', [HeadmasterController::class, 'scheduleView'])->name('lms.headmaster.schedule.view');
Route::get('/{role}/{schoolName}/{schoolId}/headmaster/schedule/get-data', [HeadmasterController::class, 'getScheduleDataAjax'])->name('lms.headmaster.schedule.data');
Route::post('/{role}/{schoolName}/{schoolId}/headmaster/schedule/save', [HeadmasterController::class, 'saveSchedule'])->name('lms.headmaster.schedule.save');

Route::get('/{role}/{schoolName}/{schoolId}/calendar', [HeadmasterController::class, 'CalendarView'])->name('lms.headmaster.calendar.view');
Route::post('/{role}/{schoolName}/{schoolId}/calendar/save', [HeadmasterController::class, 'saveCalendarData'])->name('lms.headmaster.calendar.save');

// Agenda Harian Guru
Route::get('/{role}/{schoolName}/{schoolId}/headmaster/agenda-harian-guru', [HeadmasterController::class, 'teacherDailyAgendaManagement'])->name('lms.headmaster.teacherDailyAgernda.monitoring');
Route::get('/{role}/{schoolName}/{schoolId}/headmaster/agenda-harian-guru/histori', [HeadmasterController::class, 'teacherDailyAgendaHistory'])->name('lms.headmaster.teacherDailyAgernda.monitoring.history');

// crud
Route::post('/{role}/{schoolName}/{schoolId}/headmaster/agenda-harian-guru/histori/feedback-store', [HeadmasterController::class, 'teacherDailyAgendaHistoryFeedbackStore'])->name('lms.headmaster.teacherDailyAgernda.monitoring.history.feedback-store');

// paginate agenda harian guru
Route::get('/{role}/{schoolName}/{schoolId}/headmaster/agenda-harian-guru/paginate', [HeadmasterController::class, 'paginateTeacherDailyAgendaManagement'])->name('lms.headmaster.teacherDailyAgernda.monitoring.paginate');
Route::get('/{role}/{schoolName}/{schoolId}/headmaster/agenda-harian-guru/histori/paginate', [HeadmasterController::class, 'paginateTeacherDailyAgendaHistory'])->name('lms.headmaster.teacherDailyAgernda.monitoring.history.paginate');

    // =========================================================
// POLLING MANAGEMENT (KEPALA SEKOLAH)
// =========================================================
Route::get('/lms/kepsek/polling', [HeadmasterController::class, 'pollingIndex'])->name('kepsek.polling.index');
Route::post('/lms/kepsek/polling/store', [HeadmasterController::class, 'pollingStore'])->name('kepsek.polling.store');
Route::delete('/lms/kepsek/polling/destroy/{id}', [HeadmasterController::class, 'pollingDestroy'])->name('kepsek.polling.destroy');
Route::get('/lms/kepsek/polling/{id}/breakdown', [HeadmasterController::class, 'getPollingBreakdown']);

// ROUTES FOUNDATION (yayasan)
// view
Route::get('/lms/{role}/foundation/dashboard/{foundationId?}', [FoundationDashboardController::class, 'index'])->name('lms.foundation.dashboard.view');
Route::get('/lms/{role}/foundation/school-teacher-performance/{foundationId?}', [TeacherPerformanceController::class, 'teacherPerformanceView'])->name('lms.foundation.teacher-performance.view');
Route::get('/lms/{role}/foundation/student-reflection/{foundationId?}', [StudentReflectionController::class, 'studentReflectionView'])->name('lms.foundation.student-reflection.view');
Route::get('/lms/{role}/foundation/school-users/{foundationId?}', [SchoolUserController::class, 'schoolUserView'])->name('lms.foundation.school-users.view');

// load acamdeic year (tahun ajaran sekolah)
Route::get('/lms/{role}/foundation/school-teacher-performance/load-academic-years/{foundationId?}', [TeacherPerformanceController::class, 'loadAcademicYears'])->name('lms.foundation.school-teacher-performance.academic-years.load');

// load reflection year
Route::get('/lms/{role}/foundation/student-reflection/load-reflection-years/{foundationId}', [StudentReflectionController::class, 'loadReflectionYears'])->name('lms.foundation.student-reflection.academic-years.load');

// KPI
Route::get('/lms/{role}/foundation/dashboard-kpi/{foundationId?}', [FoundationDashboardController::class, 'dashboardKPI'])->name('lms.foundation.dashboard.kpi');
Route::get('/lms/{role}/foundation/school-teacher-performance-kpi/{foundationId?}', [TeacherPerformanceController::class, 'schoolTeacherPerformanceKPI'])->name('lms.foundation.teacherPerformance.kpi');
Route::get('/lms/{role}/foundation/student-reflection-kpi/{foundationId?}', [StudentReflectionController::class, 'studentReflectionKPI'])->name('lms.foundation.studentReflection.kpi');
Route::get('/lms/{role}/foundation/school-user-kpi/{foundationId?}', [SchoolUserController::class, 'schoolUserKPI'])->name('lms.foundation.schoolUser.kpi');

// load charts
Route::get('/lms/{role}/foundation/school-teacher-performance/load-chart/{foundationId?}', [TeacherPerformanceController::class, 'schoolTeacherPerformanceChart'])->name('lms.foundation.teacherPerformance.chart');
Route::get('/lms/{role}/foundation/student-reflection/load-emotion-overview/{foundationId?}', [StudentReflectionController::class, 'loadEmotionOverview'])->name('lms.foundation.studentReflection.emotion-overview.load');
Route::get('/lms/{role}/foundation/student-reflection/load-reflection-trend/{foundationId?}', [StudentReflectionController::class, 'loadReflectionTrend'])->name('lms.foundation.studentReflection.reflection-trend.load');
Route::get('/lms/{role}/foundation/student-reflection/load-emotion-trend/{foundationId?}', [StudentReflectionController::class, 'loadEmotionTrend'])->name('lms.foundation.studentReflection.emotion-trend.load');
Route::get('/lms/{role}/foundation/school-user/chart-by-school/{foundationId?}', [SchoolUserController::class, 'schoolUserChartBySchool'])->name('lms.foundation.schoolUser.chartBySchool');
Route::get('/lms/{role}/foundation/school-user/chart-by-role/{foundationId?}', [SchoolUserController::class, 'schoolUserChartByRole'])->name('lms.foundation.schoolUser.chartByRole');
Route::get('/lms/{role}/foundation/school-user/chart-by-status/{foundationId?}', [SchoolUserController::class, 'schoolUserChartByStatus'])->name('lms.foundation.schoolUser.chartByStatus');

// filtering endpoint
Route::get('/lms/{role}/foundation/school-user/filter/{foundationId?}', [SchoolUserController::class, 'filterSchoolUser'])->name('lms.foundation.schoolUser.filter');

// paginate
Route::get('/lms/{role}/foundation/school-teacher-performance/paginate/{foundationId?}', [TeacherPerformanceController::class, 'paginateSchoolTeacherPerformance'])->name('lms.foundation.teacherPerformance.paginate');
Route::get('/lms/{role}/foundation/student-reflection/school-reflection-summary/paginate/{foundationId?}', [StudentReflectionController::class, 'paginateSchoolReflectionSummary'])->name('lms.foundation.studentReflection.school-reflection-summary.paginate');
Route::get('/lms/{role}/foundation/student-reflection/school-reflection-attention/paginate/{foundationId?}', [StudentReflectionController::class, 'paginateSchoolReflectionAttention'])->name('lms.foundation.studentReflection.school-reflection-attention.paginate');
