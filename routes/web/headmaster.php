<?php

use App\Http\Controllers\Foundation\FoundationDashboardController;
use App\Http\Controllers\Foundation\SchoolUserController;
use App\Http\Controllers\Foundation\StudentReflectionController;
use App\Http\Controllers\Foundation\TeacherPerformanceController;
use App\Http\Controllers\HeadmasterController;
use Illuminate\Support\Facades\Route;

// BLOK KEPALA SEKOLAH
Route::get('/lms/{role}/{schoolName}/{schoolId}/headmaster/dashboard', [HeadmasterController::class, 'index'])->name('Lms.Headmaster.Dashboard.View');

// Monitoring Laporan Akademik & Aktivitas
Route::get('/{role}/{schoolName}/{schoolId}/headmaster/laporan-akademik', [HeadmasterController::class, 'laporanAkademik'])->name('Lms.Headmaster.Academic.Report');
Route::get('/{role}/{schoolName}/{schoolId}/headmaster/aktivitas-guru', [HeadmasterController::class, 'aktivitasGuru'])->name('Lms.Headmaster.Teacher.Activity');

Route::get('/{role}/{schoolName}/{schoolId}/headmaster/schedule', [HeadmasterController::class, 'scheduleView'])->name('Lms.Headmaster.Schedule.View');
Route::get('/{role}/{schoolName}/{schoolId}/headmaster/schedule/get-data', [HeadmasterController::class, 'getScheduleDataAjax'])->name('Lms.Headmaster.Schedule.Data');
Route::post('/{role}/{schoolName}/{schoolId}/headmaster/schedule/save', [HeadmasterController::class, 'saveSchedule'])->name('Lms.Headmaster.Schedule.Save');

Route::get('/{role}/{schoolName}/{schoolId}/calendar', [HeadmasterController::class, 'CalendarView'])->name('Lms.Headmaster.Calendar.View');
Route::post('/{role}/{schoolName}/{schoolId}/calendar/save', [HeadmasterController::class, 'saveCalendarData'])->name('Lms.Headmaster.Calendar.Save');

// Agenda Harian Guru
Route::get('/{role}/{schoolName}/{schoolId}/headmaster/agenda-harian-guru', [HeadmasterController::class, 'teacherDailyAgendaManagement'])->name('Lms.Headmaster.TeacherDailyAgernda.Monitoring');
Route::get('/{role}/{schoolName}/{schoolId}/headmaster/agenda-harian-guru/histori', [HeadmasterController::class, 'teacherDailyAgendaHistory'])->name('Lms.Headmaster.TeacherDailyAgernda.Monitoring.History');

// crud
Route::post('/{role}/{schoolName}/{schoolId}/headmaster/agenda-harian-guru/histori/feedback-store', [HeadmasterController::class, 'teacherDailyAgendaHistoryFeedbackStore'])->name('Lms.Headmaster.TeacherDailyAgernda.Monitoring.History.FeedbackStore');

// paginate agenda harian guru
Route::get('/{role}/{schoolName}/{schoolId}/headmaster/agenda-harian-guru/paginate', [HeadmasterController::class, 'paginateTeacherDailyAgendaManagement'])->name('Lms.Headmaster.TeacherDailyAgernda.Monitoring.Paginate');
Route::get('/{role}/{schoolName}/{schoolId}/headmaster/agenda-harian-guru/histori/paginate', [HeadmasterController::class, 'paginateTeacherDailyAgendaHistory'])->name('Lms.Headmaster.TeacherDailyAgernda.Monitoring.History.Paginate');

    // =========================================================
// POLLING MANAGEMENT (KEPALA SEKOLAH)
// =========================================================
Route::get('/lms/kepsek/polling', [HeadmasterController::class, 'pollingIndex'])->name('Kepsek.Polling.Index');
Route::post('/lms/kepsek/polling/store', [HeadmasterController::class, 'pollingStore'])->name('Kepsek.Polling.Store');
Route::delete('/lms/kepsek/polling/destroy/{id}', [HeadmasterController::class, 'pollingDestroy'])->name('Kepsek.Polling.Destroy');
Route::get('/lms/kepsek/polling/{id}/breakdown', [HeadmasterController::class, 'getPollingBreakdown']);

// ROUTES FOUNDATION (yayasan)
// view
Route::get('/lms/{role}/foundation/dashboard/{foundationId?}', [FoundationDashboardController::class, 'index'])->name('Lms.Foundation.Dashboard.View');
Route::get('/lms/{role}/foundation/school-teacher-performance/{foundationId?}', [TeacherPerformanceController::class, 'teacherPerformanceView'])->name('Lms.Foundation.TeacherPerformance.View');
Route::get('/lms/{role}/foundation/student-reflection/{foundationId?}', [StudentReflectionController::class, 'studentReflectionView'])->name('Lms.Foundation.StudentReflection.View');
Route::get('/lms/{role}/foundation/school-users/{foundationId?}', [SchoolUserController::class, 'schoolUserView'])->name('Lms.Foundation.SchoolUsers.View');

// load acamdeic year (tahun ajaran sekolah)
Route::get('/lms/{role}/foundation/school-teacher-performance/load-academic-years/{foundationId?}', [TeacherPerformanceController::class, 'loadAcademicYears'])->name('Lms.Foundation.SchoolTeacherPerformance.AcademicYears.Load');

// load reflection year
Route::get('/lms/{role}/foundation/student-reflection/load-reflection-years/{foundationId}', [StudentReflectionController::class, 'loadReflectionYears'])->name('Lms.Foundation.StudentReflection.AcademicYears.Load');

// KPI
Route::get('/lms/{role}/foundation/dashboard-kpi/{foundationId?}', [FoundationDashboardController::class, 'dashboardKPI'])->name('Lms.Foundation.Dashboard.Kpi');
Route::get('/lms/{role}/foundation/school-teacher-performance-kpi/{foundationId?}', [TeacherPerformanceController::class, 'schoolTeacherPerformanceKPI'])->name('Lms.Foundation.TeacherPerformance.Kpi');
Route::get('/lms/{role}/foundation/student-reflection-kpi/{foundationId?}', [StudentReflectionController::class, 'studentReflectionKPI'])->name('Lms.Foundation.StudentReflection.Kpi');
Route::get('/lms/{role}/foundation/school-user-kpi/{foundationId?}', [SchoolUserController::class, 'schoolUserKPI'])->name('Lms.Foundation.SchoolUser.Kpi');

// load charts
Route::get('/lms/{role}/foundation/school-teacher-performance/load-chart/{foundationId?}', [TeacherPerformanceController::class, 'schoolTeacherPerformanceChart'])->name('Lms.Foundation.TeacherPerformance.Chart');
Route::get('/lms/{role}/foundation/student-reflection/load-emotion-overview/{foundationId?}', [StudentReflectionController::class, 'loadEmotionOverview'])->name('Lms.Foundation.StudentReflection.EmotionOverview.Load');
Route::get('/lms/{role}/foundation/student-reflection/load-reflection-trend/{foundationId?}', [StudentReflectionController::class, 'loadReflectionTrend'])->name('Lms.Foundation.StudentReflection.ReflectionTrend.Load');
Route::get('/lms/{role}/foundation/student-reflection/load-emotion-trend/{foundationId?}', [StudentReflectionController::class, 'loadEmotionTrend'])->name('Lms.Foundation.StudentReflection.EmotionTrend.Load');
Route::get('/lms/{role}/foundation/school-user/chart-by-school/{foundationId?}', [SchoolUserController::class, 'schoolUserChartBySchool'])->name('Lms.Foundation.SchoolUser.ChartBySchool');
Route::get('/lms/{role}/foundation/school-user/chart-by-role/{foundationId?}', [SchoolUserController::class, 'schoolUserChartByRole'])->name('Lms.Foundation.SchoolUser.ChartByRole');
Route::get('/lms/{role}/foundation/school-user/chart-by-status/{foundationId?}', [SchoolUserController::class, 'schoolUserChartByStatus'])->name('Lms.Foundation.SchoolUser.ChartByStatus');

// filtering endpoint
Route::get('/lms/{role}/foundation/school-user/filter/{foundationId?}', [SchoolUserController::class, 'filterSchoolUser'])->name('Lms.Foundation.SchoolUser.Filter');

// paginate
Route::get('/lms/{role}/foundation/school-teacher-performance/paginate/{foundationId?}', [TeacherPerformanceController::class, 'paginateSchoolTeacherPerformance'])->name('Lms.Foundation.TeacherPerformance.Paginate');
Route::get('/lms/{role}/foundation/student-reflection/school-reflection-summary/paginate/{foundationId?}', [StudentReflectionController::class, 'paginateSchoolReflectionSummary'])->name('Lms.Foundation.StudentReflection.SchoolReflectionSummary.Paginate');
Route::get('/lms/{role}/foundation/student-reflection/school-reflection-attention/paginate/{foundationId?}', [StudentReflectionController::class, 'paginateSchoolReflectionAttention'])->name('Lms.Foundation.StudentReflection.SchoolReflectionAttention.Paginate');
