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
Route::get('/lms/{role}/tka-simulation', [LibraryController::class, 'studentTKASubjectList'])->name('student.tka_subject_list.view');
Route::get('/lms/{role}/tka-simulation/class/{kelasId}/subject/{mapelId}/practice-test', [LibraryController::class, 'studentTKAPracticeTest'])->name('student.tka_practice_test.view');
Route::get('/lms/{role}/tka-simulation/class/{kelasId}/subject/{mapelId}/practice-test/form', [LibraryController::class, 'studentTKAPracticeTestForm'])->name('student.tka_practice_test.form');

// start & restart practice test
Route::post('/lms/{role}/tka-simulation/class/{kelasId}/subject/{mapelId}/practice-test/start', [LibraryController::class, 'studentTkaStartPractice'])->name('student.tka_practice_test.start');
Route::post('/lms/{role}/tka-simulation/class/{kelasId}/subject/{mapelId}/practice-test/restart', [LibraryController::class, 'studentTkaResstartPractice'])->name('student.tka_practice_test.restart');

// submit answer
Route::post('/lms/{role}/tka-simulation/class/{kelasId}/subject/{mapelId}/practice-test/answer/{attemptId}', [LibraryController::class, 'studentTkaSubmitAnswer'])->name('student.tka_practice_test.answer');

// paginate
Route::get('/lms/{role}/tka-simulation/subject-list/paginate', [LibraryController::class, 'paginateTKASubject'])->name('student.tka_practice_test.subject_list.paginate');

// OFFICES DASHBOARD
Route::get('/lms/{role}/dashboard', [DashboardController::class, 'index'])->name('lms.office.dashboard.view');

// ROUTES TKA TRYOUT MANAGEMENT (Administrator)
// tka tryout period maangement
// views
Route::get('/lms/{role}/tka-tryout-management', [TkaTryoutPeriodController::class, 'index'])->name('lms.office.tka_tryout_period_management.view');
Route::get('/lms/{role}/tka-tryout-management/period-form', [TkaTryoutPeriodController::class, 'viewTkaTryoutForm'])->name('lms.office.tka_tryout_period_form.view');
Route::get('/lms/{role}/tka-tryout-management/period-school-override-form/{periodId}', [TkaTryoutPeriodSchoolOverrideController::class, 'index'])->name('lms.office.tka_tryout_period_school_override_form.view');

// load kpi & filter
Route::get('/lms/{role}/tka-tryout-management/load-filter', [TkaTryoutPeriodController::class, 'loadFilter'])->name('lms.office.tka_tryout_period_managment.load_filter');
Route::get('/lms/{role}/tka-tryout-management/load-kpi', [TkaTryoutPeriodController::class, 'loadKpi'])->name('lms.office.tka_tryout_period_managment.load_kpi');

// crud
Route::post('/lms/{role}/tka-tryout-management/submit-periode-form', [TkaTryoutPeriodController::class, 'tkaTryoutPeriodCreate'])->name('lms.office.tka_tryout_period_submit_form');
Route::post('/lms/{role}/tka-tryout-management/update-periode-form/{periodId}', [TkaTryoutPeriodController::class, 'tkaTryoutPeriodUpdate'])->name('lms.office.tka_tryout_period_update_form');
Route::post('/lms/{role}/tka-tryout-management/period-school-override-form/{periodOverrideId}/submit', [TkaTryoutPeriodSchoolOverrideController::class, 'tkaTryoutPeriodSchoolOverrideCreate'])->name('lms.office.tka_tryout_period_school_override_form.create');
Route::post('/lms/{role}/tka-tryout-management/period-school-override-form/{periodOverrideId}/{schoolOverrideId}/update', [TkaTryoutPeriodSchoolOverrideController::class, 'tkaTryoutPeriodSchoolOverrideUpdate'])->name('lms.office.tka_tryout_period_school_override_form.submit');

// paginate
Route::get('/lms/{role}/tka-tryout-management/paginate', [TkaTryoutPeriodController::class, 'paginateTkaTryoutPeriod'])->name('lms.office.tka_tryout_period_managment.paginate');
Route::get('/lms/{role}/tka-tryout-management/period-school-override/{periodOverrideId}/paginate', [TkaTryoutPeriodController::class, 'paginateTkaTryoutPeriodSchoolOVerride'])->name('lms.office.tka_tryout_period_school_override.paginate');

// tka tryout period session
// views (defaul & override)
Route::get('/lms/{role}/tka-tryout-management/session-form/{periodId}', [TkaTryoutPeriodSessionController::class, 'index'])->name('lms.office.tka_tryout_session_form.view');
Route::get('/lms/{role}/tka-tryout-management/session-form/{periodOverrideId}/school-override', [TkaTryoutPeriodSessionController::class, 'indexOverride'])->name('lms.office.tka_tryout_session_form_school_override.view');
Route::get('/lms/{role}/tka-tryout-management/session-form/{periodId}/manage-students/{sessionId}', [TkaTryoutPeriodSessionController::class, 'manageStudent'])->name('lms.office.tka_tryout_session_manage_student.view');

// manage user form
Route::get('/lms/{role}/tka-tryout-management/session-form/{periodId}/manage-students/{sessionId}/{schoolId}/form', [TkaTryoutPeriodSessionController::class, 'manageStudentForm'])->name('lms.office.tka_tryout_session_manage_student.form');

// crud
Route::post('/lms/{role}/tka-tryout-management/session-form/{periodId}/submit', [TkaTryoutPeriodSessionController::class, 'tkaTryoutPeriodSessionCreate'])->name('lms.office.tka_tryout_session_submit_form.create');
Route::post('/lms/{role}/tka-tryout-management/session-form/{periodOverrideId}/override/submit', [TkaTryoutPeriodSessionController::class, 'tkaTryoutPeriodSessionOverrideCreate'])->name('lms.office.tka_tryout_override_session_submit_form.create');
Route::post('/lms/{role}/tka-tryout-management/session-form/{periodId}/{sessionId}/update', [TkaTryoutPeriodSessionController::class, 'tkaTryoutPeriodSessionUpdate'])->name('lms.office.tka_tryout_session_submit_form.update');
Route::post('/lms/{role}/tka-tryout-management/session-form/override/{periodOverrideId}/{sessionId}/update', [TkaTryoutPeriodSessionController::class, 'tkaTryoutPeriodSessionOverrideUpdate'])->name('lms.office.tka_tryout_session_override.update');
Route::post('/lms/{role}/tka-tryout-management/session-form/{periodId}/manage-students/{sessionId}/submit-form', [TkaTryoutPeriodSessionController::class, 'manageStudentSubmitForm'])->name('lms.office.tka_tryout_session_manage_student.submit_form');

// paginate
Route::get('/lms/{role}/tka-tryout-management/session/{periodId}/paginate', [TkaTryoutPeriodSessionController::class, 'paginateTkaTryoutPeriodSession'])->name('lms.office.tka_tryout_session.paginate');
Route::get('/lms/{role}/tka-tryout-management/session/override/{periodOverrideId}/paginate', [TkaTryoutPeriodSessionController::class, 'paginateTkaTryoutPeriodSessionOverride'])->name('lms.office.tka_tryout_session_override.paginate');

// tka tryout period subject
// views
Route::get('/lms/{role}/tka-tryout-management/assign-subject-form/{periodId}', [TkaTryoutPeriodSubjectController::class, 'index'])->name('lms.office.tka_tryout_subject_form.view');
Route::get('/lms/{role}/tka-tryout-management/assign-subject-form/{periodOverrideId}/school-override', [TkaTryoutPeriodSubjectController::class, 'indexOverride'])->name('lms.office.tka_tryout_subject_form_school_override.view');

// form
Route::get('/lms/{role}/tka-tryout-management/assign-subject-form/{periodId}/load-form', [TkaTryoutPeriodSubjectController::class, 'tkaTryoutPeriodSubjectForm'])->name('lms.office.tka_tryout_subject_form.load_data');

// crud
Route::post('/lms/{role}/tka-tryout-management/assign-subject-form/{periodId}/submit-form', [TkaTryoutPeriodSubjectController::class, 'tkaTryoutPeriodSubjectSubmitForm'])->name('lms.office.tka_tryout_subject_form.submit');
Route::post('/lms/{role}/tka-tryout-management/assign-subject-form/{periodId}/{tkaTryoutSubjectId}/edit', [TkaTryoutPeriodSubjectController::class, 'tkaTryoutPeriodSubjectEdit'])->name('lms.office.tka_tryout_subject.edit');
Route::put('/lms/{role}/tka-tryout-management/assign-subject-form/{periodId}/{tkaTryoutSubjectId}/activate', [TkaTryoutPeriodSubjectController::class, 'tkaTryoutPeriodSubjectOverrideActivate'])->name('lms.office.tka_tryout_subject.activate');
Route::put('/lms/{role}/tka-tryout-management/assign-subject-form/override/{periodOverrideId}/{tkaTryoutSubjectId}/activate', [TkaTryoutPeriodSubjectController::class, 'tkaTryoutPeriodSubjectOverrideActivate'])->name('lms.office.tka_tryout_subject_override.activate');

// paginate
Route::get('/lms/{role}/tka-tryout-management/subject-list/{periodId}/paginate', [TkaTryoutPeriodSubjectController::class, 'paginateTkaTryoutPeriodSubject'])->name('lms.office.tka_tryout_subject.paginate');
Route::get('/lms/{role}/tka-tryout-management/subject-list/override/{periodOverrideId}/paginate', [TkaTryoutPeriodSubjectController::class, 'paginateTkaTryoutPeriodOverrideSubject'])->name('lms.office.tka_tryout_override_subject.paginate');

// =========================================================
// SITE ADMIN ANALYTICS DASHBOARD & TRACKING APIs
// =========================================================
Route::get('/lms/{role}/analytics', [UserAnalyticsController::class, 'index'])->name('lms.office.analytics.view');

Route::prefix('administrator/analytics')->group(function () {
    Route::get('/kpi', [UserAnalyticsController::class, 'kpiSummary'])->name('admin.analytics.kpi');
    Route::get('/charts', [UserAnalyticsController::class, 'chartData'])->name('admin.analytics.charts');
    Route::get('/logs', [UserAnalyticsController::class, 'activityLogs'])->name('admin.analytics.logs');
    Route::get('/schools', [UserAnalyticsController::class, 'schoolLeaderboard'])->name('admin.analytics.schools');
    Route::get('/live-users', [UserAnalyticsController::class, 'liveOnlineUsers'])->name('admin.analytics.live_users');
});

// =========================================================
// SITE ADMIN ASSESSMENT & ATTEMPTS DEBUGGER
// =========================================================
Route::get('/lms/{role}/assessment-debug', [AssessmentDebugController::class, 'index'])->name('lms.office.assessment_debug.view');

Route::prefix('administrator/assessment-debug')->group(function () {
    Route::get('/data', [AssessmentDebugController::class, 'getAttempts'])->name('admin.assessment_debug.data');
    Route::get('/assessments', [AssessmentDebugController::class, 'getAssessments'])->name('admin.assessment_debug.assessments');
    Route::get('/assessment/{id}', [AssessmentDebugController::class, 'getAssessmentDetail'])->name('admin.assessment_debug.assessment_detail');
    Route::get('/assessment/{assessmentId}/student/{studentId}', [AssessmentDebugController::class, 'getStudentExamDetail'])->name('admin.assessment_debug.student_exam_detail');
    Route::post('/assessment/{id}/recount', [AssessmentDebugController::class, 'recountAssessment'])->name('admin.assessment_debug.recount_assessment');
    Route::post('/recount-user', [AssessmentDebugController::class, 'recountUser'])->name('admin.assessment_debug.recount_user');
    Route::post('/recount-selected', [AssessmentDebugController::class, 'recountSelectedAttempts'])->name('admin.assessment_debug.recount_selected');
    Route::get('/cache/status', [AssessmentDebugController::class, 'getCacheStatus'])->name('admin.assessment_debug.cache_status');
    Route::post('/cache/warm/{id}', [AssessmentDebugController::class, 'warmCache'])->name('admin.assessment_debug.warm_cache');
    Route::post('/cache/clear/{id}', [AssessmentDebugController::class, 'clearCache'])->name('admin.assessment_debug.clear_cache');
    Route::post('/cache/clear-db', [AssessmentDebugController::class, 'clearDatabaseCache'])->name('admin.assessment_debug.clear_db_cache');
    Route::post('/cache/purge-global', [AssessmentDebugController::class, 'purgeGlobalCache'])->name('admin.assessment_debug.purge_global_cache');
    Route::post('/{id}/unlock', [AssessmentDebugController::class, 'unlock'])->name('admin.assessment_debug.unlock');
    Route::post('/{id}/recount', [AssessmentDebugController::class, 'recountAttempt'])->name('admin.assessment_debug.recount_attempt');
    Route::post('/{id}/update-status', [AssessmentDebugController::class, 'updateStatus'])->name('admin.assessment_debug.update_status');
    Route::post('/{id}/update-attempts', [AssessmentDebugController::class, 'updateTabSwitchCount'])->name('admin.assessment_debug.update_attempts');
    Route::post('/{id}/update', [AssessmentDebugController::class, 'updateAttempt'])->name('admin.assessment_debug.update');
    Route::post('/assessment/{assessmentId}/student/{studentId}/question/{questionId}/update', [AssessmentDebugController::class, 'updateQuestionGradeAndAnswer'])->name('admin.assessment_debug.update_question_grade_and_answer');
    Route::post('/assessment/{assessmentId}/student/{studentId}/update-exam-grades', [AssessmentDebugController::class, 'updateExamGradesAndAnswers'])->name('admin.assessment_debug.update_exam_grades_and_answers');
    Route::post('/attempt/{id}/update-exam-grades', [AssessmentDebugController::class, 'updateAttemptExamGrades'])->name('admin.assessment_debug.update_attempt_exam_grades');
    Route::delete('/{id}', [AssessmentDebugController::class, 'deleteAttempt'])->name('admin.assessment_debug.delete');
});

// =========================================================
// ROUTES SYLLABUS-SERVICES
// =========================================================
Route::get('/syllabus/curriculum', [SyllabusController::class, 'curriculumView'])->name('kurikulum.view');
Route::get('/syllabus/curriculum/{curriculumName}/{curriculumId}/fase', [SyllabusController::class, 'faseView'])->name('fase.view');
Route::get('/syllabus/curriculum/{curriculumName}/{curriculumId}/{faseId}/kelas', [SyllabusController::class, 'kelasView'])->name('kelas.view');
Route::get('/syllabus/curriculum/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/mapel', [SyllabusController::class, 'mapelView'])->name('mapel.view');
Route::get('/syllabus/curriculum/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/{mapelId}/bab', [SyllabusController::class, 'babView'])->name('bab.view');
Route::get('/syllabus/curriculum/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/{mapelId}/{babId}/sub-bab', [SyllabusController::class, 'subBabView'])->name('sub_bab.view');

// CRUD Kurikulum
Route::post('/syllabus/curriculum/store', [SyllabusController::class, 'curiculumStore'])->name('kurikulum.store');
Route::post('/syllabus/curriculum/edit/{curriculumId}', [SyllabusController::class, 'curiculumEdit'])->name('kurikulum.edit');

// CRUD Fase
Route::post('/syllabus/{curriculumId}/fase/store', [SyllabusController::class, 'faseStore'])->name('fase.store');
Route::post('/syllabus/curriculum/fase/edit/{curriculumId}/{faseId}', [SyllabusController::class, 'faseEdit'])->name('fase.edit');

// CRUD Kelas
Route::post('/syllabus/{curriculumId}/{faseId}/kelas/store', [SyllabusController::class, 'kelasStore'])->name('kelas.store');
Route::post('/syllabus/curriculum/kelas/edit/{curriculumId}/{faseId}/{kelasId}', [SyllabusController::class, 'kelasEdit'])->name('kelas.edit');

// CRUD Mapel
Route::post('/syllabus/{curriculumId}/{faseId}/{kelasId}/mapel/store', [SyllabusController::class, 'mapelStore'])->name('mapel.store');
Route::post('/syllabus/curriculum/mapel/edit/{curriculumId}/{faseId}/{kelasId}/{mapelId}', [SyllabusController::class, 'mapelEdit'])->name('mapel.edit');
Route::put('/syllabus/curriculum/mapel/activate/{mapelId}', [SyllabusController::class, 'mapelActivate'])->name('mapel.activate');

// CRUD Bab
Route::post('/syllabus/{curriculumId}/{faseId}/{kelasId}/{mapelId}/bab/store', [SyllabusController::class, 'babStore'])->name('bab.store');
Route::post('/syllabus/curriculum/bab/edit/{curriculumId}/{faseId}/{kelasId}/{mapelId}/{babId}', [SyllabusController::class, 'babEdit'])->name('bab.edit');
Route::put('/syllabus/curriculum/bab/activate/{babId}', [SyllabusController::class, 'babActivate'])->name('bab.activate');

// CRUD Sub Bab
Route::post('/syllabus/{curriculumId}/{faseId}/{kelasId}/{mapelId}/bab/sub-bab/store', [SyllabusController::class, 'subBabStore'])->name('sub_bab.store');
Route::post('/syllabus/curriculum/sub-bab/edit/{curriculumId}/{faseId}/{kelasId}/{mapelId}/{babId}/{subBabId}', [SyllabusController::class, 'subBabEdit'])->name('sub_bab.edit');
Route::put('/syllabus/curriculum/sub-bab/activate/{subBabId}', [SyllabusController::class, 'subBabActivate'])->name('sub_bab.activate');
