<?php

use App\Http\Controllers\Administrator\AssessmentDebugController;
use App\Http\Controllers\Administrator\TkaTryout\TkaTryoutPeriodController;
use App\Http\Controllers\Administrator\TkaTryout\TkaTryoutPeriodSchoolOverrideController;
use App\Http\Controllers\Administrator\TkaTryout\TkaTryoutPeriodSessionController;
use App\Http\Controllers\Administrator\TkaTryout\TkaTryoutPeriodSubjectController;
use App\Http\Controllers\Administrator\UserAnalyticsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\SyllabusController;
use Illuminate\Support\Facades\Route;

// STUDENT TKA PRACTICE TEST
// views
Route::get('/lms/{role}/tka-simulation', [LibraryController::class, 'studentTKASubjectList'])->name('Student.TkaSubjectList.View');
Route::get('/lms/{role}/tka-simulation/class/{kelasId}/subject/{mapelId}/practice-test', [LibraryController::class, 'studentTKAPracticeTest'])->name('Student.TkaPracticeTest.View');
Route::get('/lms/{role}/tka-simulation/class/{kelasId}/subject/{mapelId}/practice-test/form', [LibraryController::class, 'studentTKAPracticeTestForm'])->name('Student.TkaPracticeTest.Form');

// start & restart practice test
Route::post('/lms/{role}/tka-simulation/class/{kelasId}/subject/{mapelId}/practice-test/start', [LibraryController::class, 'studentTkaStartPractice'])->name('Student.TkaPracticeTest.Start');
Route::post('/lms/{role}/tka-simulation/class/{kelasId}/subject/{mapelId}/practice-test/restart', [LibraryController::class, 'studentTkaResstartPractice'])->name('Student.TkaPracticeTest.Restart');

// submit answer
Route::post('/lms/{role}/tka-simulation/class/{kelasId}/subject/{mapelId}/practice-test/answer/{attemptId}', [LibraryController::class, 'studentTkaSubmitAnswer'])->name('Student.TkaPracticeTest.Answer');

// paginate
Route::get('/lms/{role}/tka-simulation/subject-list/paginate', [LibraryController::class, 'paginateTKASubject'])->name('Student.TkaPracticeTest.SubjectList.Paginate');

// OFFICES DASHBOARD
Route::get('/lms/{role}/dashboard', [DashboardController::class, 'index'])->name('Lms.Office.Dashboard.View');

// ROUTES TKA TRYOUT MANAGEMENT (Administrator)
// tka tryout period maangement
// views
Route::get('/lms/{role}/tka-tryout-management', [TkaTryoutPeriodController::class, 'index'])->name('Lms.Office.TkaTryoutPeriodManagement.View');
Route::get('/lms/{role}/tka-tryout-management/period-form', [TkaTryoutPeriodController::class, 'viewTkaTryoutForm'])->name('Lms.Office.TkaTryoutPeriodForm.View');
Route::get('/lms/{role}/tka-tryout-management/period-school-override-form/{periodId}', [TkaTryoutPeriodSchoolOverrideController::class, 'index'])->name('Lms.Office.TkaTryoutPeriodSchoolOverrideForm.View');

// load kpi & filter
Route::get('/lms/{role}/tka-tryout-management/load-filter', [TkaTryoutPeriodController::class, 'loadFilter'])->name('Lms.Office.TkaTryoutPeriodManagment.LoadFilter');
Route::get('/lms/{role}/tka-tryout-management/load-kpi', [TkaTryoutPeriodController::class, 'loadKpi'])->name('Lms.Office.TkaTryoutPeriodManagment.LoadKpi');

// crud
Route::post('/lms/{role}/tka-tryout-management/submit-periode-form', [TkaTryoutPeriodController::class, 'tkaTryoutPeriodCreate'])->name('Lms.Office.TkaTryoutPeriodSubmitForm');
Route::post('/lms/{role}/tka-tryout-management/update-periode-form/{periodId}', [TkaTryoutPeriodController::class, 'tkaTryoutPeriodUpdate'])->name('Lms.Office.TkaTryoutPeriodUpdateForm');
Route::post('/lms/{role}/tka-tryout-management/period-school-override-form/{periodOverrideId}/submit', [TkaTryoutPeriodSchoolOverrideController::class, 'tkaTryoutPeriodSchoolOverrideCreate'])->name('Lms.Office.TkaTryoutPeriodSchoolOverrideForm.Create');
Route::post('/lms/{role}/tka-tryout-management/period-school-override-form/{periodOverrideId}/{schoolOverrideId}/update', [TkaTryoutPeriodSchoolOverrideController::class, 'tkaTryoutPeriodSchoolOverrideUpdate'])->name('Lms.Office.TkaTryoutPeriodSchoolOverrideForm.Submit');

// paginate
Route::get('/lms/{role}/tka-tryout-management/paginate', [TkaTryoutPeriodController::class, 'paginateTkaTryoutPeriod'])->name('Lms.Office.TkaTryoutPeriodManagment.Paginate');
Route::get('/lms/{role}/tka-tryout-management/period-school-override/{periodOverrideId}/paginate', [TkaTryoutPeriodController::class, 'paginateTkaTryoutPeriodSchoolOVerride'])->name('Lms.Office.TkaTryoutPeriodSchoolOverride.Paginate');

// tka tryout period session
// views (defaul & override)
Route::get('/lms/{role}/tka-tryout-management/session-form/{periodId}', [TkaTryoutPeriodSessionController::class, 'index'])->name('Lms.Office.TkaTryoutSessionForm.View');
Route::get('/lms/{role}/tka-tryout-management/session-form/{periodOverrideId}/school-override', [TkaTryoutPeriodSessionController::class, 'indexOverride'])->name('Lms.Office.TkaTryoutSessionFormSchoolOverride.View');
Route::get('/lms/{role}/tka-tryout-management/session-form/{periodId}/manage-students/{sessionId}', [TkaTryoutPeriodSessionController::class, 'manageStudent'])->name('Lms.Office.TkaTryoutSessionManageStudent.View');

// manage user form
Route::get('/lms/{role}/tka-tryout-management/session-form/{periodId}/manage-students/{sessionId}/{schoolId}/form', [TkaTryoutPeriodSessionController::class, 'manageStudentForm'])->name('Lms.Office.TkaTryoutSessionManageStudent.Form');

// crud
Route::post('/lms/{role}/tka-tryout-management/session-form/{periodId}/submit', [TkaTryoutPeriodSessionController::class, 'tkaTryoutPeriodSessionCreate'])->name('Lms.Office.TkaTryoutSessionSubmitForm.Create');
Route::post('/lms/{role}/tka-tryout-management/session-form/{periodOverrideId}/override/submit', [TkaTryoutPeriodSessionController::class, 'tkaTryoutPeriodSessionOverrideCreate'])->name('Lms.Office.TkaTryoutOverrideSessionSubmitForm.Create');
Route::post('/lms/{role}/tka-tryout-management/session-form/{periodId}/{sessionId}/update', [TkaTryoutPeriodSessionController::class, 'tkaTryoutPeriodSessionUpdate'])->name('Lms.Office.TkaTryoutSessionSubmitForm.Update');
Route::post('/lms/{role}/tka-tryout-management/session-form/override/{periodOverrideId}/{sessionId}/update', [TkaTryoutPeriodSessionController::class, 'tkaTryoutPeriodSessionOverrideUpdate'])->name('Lms.Office.TkaTryoutSessionOverride.Update');
Route::post('/lms/{role}/tka-tryout-management/session-form/{periodId}/manage-students/{sessionId}/submit-form', [TkaTryoutPeriodSessionController::class, 'manageStudentSubmitForm'])->name('Lms.Office.TkaTryoutSessionManageStudent.SubmitForm');

// paginate
Route::get('/lms/{role}/tka-tryout-management/session/{periodId}/paginate', [TkaTryoutPeriodSessionController::class, 'paginateTkaTryoutPeriodSession'])->name('Lms.Office.TkaTryoutSession.Paginate');
Route::get('/lms/{role}/tka-tryout-management/session/override/{periodOverrideId}/paginate', [TkaTryoutPeriodSessionController::class, 'paginateTkaTryoutPeriodSessionOverride'])->name('Lms.Office.TkaTryoutSessionOverride.Paginate');

// tka tryout period subject
// views
Route::get('/lms/{role}/tka-tryout-management/assign-subject-form/{periodId}', [TkaTryoutPeriodSubjectController::class, 'index'])->name('Lms.Office.TkaTryoutSubjectForm.View');
Route::get('/lms/{role}/tka-tryout-management/assign-subject-form/{periodOverrideId}/school-override', [TkaTryoutPeriodSubjectController::class, 'indexOverride'])->name('Lms.Office.TkaTryoutSubjectFormSchoolOverride.View');

// form
Route::get('/lms/{role}/tka-tryout-management/assign-subject-form/{periodId}/load-form', [TkaTryoutPeriodSubjectController::class, 'tkaTryoutPeriodSubjectForm'])->name('Lms.Office.TkaTryoutSubjectForm.LoadData');

// crud
Route::post('/lms/{role}/tka-tryout-management/assign-subject-form/{periodId}/submit-form', [TkaTryoutPeriodSubjectController::class, 'tkaTryoutPeriodSubjectSubmitForm'])->name('Lms.Office.TkaTryoutSubjectForm.Submit');
Route::post('/lms/{role}/tka-tryout-management/assign-subject-form/{periodId}/{tkaTryoutSubjectId}/edit', [TkaTryoutPeriodSubjectController::class, 'tkaTryoutPeriodSubjectEdit'])->name('Lms.Office.TkaTryoutSubject.Edit');
Route::put('/lms/{role}/tka-tryout-management/assign-subject-form/{periodId}/{tkaTryoutSubjectId}/activate', [TkaTryoutPeriodSubjectController::class, 'tkaTryoutPeriodSubjectOverrideActivate'])->name('Lms.Office.TkaTryoutSubject.Activate');
Route::put('/lms/{role}/tka-tryout-management/assign-subject-form/override/{periodOverrideId}/{tkaTryoutSubjectId}/activate', [TkaTryoutPeriodSubjectController::class, 'tkaTryoutPeriodSubjectOverrideActivate'])->name('Lms.Office.TkaTryoutSubjectOverride.Activate');

// paginate
Route::get('/lms/{role}/tka-tryout-management/subject-list/{periodId}/paginate', [TkaTryoutPeriodSubjectController::class, 'paginateTkaTryoutPeriodSubject'])->name('Lms.Office.TkaTryoutSubject.Paginate');
Route::get('/lms/{role}/tka-tryout-management/subject-list/override/{periodOverrideId}/paginate', [TkaTryoutPeriodSubjectController::class, 'paginateTkaTryoutPeriodOverrideSubject'])->name('Lms.Office.TkaTryoutOverrideSubject.Paginate');

// =========================================================
// SITE ADMIN ANALYTICS DASHBOARD & TRACKING APIs
// =========================================================
Route::get('/lms/{role}/analytics', [UserAnalyticsController::class, 'index'])->name('Lms.Office.Analytics.View');

Route::prefix('administrator/analytics')->group(function () {
    Route::get('/kpi', [UserAnalyticsController::class, 'kpiSummary'])->name('Admin.Analytics.Kpi');
    Route::get('/charts', [UserAnalyticsController::class, 'chartData'])->name('Admin.Analytics.Charts');
    Route::get('/logs', [UserAnalyticsController::class, 'activityLogs'])->name('Admin.Analytics.Logs');
    Route::get('/schools', [UserAnalyticsController::class, 'schoolLeaderboard'])->name('Admin.Analytics.Schools');
    Route::get('/live-users', [UserAnalyticsController::class, 'liveOnlineUsers'])->name('Admin.Analytics.LiveUsers');
});

// =========================================================
// SITE ADMIN ASSESSMENT & ATTEMPTS DEBUGGER
// =========================================================
Route::get('/lms/{role}/assessment-debug', [AssessmentDebugController::class, 'index'])->name('Lms.Office.AssessmentDebug.View');

Route::prefix('administrator/assessment-debug')->group(function () {
    Route::get('/data', [AssessmentDebugController::class, 'getAttempts'])->name('Admin.AssessmentDebug.Data');
    Route::get('/assessments', [AssessmentDebugController::class, 'getAssessments'])->name('Admin.AssessmentDebug.Assessments');
    Route::get('/assessment/{id}', [AssessmentDebugController::class, 'getAssessmentDetail'])->name('Admin.AssessmentDebug.AssessmentDetail');
    Route::get('/assessment/{assessmentId}/student/{studentId}', [AssessmentDebugController::class, 'getStudentExamDetail'])->name('Admin.AssessmentDebug.StudentExamDetail');
    Route::post('/assessment/{id}/recount', [AssessmentDebugController::class, 'recountAssessment'])->name('Admin.AssessmentDebug.RecountAssessment');
    Route::post('/recount-user', [AssessmentDebugController::class, 'recountUser'])->name('Admin.AssessmentDebug.RecountUser');
    Route::post('/recount-selected', [AssessmentDebugController::class, 'recountSelectedAttempts'])->name('Admin.AssessmentDebug.RecountSelected');
    Route::get('/cache/status', [AssessmentDebugController::class, 'getCacheStatus'])->name('Admin.AssessmentDebug.CacheStatus');
    Route::post('/cache/warm/{id}', [AssessmentDebugController::class, 'warmCache'])->name('Admin.AssessmentDebug.WarmCache');
    Route::post('/cache/clear/{id}', [AssessmentDebugController::class, 'clearCache'])->name('Admin.AssessmentDebug.ClearCache');
    Route::post('/cache/clear-db', [AssessmentDebugController::class, 'clearDatabaseCache'])->name('Admin.AssessmentDebug.ClearDbCache');
    Route::post('/cache/purge-global', [AssessmentDebugController::class, 'purgeGlobalCache'])->name('Admin.AssessmentDebug.PurgeGlobalCache');
    Route::post('/{id}/unlock', [AssessmentDebugController::class, 'unlock'])->name('Admin.AssessmentDebug.Unlock');
    Route::post('/{id}/recount', [AssessmentDebugController::class, 'recountAttempt'])->name('Admin.AssessmentDebug.RecountAttempt');
    Route::post('/{id}/update-status', [AssessmentDebugController::class, 'updateStatus'])->name('Admin.AssessmentDebug.UpdateStatus');
    Route::post('/{id}/update-attempts', [AssessmentDebugController::class, 'updateTabSwitchCount'])->name('Admin.AssessmentDebug.UpdateAttempts');
    Route::post('/{id}/update', [AssessmentDebugController::class, 'updateAttempt'])->name('Admin.AssessmentDebug.Update');
    Route::post('/assessment/{assessmentId}/student/{studentId}/question/{questionId}/update', [AssessmentDebugController::class, 'updateQuestionGradeAndAnswer'])->name('Admin.AssessmentDebug.UpdateQuestionGradeAndAnswer');
    Route::post('/assessment/{assessmentId}/student/{studentId}/update-exam-grades', [AssessmentDebugController::class, 'updateExamGradesAndAnswers'])->name('Admin.AssessmentDebug.UpdateExamGradesAndAnswers');
    Route::post('/attempt/{id}/update-exam-grades', [AssessmentDebugController::class, 'updateAttemptExamGrades'])->name('Admin.AssessmentDebug.UpdateAttemptExamGrades');
    Route::delete('/{id}', [AssessmentDebugController::class, 'deleteAttempt'])->name('Admin.AssessmentDebug.Delete');
});

// =========================================================
// ROUTES SYLLABUS-SERVICES
// =========================================================
Route::get('/syllabus/curriculum', [SyllabusController::class, 'curriculumView'])->name('Kurikulum.View');
Route::get('/syllabus/curriculum/{curriculumName}/{curriculumId}/fase', [SyllabusController::class, 'faseView'])->name('Fase.View');
Route::get('/syllabus/curriculum/{curriculumName}/{curriculumId}/{faseId}/kelas', [SyllabusController::class, 'kelasView'])->name('Kelas.View');
Route::get('/syllabus/curriculum/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/mapel', [SyllabusController::class, 'mapelView'])->name('Mapel.View');
Route::get('/syllabus/curriculum/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/{mapelId}/bab', [SyllabusController::class, 'babView'])->name('Bab.View');
Route::get('/syllabus/curriculum/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/{mapelId}/{babId}/sub-bab', [SyllabusController::class, 'subBabView'])->name('SubBab.View');

// CRUD Kurikulum
Route::post('/syllabus/curriculum/store', [SyllabusController::class, 'curiculumStore'])->name('Kurikulum.Store');
Route::post('/syllabus/curriculum/edit/{curriculumId}', [SyllabusController::class, 'curiculumEdit'])->name('Kurikulum.Edit');

// CRUD Fase
Route::post('/syllabus/{curriculumId}/fase/store', [SyllabusController::class, 'faseStore'])->name('Fase.Store');
Route::post('/syllabus/curriculum/fase/edit/{curriculumId}/{faseId}', [SyllabusController::class, 'faseEdit'])->name('Fase.Edit');

// CRUD Kelas
Route::post('/syllabus/{curriculumId}/{faseId}/kelas/store', [SyllabusController::class, 'kelasStore'])->name('Kelas.Store');
Route::post('/syllabus/curriculum/kelas/edit/{curriculumId}/{faseId}/{kelasId}', [SyllabusController::class, 'kelasEdit'])->name('Kelas.Edit');

// CRUD Mapel
Route::post('/syllabus/{curriculumId}/{faseId}/{kelasId}/mapel/store', [SyllabusController::class, 'mapelStore'])->name('Mapel.Store');
Route::post('/syllabus/curriculum/mapel/edit/{curriculumId}/{faseId}/{kelasId}/{mapelId}', [SyllabusController::class, 'mapelEdit'])->name('Mapel.Edit');
Route::put('/syllabus/curriculum/mapel/activate/{mapelId}', [SyllabusController::class, 'mapelActivate'])->name('Mapel.Activate');

// CRUD Bab
Route::post('/syllabus/{curriculumId}/{faseId}/{kelasId}/{mapelId}/bab/store', [SyllabusController::class, 'babStore'])->name('Bab.Store');
Route::post('/syllabus/curriculum/bab/edit/{curriculumId}/{faseId}/{kelasId}/{mapelId}/{babId}', [SyllabusController::class, 'babEdit'])->name('Bab.Edit');
Route::put('/syllabus/curriculum/bab/activate/{babId}', [SyllabusController::class, 'babActivate'])->name('Bab.Activate');

// CRUD Sub Bab
Route::post('/syllabus/{curriculumId}/{faseId}/{kelasId}/{mapelId}/bab/sub-bab/store', [SyllabusController::class, 'subBabStore'])->name('SubBab.Store');
Route::post('/syllabus/curriculum/sub-bab/edit/{curriculumId}/{faseId}/{kelasId}/{mapelId}/{babId}/{subBabId}', [SyllabusController::class, 'subBabEdit'])->name('SubBab.Edit');
Route::put('/syllabus/curriculum/sub-bab/activate/{subBabId}', [SyllabusController::class, 'subBabActivate'])->name('SubBab.Activate');
