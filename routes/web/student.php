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
Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}', [StudentAssessmentController::class, 'studentPreviewAssessment'])->name('Lms.StudentPreviewAssessment.View');
Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/mode/{mode}/{parentAssessmentId}', [StudentAssessmentController::class, 'studentPreviewAssessment'])->name('Lms.StudentPreviewAssessment.Mode.View');

// load assessment data by semester
Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/semester/{semester}', [StudentAssessmentController::class, 'loadStudentPreviewAssessment'])->name('Lms.LoadStudentPreviewAssessment');
Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/semester/{semester}/mode/{mode}/{parentAssessmentId}', [StudentAssessmentController::class, 'loadStudentPreviewAssessment'])->name('Lms.LoadStudentPreviewAssessment.Mode');

// assessment (exam)
Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/semester/{semester}/test/{assessmentId}', [StudentAssessmentExamController::class, 'studentAssessmentExam'])->name('Lms.StudentAssessmentExam.View');

// form
Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/semester/{semester}/form/{assessmentId}', [StudentAssessmentExamController::class, 'studentAssessmentExamForm'])->name('Lms.StudentAssessmentExam.Form');

// routes timer
Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/semester/{semester}/form/{assessmentId}/start-timer', [StudentAssessmentExamController::class, 'startTimer'])->name('Lms.StartTimer.Test');

// routes report tab switch (cheating detection)
Route::post('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/semester/{semester}/form/{assessmentId}/report-tab-switch', [StudentAssessmentExamController::class, 'reportTabSwitch'])->name('Lms.ReportTabSwitch.Cheating');
Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/semester/{semester}/form/{assessmentId}/attempt-status', [StudentAssessmentExamController::class, 'checkAttemptStatus'])->name('Lms.CheckAttemptStatus.Cheating');
Route::post('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/semester/{semester}/form/{assessmentId}/heartbeat', [StudentAssessmentExamController::class, 'heartbeat'])->name('Lms.Heartbeat.Cheating');

// answer
Route::post('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/semester/{semester}/form/{assessmentId}/answer', [StudentAssessmentExamController::class, 'studentAssessmentExamAnswer'])->name('Lms.StudentAssessmentExam.Answer');
Route::post('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/semester/{semester}/form/{assessmentId}/project-submission', [StudentAssessmentExamController::class, 'studentProjectSubmission'])->name('Lms.StudentProjectSubmission.Answer');

// end assessment
Route::post('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/semester/{semester}/form/{assessmentId}/end', [StudentAssessmentExamController::class, 'studentAssessmentExamEnd'])->name('Lms.StudentAssessmentExam.End');
Route::post('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/semester/{semester}/form/{assessmentId}/emd', [StudentAssessmentExamController::class, 'studentAssessmentExamEnd'])->name('Lms.StudentAssessmentExam.Emd');

// results
Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/semester/{semester}/assessment/{assessmentId}/result-test', [StudentAssessmentExamController::class, 'studentResultAssessment'])->name('Lms.StudentAssessment.Result');
Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/semester/{semester}/assessment/{assessmentId}/project-result', [StudentAssessmentExamController::class, 'studentProjectResult'])->name('Lms.StudentProjectAssessment.Result');
Route::get('/lms/{schoolId}/teacher-schedule/get-data/{classId}', [TeacherInformationController::class, 'getScheduleDataAjax']);

// polling siswa
Route::post('/lms/student/polling/vote', [StudentDashboardController::class, 'submitVote'])->name('Lms.StudentPolling.Vote');

// ROUTES TRYOUT TKA (STUDENT)
// PERIOD LIST
// views
Route::get('/lms/{role}/{schoolName}/{schoolId}/tryout-tka/period-list', [StudentTkaTryoutPeriodController::class, 'index'])->name('Lms.Student.TkaTryoutPeriod.View');

// check period
Route::get('/lms/{role}/{schoolName}/{schoolId}/tryout-tka/period-list/{periodId}/check', [StudentTkaTryoutPeriodController::class, 'checkPeriod'])->name('Student.TkaTryout.Period.Check');

// paginate
Route::get('/lms/{role}/{schoolName}/{schoolId}/tryout-tka/period-list/paginate', [StudentTkaTryoutPeriodController::class, 'paginatePeriodList'])->name('Lms.Student.TkaTryoutPeriod.Paginate');

// SESSION LIST
// views
Route::get('/lms/{role}/{schoolName}/{schoolId}/tryout-tka/period-list/{periodId}/session-list', [StudentTkaTryoutSessionController::class, 'index'])->name('Lms.Student.TkaTryoutSession.View');

// paginate
Route::get('/lms/{role}/{schoolName}/{schoolId}/tryout-tka/period-list/{periodId}/session-list/paginate', [StudentTkaTryoutSessionController::class, 'paginateSessionList'])->name('Lms.Student.TkaTryoutSession.Paginate');

// TRYOUT TKA TEST
// views
Route::get('/lms/{role}/{schoolName}/{schoolId}/tryout-tka/period-list/{periodId}/session-list/{sessionId}/subject/{subjectId}/test', [StudentTkaTryoutTestController::class, 'index'])->name('Lms.Student.TkaTryoutTest.View');

// form
Route::get('/lms/{role}/{schoolName}/{schoolId}/tryout-tka/period-list/{periodId}/session-list/{sessionId}/subject/{subjectId}/test/form', [StudentTkaTryoutTestController::class, 'studentTkaTryoutTestForm'])->name('Lms.Student.TkaTryoutTest.Form');

// start test
Route::post('/lms/{role}/{schoolName}/{schoolId}/tryout-tka/period-list/{periodId}/session-list/{sessionId}/subject/{subjectId}/test/form/start', [StudentTkaTryoutTestController::class, 'studentTkaTryoutStartTest'])->name('Lms.Student.TkaTryoutTest.Start');

// submit answer
Route::post('/lms/{role}/{schoolName}/{schoolId}/tryout-tka/period-list/{periodId}/session-list/{sessionId}/subject/{subjectId}/test/form/submit-answer/{attemptId}', [StudentTkaTryoutTestController::class, 'studentTkaTryoutSubmitAnswer'])->name('Lms.Student.TkaTryoutTest.SubmitAnswer');

// =========================================================
// ROUTES TEACHER LMS
// =========================================================

// content management
Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher/dashboard', [LmsController::class, 'lmsTeacherView'])->name('Lms.Teacher.View');
Route::get('/lms/{role}/{schoolName}/{schoolId}/beranda/cheating-history', [LmsController::class, 'getTeacherAssessmentCheatingHistory'])->name('Lms.TeacherAssessmentCheatingHistory.Data');
Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-content-management', [TeacherContentController::class, 'teacherContentManagement'])->name('Lms.TeacherContentManagement.View');
Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-content-management/{contentId}/review', [TeacherContentController::class, 'teacherReviewContent'])->name('Lms.TeacherContentManagement.Review.View');
Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-content-management/{contentId}/edit', [TeacherContentController::class, 'teacherEditContent'])->name('Lms.TeacherContentManagement.Edit.View');
Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-content-management/paginate', [TeacherContentController::class, 'paginateTeacherContentManagement'])->name('Lms.TeacherContentManagement.Paginate');

// content for release
Route::get('/lms/{role}/{schoolName}/{schoolId}/content-for-release', [TeacherContentReleaseController::class, 'teacherContentForRelease'])->name('Lms.TeacherContentForRelease.View');
Route::get('/lms/{role}/{schoolName}/{schoolId}/content-for-release/rombel-kelas/{schoolClassId}/subject/{mapelId}/semester/{semester}/service/{serviceId}/review-meetings', [TeacherContentReleaseController::class, 'teacherContentForReleaseReviewMeeting'])->name('Lms.TeacherContentForReleaseReviewMeeting.View');
Route::get('/lms/{role}/{schoolName}/{schoolId}/content-for-release/rombel-kelas/{schoolClassId}/subject/{mapelId}/semester/{semester}/service/{serviceId}/review-content/{meetingContentId}', [TeacherContentReleaseController::class, 'teacherContentForReleaseReviewContent'])->name('Lms.TeacherContentForReleaseReviewContent.View');
Route::get('/lms/{role}/{schoolName}/{schoolId}/content-for-release/form', [TeacherContentReleaseController::class, 'teacherFormContentForRelease'])->name('Lms.TeacherContentForRelease.Form');
Route::post('/lms/{role}/{schoolName}/{schoolId}/content-for-release/store', [TeacherContentReleaseController::class, 'teacherContentForReleaseStore'])->name('Lms.TeacherContentForRelease.Store');
Route::post('/lms/{role}/{schoolName}/{schoolId}/content-for-release/{meetingContentId}/edit', [TeacherContentReleaseController::class, 'teacherContentForReleaseEdit'])->name('Lms.TeacherContentForRelease.Edit');
Route::put('/lms/{role}/{schoolName}/{schoolId}/content-for-release/{meetingContentId}/activate', [TeacherContentReleaseController::class, 'teacherContentForReleaseActivate'])->name('Lms.TeacherContentForRelease.Activate');
Route::get('/lms/{role}/{schoolName}/{schoolId}/content-for-release/paginate', [TeacherContentReleaseController::class, 'paginateTeacherContentForRelease'])->name('Lms.TeacherContentForRelease.Paginate');
Route::get('/lms/{role}/{schoolName}/{schoolId}/content-for-release/rombel-kelas/{schoolClassId}/subject/{mapelId}/semester/{semester}/service/{serviceId}/review-meetings/paginate', [TeacherContentReleaseController::class, 'paginateTeacherContentForReleaseReviewMeeting'])->name('Lms.TeacherContentForReleaseReviewMeeting.Paginate');

// assessment management
Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-assessment-management', [TeacherAssessmentController::class, 'teacherAssessmentManagement'])->name('Lms.TeacherAssessmentManagement.View');
Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-assessment-management/{mode}/{parentAssessmentId}', [TeacherAssessmentController::class, 'teacherAssessmentManagement'])->name('Lms.TeacherAssessmentManagement.Mode.View');
Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-assessment-management/{assessmentId}/edit/view', [TeacherAssessmentController::class, 'teacherAssessmentManagementEdit'])->name('Lms.TeacherAssessmentManagementEdit.View');

// form
Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-assessment-management/form', [TeacherAssessmentController::class, 'teacherFormAssessmentManagement'])->name('Lms.TeacherFormAssessmentManagement.Form');
Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-assessment-management/{assessmentId}/edit/form', [TeacherAssessmentController::class, 'teacherFormAssessmentManagementEdit'])->name('Lms.TeacherFormAssessmentManagement.Edit');
Route::post('/lms/{role}/{schoolName}/{schoolId}/teacher-assessment-management/validate-step-form/store', [TeacherAssessmentController::class, 'teacherFormAssessmentValidateStep'])->name('Lms.TeacherAssessmentManagementValidateStep.Form');
Route::post('/lms/{role}/{schoolName}/{schoolId}/teacher-assessment-management/store', [TeacherAssessmentController::class, 'teacherFormAssessmentManagementStore'])->name('Lms.TeacherAssessmentManagement.Store');
Route::post('/lms/{role}/{schoolName}/{schoolId}/teacher-assessment-management/{assessmentId}/edit', [TeacherAssessmentController::class, 'teacherAssessmentManagementEditSubmission'])->name('Lms.TeacherAssessmentManagement.Edit');
Route::put('/lms/{role}/{schoolName}/{schoolId}/teacher-assessment-management/{assessmentId}/activate', [TeacherAssessmentController::class, 'teacherFormAssessmentManagementActivate'])->name('Lms.TeacherAssessmentManagement.Activate');
Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-assessment-management/paginate', [TeacherAssessmentController::class, 'paginateTeacherAssessmentManagement'])->name('Lms.TeacherAssessmentManagement.Paginate');

// question bank management
Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-question-bank-management', [TeacherQuestionBankController::class, 'teacherQuestionBankManagement'])->name('Lms.TeacherQuestionBankManagement.View');
Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-question-bank-management/kurikulum/{kurikulumId}/kelas/{kelasId}/mapel/{mapelId}/source/{source}/review/question-type/{questionType}/question-category/{questionCategory}/{questionId}/edit/{babId?}/{subBabId?}', [TeacherQuestionBankController::class, 'teacherQuestionBankManagementEdit'])->name('Lms.TeacherQuestionBankManagement.Edit.View');
Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-question-bank-management/kurikulum/{kurikulumId}/kelas/{kelasId}/mapel/{mapelId}/source/{source}/review/question-type/{questionType}/question-category/{questionCategory}/{babId?}/{subBabId?}', [TeacherQuestionBankController::class, 'teacherQuestionBankManagementDetail'])->name('Lms.TeacherQuestionBankManagement.Detail.View');
Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-question-bank-management/paginate', [TeacherQuestionBankController::class, 'paginateTeacherQuestionBankManagement'])->name('Lms.TeacherQuestionBankManagement.Paginate');

// question bank for release
Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-question-bank-for-release', [TeacherQuestionBankReleaseController::class, 'teacherQuestionBankForRelease'])->name('Lms.TeacherQuestionBankForRelease.View');
Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-question-bank-for-release/review/{assessmentQuestionId}', [TeacherQuestionBankReleaseController::class, 'teacherReviewQuestionBankForRelease'])->name('Lms.TeacherReviewQuestionBankForRelease.View');
Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-question-bank-for-release/form', [TeacherQuestionBankReleaseController::class, 'teacherFormQuestionBankForRelease'])->name('Lms.TeacherQuestionBankForRelease.Form');
Route::post('/lms/{role}/{schoolName}/{schoolId}/teacher-question-bank-for-release/store', [TeacherQuestionBankReleaseController::class, 'teacherQuestionBankForReleaseStore'])->name('Lms.TeacherQuestionBankForRelease.Store');
Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-question-bank-for-release/paginate', [TeacherQuestionBankReleaseController::class, 'paginateTeacherQuestionBankForRelease'])->name('Lms.TeacherQuestionBankForRelease.Paginate');
Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-question-bank-for-release/review/{assessmentQuestionId}/paginate', [TeacherQuestionBankReleaseController::class, 'paginateTeacherReviewQuestionBankForRelease'])->name('Lms.TeacherReviewQuestionBankForRelease.Paginate');

// teacher assessment grading
Route::get('/lms/{role}/{schoolName}/{schoolId}/assessment-grading', [TeacherAssessmentGradingController::class, 'assessmentGradingManagement'])->name('Lms.AssessmentGradingManagement.View');
Route::get('/lms/{role}/{schoolName}/{schoolId}/assessment-grading/{assessmentId}/mode/{mode}/student-list', [TeacherAssessmentGradingController::class, 'assessmentGradingStudentList'])->name('Lms.AssessmentGradingStudentList.View');
Route::get('/lms/{role}/{schoolName}/{schoolId}/assessment-grading/{assessmentId}/mode/{mode}/student-list/{studentId}/scoring', [TeacherAssessmentGradingController::class, 'assessmentGradingStudentAnswer'])->name('Lms.AssessmentGradingStudentAnswer.View');

// crud
Route::post('/lms/{role}/{schoolName}/{schoolId}/assessment-grading/{assessmentId}/student-list/{studentId}/scoring/submission/{schoolAssessmentQuestionId}', [TeacherAssessmentGradingController::class, 'submitAssessmentStudentScore'])->name('Lms.AssessmentGradingStudentAnswer.Submission');
Route::post('/lms/{role}/{schoolName}/{schoolId}/assessment-grading/{assessmentId}/student-list/{studentId}/scoring/submission/{submissionId}/project', [TeacherAssessmentGradingController::class, 'submitAssessmentStudentProjectScore'])->name('Lms.AssessmentGradingStudentProject.Submission');
Route::get('/lms/{role}/{schoolName}/{schoolId}/assessment-grading/paginate', [TeacherAssessmentGradingController::class, 'paginateAssessmentGrading'])->name('Lms.AssessmentGrading.Paginate');
Route::get('/lms/{role}/{schoolName}/{schoolId}/assessment-grading/{assessmentId}/mode/{mode}/student-list/paginate', [TeacherAssessmentGradingController::class, 'paginateAssessmentGradingStudentList'])->name('Lms.AssessmentGradingStudentList.Paginate');
Route::get('/lms/{role}/{schoolName}/{schoolId}/assessment-grading/{assessmentId}/mode/{mode}/student-list/{studentId}/scoring/paginate', [TeacherAssessmentGradingController::class, 'paginateAssessmentGradingStudentAnswer'])->name('Lms.AssessmentGradingStudentAnswer.Paginate');
Route::get('/lms/{role}/{schoolName}/{schoolId}/assessment-grading/{assessmentId}/student-list/{studentId}/scoring/project', [TeacherAssessmentGradingController::class, 'paginateAssessmentGradingStudentProject'])->name('Lms.AssessmentGradingStudentProject');
