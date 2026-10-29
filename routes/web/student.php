<?php

use App\Http\Controllers\LmsController;
use App\Http\Controllers\Student\TkaTryout\StudentTkaTryoutPeriodController;
use App\Http\Controllers\Student\TkaTryout\StudentTkaTryoutSessionController;
use App\Http\Controllers\Student\TkaTryout\StudentTkaTryoutTestController;
use App\Http\Controllers\StudentAssessmentController;
use App\Http\Controllers\StudentAssessmentExamController;
use App\Http\Controllers\StudentDashboardController;
use App\Http\Controllers\TeacherAssessmentController;
use App\Http\Controllers\TeacherAssessmentGradingController;
use App\Http\Controllers\TeacherContentController;
use App\Http\Controllers\TeacherContentReleaseController;
use App\Http\Controllers\TeacherInformationController;
use App\Http\Controllers\TeacherQuestionBankController;
use App\Http\Controllers\TeacherQuestionBankReleaseController;
use Illuminate\Support\Facades\Route;

// assessment
Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}', [StudentAssessmentController::class, 'studentPreviewAssessment'])->name('lms.studentPreviewAssessment.view');
Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/mode/{mode}/{parentAssessmentId}', [StudentAssessmentController::class, 'studentPreviewAssessment'])->name('lms.studentPreviewAssessment.mode.view');

// load assessment data by semester
Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/semester/{semester}', [StudentAssessmentController::class, 'loadStudentPreviewAssessment'])->name('lms.loadStudentPreviewAssessment');
Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/semester/{semester}/mode/{mode}/{parentAssessmentId}', [StudentAssessmentController::class, 'loadStudentPreviewAssessment'])->name('lms.loadStudentPreviewAssessment.mode');

// assessment (exam)
Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/semester/{semester}/test/{assessmentId}', [StudentAssessmentExamController::class, 'studentAssessmentExam'])->name('lms.studentAssessmentExan.view');

// form
Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/semester/{semester}/form/{assessmentId}', [StudentAssessmentExamController::class, 'studentAssessmentExamForm'])->name('lms.studentAssessmentExan.form');

// routes timer
Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/semester/{semester}/form/{assessmentId}/start-timer', [StudentAssessmentExamController::class, 'startTimer'])->name('lms.startTimer.test');

// routes report tab switch (cheating detection)
Route::post('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/semester/{semester}/form/{assessmentId}/report-tab-switch', [StudentAssessmentExamController::class, 'reportTabSwitch'])->name('lms.reportTabSwitch.cheating');
Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/semester/{semester}/form/{assessmentId}/attempt-status', [StudentAssessmentExamController::class, 'checkAttemptStatus'])->name('lms.checkAttemptStatus.cheating');
Route::post('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/semester/{semester}/form/{assessmentId}/heartbeat', [StudentAssessmentExamController::class, 'heartbeat'])->name('lms.heartbeat.cheating');

// answer
Route::post('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/semester/{semester}/form/{assessmentId}/answer', [StudentAssessmentExamController::class, 'studentAssessmentExamAnswer'])->name('lms.studentAssessmentExan.answer');
Route::post('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/semester/{semester}/form/{assessmentId}/project-submission', [StudentAssessmentExamController::class, 'studentProjectSubmission'])->name('lms.studentProjectSubmission.answer');

// end assessment
Route::post('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/semester/{semester}/form/{assessmentId}/end', [StudentAssessmentExamController::class, 'studentAssessmentExamEnd'])->name('lms.studentAssessmentExam.end');
Route::post('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/semester/{semester}/form/{assessmentId}/emd', [StudentAssessmentExamController::class, 'studentAssessmentExamEnd'])->name('lms.studentAssessmentExan.emd');

// results
Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/semester/{semester}/assessment/{assessmentId}/result-test', [StudentAssessmentExamController::class, 'studentResultAssessment'])->name('lms.studentAssessment.result');
Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/semester/{semester}/assessment/{assessmentId}/project-result', [StudentAssessmentExamController::class, 'studentProjectResult'])->name('lms.studentProjectAssessment.result');
Route::get('/lms/{schoolId}/teacher-schedule/get-data/{classId}', [TeacherInformationController::class, 'getScheduleDataAjax']);

// polling siswa
Route::post('/lms/student/polling/vote', [StudentDashboardController::class, 'submitVote'])->name('lms.studentPolling.vote');

// ROUTES TRYOUT TKA (STUDENT)
// PERIOD LIST
// views
Route::get('/lms/{role}/{schoolName}/{schoolId}/tryout-tka/period-list', [StudentTkaTryoutPeriodController::class, 'index'])->name('lms.student.tka-tryout-period.view');

// check period
Route::get('/lms/{role}/{schoolName}/{schoolId}/tryout-tka/period-list/{periodId}/check', [StudentTkaTryoutPeriodController::class, 'checkPeriod'])->name('student.tka-tryout.period.check');

// paginate
Route::get('/lms/{role}/{schoolName}/{schoolId}/tryout-tka/period-list/paginate', [StudentTkaTryoutPeriodController::class, 'paginatePeriodList'])->name('lms.student.tka-tryout-period.paginate');

// SESSION LIST
// views
Route::get('/lms/{role}/{schoolName}/{schoolId}/tryout-tka/period-list/{periodId}/session-list', [StudentTkaTryoutSessionController::class, 'index'])->name('lms.student.tka-tryout-session.view');

// paginate
Route::get('/lms/{role}/{schoolName}/{schoolId}/tryout-tka/period-list/{periodId}/session-list/paginate', [StudentTkaTryoutSessionController::class, 'paginateSessionList'])->name('lms.student.tka-tryout-session.paginate');

// TRYOUT TKA TEST
// views
Route::get('/lms/{role}/{schoolName}/{schoolId}/tryout-tka/period-list/{periodId}/session-list/{sessionId}/subject/{subjectId}/test', [StudentTkaTryoutTestController::class, 'index'])->name('lms.student.tka-tryout-test.view');

// form
Route::get('/lms/{role}/{schoolName}/{schoolId}/tryout-tka/period-list/{periodId}/session-list/{sessionId}/subject/{subjectId}/test/form', [StudentTkaTryoutTestController::class, 'studentTkaTryoutTestForm'])->name('lms.student.tka-tryout-test.form');

// start test
Route::post('/lms/{role}/{schoolName}/{schoolId}/tryout-tka/period-list/{periodId}/session-list/{sessionId}/subject/{subjectId}/test/form/start', [StudentTkaTryoutTestController::class, 'studentTkaTryoutStartTest'])->name('lms.student.tka-tryout-test.start');

// submit answer
Route::post('/lms/{role}/{schoolName}/{schoolId}/tryout-tka/period-list/{periodId}/session-list/{sessionId}/subject/{subjectId}/test/form/submit-answer/{attemptId}', [StudentTkaTryoutTestController::class, 'studentTkaTryoutSubmitAnswer'])->name('lms.student.tka-tryout-test.submit-answer');

// =========================================================
// ROUTES TEACHER LMS
// =========================================================

// content management
Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher/dashboard', [LmsController::class, 'lmsTeacherView'])->name('lms.teacher.view');
Route::get('/lms/{role}/{schoolName}/{schoolId}/beranda/cheating-history', [LmsController::class, 'getTeacherAssessmentCheatingHistory'])->name('lms.teacherAssessmentCheatingHistory.data');
Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-content-management', [TeacherContentController::class, 'teacherContentManagement'])->name('lms.teacherContentManagement.view');
Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-content-management/{contentId}/review', [TeacherContentController::class, 'teacherReviewContent'])->name('lms.teacherContentManagement.review.view');
Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-content-management/{contentId}/edit', [TeacherContentController::class, 'teacherEditContent'])->name('lms.teacherContentManagement.edit.view');
Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-content-management/paginate', [TeacherContentController::class, 'paginateTeacherContentManagement'])->name('lms.teacherContentManagement.paginate');

// content for release
Route::get('/lms/{role}/{schoolName}/{schoolId}/content-for-release', [TeacherContentReleaseController::class, 'teacherContentForRelease'])->name('lms.teacherContentForRelease.view');
Route::get('/lms/{role}/{schoolName}/{schoolId}/content-for-release/rombel-kelas/{schoolClassId}/subject/{mapelId}/semester/{semester}/service/{serviceId}/review-meetings', [TeacherContentReleaseController::class, 'teacherContentForReleaseReviewMeeting'])->name('lms.teacherContentForReleaseReviewMeeting.view');
Route::get('/lms/{role}/{schoolName}/{schoolId}/content-for-release/rombel-kelas/{schoolClassId}/subject/{mapelId}/semester/{semester}/service/{serviceId}/review-content/{meetingContentId}', [TeacherContentReleaseController::class, 'teacherContentForReleaseReviewContent'])->name('lms.teacherContentForReleaseReviewContent.view');
Route::get('/lms/{role}/{schoolName}/{schoolId}/content-for-release/form', [TeacherContentReleaseController::class, 'teacherFormContentForRelease'])->name('lms.teacherContentForRelease.form');
Route::post('/lms/{role}/{schoolName}/{schoolId}/content-for-release/store', [TeacherContentReleaseController::class, 'teacherContentForReleaseStore'])->name('lms.teacherContentForRelease.store');
Route::post('/lms/{role}/{schoolName}/{schoolId}/content-for-release/{meetingContentId}/edit', [TeacherContentReleaseController::class, 'teacherContentForReleaseEdit'])->name('lms.teacherContentForRelease.edit');
Route::put('/lms/{role}/{schoolName}/{schoolId}/content-for-release/{meetingContentId}/activate', [TeacherContentReleaseController::class, 'teacherContentForReleaseActivate'])->name('lms.teacherContentForRelease.activate');
Route::get('/lms/{role}/{schoolName}/{schoolId}/content-for-release/paginate', [TeacherContentReleaseController::class, 'paginateTeacherContentForRelease'])->name('lms.teacherContentForRelease.paginate');
Route::get('/lms/{role}/{schoolName}/{schoolId}/content-for-release/rombel-kelas/{schoolClassId}/subject/{mapelId}/semester/{semester}/service/{serviceId}/review-meetings/paginate', [TeacherContentReleaseController::class, 'paginateTeacherContentForReleaseReviewMeeting'])->name('lms.teacherContentForReleaseReviewMeeting.paginate');

// assessment management
Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-assessment-management', [TeacherAssessmentController::class, 'teacherAssessmentManagement'])->name('lms.teacherAssessmentManagement.view');
Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-assessment-management/{mode}/{parentAssessmentId}', [TeacherAssessmentController::class, 'teacherAssessmentManagement'])->name('lms.teacherAssessmentManagement.mode.view');
Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-assessment-management/{assessmentId}/edit/view', [TeacherAssessmentController::class, 'teacherAssessmentManagementEdit'])->name('lms.teacherAssessmentManagementEdit.view');

// form
Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-assessment-management/form', [TeacherAssessmentController::class, 'teacherFormAssessmentManagement'])->name('lms.teacherFormAssessmentManagement.form');
Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-assessment-management/{assessmentId}/edit/form', [TeacherAssessmentController::class, 'teacherFormAssessmentManagementEdit'])->name('lms.teacherFormAssessmentManagement.edit');
Route::post('/lms/{role}/{schoolName}/{schoolId}/teacher-assessment-management/validate-step-form/store', [TeacherAssessmentController::class, 'teacherFormAssessmentValidateStep'])->name('lms.teacherAssessmentManagementValidateStep.form');
Route::post('/lms/{role}/{schoolName}/{schoolId}/teacher-assessment-management/store', [TeacherAssessmentController::class, 'teacherFormAssessmentManagementStore'])->name('lms.teacherAssessmentManagement.store');
Route::post('/lms/{role}/{schoolName}/{schoolId}/teacher-assessment-management/{assessmentId}/edit', [TeacherAssessmentController::class, 'teacherAssessmentManagementEditSubmission'])->name('lms.teacherAssessmentManagement.edit');
Route::put('/lms/{role}/{schoolName}/{schoolId}/teacher-assessment-management/{assessmentId}/activate', [TeacherAssessmentController::class, 'teacherFormAssessmentManagementActivate'])->name('lms.teacherAssessmentManagement.activate');
Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-assessment-management/paginate', [TeacherAssessmentController::class, 'paginateTeacherAssessmentManagement'])->name('lms.teacherAssessmentManagement.paginate');

// question bank management
Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-question-bank-management', [TeacherQuestionBankController::class, 'teacherQuestionBankManagement'])->name('lms.teacherQuestionBankManagement.view');
Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-question-bank-management/kurikulum/{kurikulumId}/kelas/{kelasId}/mapel/{mapelId}/source/{source}/review/question-type/{questionType}/question-category/{questionCategory}/{questionId}/edit/{babId?}/{subBabId?}', [TeacherQuestionBankController::class, 'teacherQuestionBankManagementEdit'])->name('lms.teacherQuestionBankManagement.edit.view');
Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-question-bank-management/kurikulum/{kurikulumId}/kelas/{kelasId}/mapel/{mapelId}/source/{source}/review/question-type/{questionType}/question-category/{questionCategory}/{babId?}/{subBabId?}', [TeacherQuestionBankController::class, 'teacherQuestionBankManagementDetail'])->name('lms.teacherQuestionBankManagement.detail.view');
Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-question-bank-management/paginate', [TeacherQuestionBankController::class, 'paginateTeacherQuestionBankManagement'])->name('lms.teacherQuestionBankManagement.paginate');

// question bank for release
Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-question-bank-for-release', [TeacherQuestionBankReleaseController::class, 'teacherQuestionBankForRelease'])->name('lms.teacherQuestionBankForRelease.view');
Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-question-bank-for-release/review/{assessmentQuestionId}', [TeacherQuestionBankReleaseController::class, 'teacherReviewQuestionBankForRelease'])->name('lms.teacherReviewQuestionBankForRelease.view');
Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-question-bank-for-release/form', [TeacherQuestionBankReleaseController::class, 'teacherFormQuestionBankForRelease'])->name('lms.teacherQuestionBankForRelease.form');
Route::post('/lms/{role}/{schoolName}/{schoolId}/teacher-question-bank-for-release/store', [TeacherQuestionBankReleaseController::class, 'teacherQuestionBankForReleaseStore'])->name('lms.teacherQuestionBankForRelease.store');
Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-question-bank-for-release/paginate', [TeacherQuestionBankReleaseController::class, 'paginateTeacherQuestionBankForRelease'])->name('lms.teacherQuestionBankForRelease.paginate');
Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-question-bank-for-release/review/{assessmentQuestionId}/paginate', [TeacherQuestionBankReleaseController::class, 'paginateTeacherReviewQuestionBankForRelease'])->name('lms.teacherReviewQuestionBankForRelease.paginate');

// teacher assessment grading
Route::get('/lms/{role}/{schoolName}/{schoolId}/assessment-grading', [TeacherAssessmentGradingController::class, 'assessmentGradingManagement'])->name('lms.assessmentGradingManagement.view');
Route::get('/lms/{role}/{schoolName}/{schoolId}/assessment-grading/{assessmentId}/mode/{mode}/student-list', [TeacherAssessmentGradingController::class, 'assessmentGradingStudentList'])->name('lms.assessmentGradingStudentList.view');
Route::get('/lms/{role}/{schoolName}/{schoolId}/assessment-grading/{assessmentId}/mode/{mode}/student-list/{studentId}/scoring', [TeacherAssessmentGradingController::class, 'assessmentGradingStudentAnswer'])->name('lms.assessmentGradingStudentAnswer.view');

// crud
Route::post('/lms/{role}/{schoolName}/{schoolId}/assessment-grading/{assessmentId}/student-list/{studentId}/scoring/submission/{schoolAssessmentQuestionId}', [TeacherAssessmentGradingController::class, 'submitAssessmentStudentScore'])->name('lms.assessmentGradingStudentAnswer.submission');
Route::post('/lms/{role}/{schoolName}/{schoolId}/assessment-grading/{assessmentId}/student-list/{studentId}/scoring/submission/{submissionId}/project', [TeacherAssessmentGradingController::class, 'submitAssessmentStudentProjectScore'])->name('lms.assessmentGradingStudentProject.submission');
Route::get('/lms/{role}/{schoolName}/{schoolId}/assessment-grading/paginate', [TeacherAssessmentGradingController::class, 'paginateAssessmentGrading'])->name('lms.assessmentGrading.paginate');
Route::get('/lms/{role}/{schoolName}/{schoolId}/assessment-grading/{assessmentId}/mode/{mode}/student-list/paginate', [TeacherAssessmentGradingController::class, 'paginateAssessmentGradingStudentList'])->name('lms.assessmentGradingStudentList.paginate');
Route::get('/lms/{role}/{schoolName}/{schoolId}/assessment-grading/{assessmentId}/mode/{mode}/student-list/{studentId}/scoring/paginate', [TeacherAssessmentGradingController::class, 'paginateAssessmentGradingStudentAnswer'])->name('lms.assessmentGradingStudentAnswer.paginate');
Route::get('/lms/{role}/{schoolName}/{schoolId}/assessment-grading/{assessmentId}/student-list/{studentId}/scoring/project', [TeacherAssessmentGradingController::class, 'paginateAssessmentGradingStudentProject'])->name('lms.assessmentGradingStudentProject');
