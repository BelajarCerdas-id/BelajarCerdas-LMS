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
Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}', [StudentAssessmentController::class, 'studentPreviewAssessment'])->name('lms.student_preview_assessment.view');
Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/mode/{mode}/{parentAssessmentId}', [StudentAssessmentController::class, 'studentPreviewAssessment'])->name('lms.student_preview_assessment.mode.view');

// load assessment data by semester
Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/semester/{semester}', [StudentAssessmentController::class, 'loadStudentPreviewAssessment'])->name('lms.load_student_preview_assessment');
Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/semester/{semester}/mode/{mode}/{parentAssessmentId}', [StudentAssessmentController::class, 'loadStudentPreviewAssessment'])->name('lms.load_student_preview_assessment.mode');

// assessment (exam)
Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/semester/{semester}/test/{assessmentId}', [StudentAssessmentExamController::class, 'studentAssessmentExam'])->name('lms.student_assessment_exam.view');

// form
Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/semester/{semester}/form/{assessmentId}', [StudentAssessmentExamController::class, 'studentAssessmentExamForm'])->name('lms.student_assessment_exam.form');

// routes timer
Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/semester/{semester}/form/{assessmentId}/start-timer', [StudentAssessmentExamController::class, 'startTimer'])->name('lms.start_timer.test');

// routes report tab switch (cheating detection)
Route::post('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/semester/{semester}/form/{assessmentId}/report-tab-switch', [StudentAssessmentExamController::class, 'reportTabSwitch'])->name('lms.report_tab_switch.cheating');
Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/semester/{semester}/form/{assessmentId}/attempt-status', [StudentAssessmentExamController::class, 'checkAttemptStatus'])->name('lms.check_attempt_status.cheating');
Route::post('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/semester/{semester}/form/{assessmentId}/heartbeat', [StudentAssessmentExamController::class, 'heartbeat'])->name('lms.heartbeat.cheating');

// answer
Route::post('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/semester/{semester}/form/{assessmentId}/answer', [StudentAssessmentExamController::class, 'studentAssessmentExamAnswer'])->name('lms.student_assessment_exam.answer');
Route::post('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/semester/{semester}/form/{assessmentId}/project-submission', [StudentAssessmentExamController::class, 'studentProjectSubmission'])->name('lms.student_project_submission.answer');

// end assessment
Route::post('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/semester/{semester}/form/{assessmentId}/end', [StudentAssessmentExamController::class, 'studentAssessmentExamEnd'])->name('lms.student_assessment_exam.end');
Route::post('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/semester/{semester}/form/{assessmentId}/emd', [StudentAssessmentExamController::class, 'studentAssessmentExamEnd'])->name('lms.student_assessment_exam.emd');

// results
Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/semester/{semester}/assessment/{assessmentId}/result-test', [StudentAssessmentExamController::class, 'studentResultAssessment'])->name('lms.student_assessment.result');
Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/semester/{semester}/assessment/{assessmentId}/project-result', [StudentAssessmentExamController::class, 'studentProjectResult'])->name('lms.student_project_assessment.result');
Route::get('/lms/{schoolId}/teacher-schedule/get-data/{classId}', [TeacherInformationController::class, 'getScheduleDataAjax']);

// polling siswa
Route::post('/lms/student/polling/vote', [StudentDashboardController::class, 'submitVote'])->name('lms.student_polling.vote');

// ROUTES TRYOUT TKA (STUDENT)
// PERIOD LIST
// views
Route::get('/lms/{role}/{schoolName}/{schoolId}/tryout-tka/period-list', [StudentTkaTryoutPeriodController::class, 'index'])->name('lms.student.tka_tryout_period.view');

// check period
Route::get('/lms/{role}/{schoolName}/{schoolId}/tryout-tka/period-list/{periodId}/check', [StudentTkaTryoutPeriodController::class, 'checkPeriod'])->name('student.tka_tryout.period.check');

// paginate
Route::get('/lms/{role}/{schoolName}/{schoolId}/tryout-tka/period-list/paginate', [StudentTkaTryoutPeriodController::class, 'paginatePeriodList'])->name('lms.student.tka_tryout_period.paginate');

// SESSION LIST
// views
Route::get('/lms/{role}/{schoolName}/{schoolId}/tryout-tka/period-list/{periodId}/session-list', [StudentTkaTryoutSessionController::class, 'index'])->name('lms.student.tka_tryout_session.view');

// paginate
Route::get('/lms/{role}/{schoolName}/{schoolId}/tryout-tka/period-list/{periodId}/session-list/paginate', [StudentTkaTryoutSessionController::class, 'paginateSessionList'])->name('lms.student.tka_tryout_session.paginate');

// TRYOUT TKA TEST
// views
Route::get('/lms/{role}/{schoolName}/{schoolId}/tryout-tka/period-list/{periodId}/session-list/{sessionId}/subject/{subjectId}/test', [StudentTkaTryoutTestController::class, 'index'])->name('lms.student.tka_tryout_test.view');

// form
Route::get('/lms/{role}/{schoolName}/{schoolId}/tryout-tka/period-list/{periodId}/session-list/{sessionId}/subject/{subjectId}/test/form', [StudentTkaTryoutTestController::class, 'studentTkaTryoutTestForm'])->name('lms.student.tka_tryout_test.form');

// start test
Route::post('/lms/{role}/{schoolName}/{schoolId}/tryout-tka/period-list/{periodId}/session-list/{sessionId}/subject/{subjectId}/test/form/start', [StudentTkaTryoutTestController::class, 'studentTkaTryoutStartTest'])->name('lms.student.tka_tryout_test.start');

// submit answer
Route::post('/lms/{role}/{schoolName}/{schoolId}/tryout-tka/period-list/{periodId}/session-list/{sessionId}/subject/{subjectId}/test/form/submit-answer/{attemptId}', [StudentTkaTryoutTestController::class, 'studentTkaTryoutSubmitAnswer'])->name('lms.student.tka_tryout_test.submit_answer');

// =========================================================
// ROUTES TEACHER LMS
// =========================================================

// content management
Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher/dashboard', [LmsController::class, 'lmsTeacherView'])->name('lms.teacher.view');
Route::get('/lms/{role}/{schoolName}/{schoolId}/beranda/cheating-history', [LmsController::class, 'getTeacherAssessmentCheatingHistory'])->name('lms.teacher_assessment_cheating_history.data');
Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-content-management', [TeacherContentController::class, 'teacherContentManagement'])->name('lms.teacher_content_management.view');
Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-content-management/{contentId}/review', [TeacherContentController::class, 'teacherReviewContent'])->name('lms.teacher_content_management.review.view');
Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-content-management/{contentId}/edit', [TeacherContentController::class, 'teacherEditContent'])->name('lms.teacher_content_management.edit.view');
Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-content-management/paginate', [TeacherContentController::class, 'paginateTeacherContentManagement'])->name('lms.teacher_content_management.paginate');

// content for release
Route::get('/lms/{role}/{schoolName}/{schoolId}/content-for-release', [TeacherContentReleaseController::class, 'teacherContentForRelease'])->name('lms.teacher_content_for_release.view');
Route::get('/lms/{role}/{schoolName}/{schoolId}/content-for-release/rombel-kelas/{schoolClassId}/subject/{mapelId}/semester/{semester}/service/{serviceId}/review-meetings', [TeacherContentReleaseController::class, 'teacherContentForReleaseReviewMeeting'])->name('lms.teacher_content_for_release_review_meeting.view');
Route::get('/lms/{role}/{schoolName}/{schoolId}/content-for-release/rombel-kelas/{schoolClassId}/subject/{mapelId}/semester/{semester}/service/{serviceId}/review-content/{meetingContentId}', [TeacherContentReleaseController::class, 'teacherContentForReleaseReviewContent'])->name('lms.teacher_content_for_release_review_content.view');
Route::get('/lms/{role}/{schoolName}/{schoolId}/content-for-release/form', [TeacherContentReleaseController::class, 'teacherFormContentForRelease'])->name('lms.teacher_content_for_release.form');
Route::post('/lms/{role}/{schoolName}/{schoolId}/content-for-release/store', [TeacherContentReleaseController::class, 'teacherContentForReleaseStore'])->name('lms.teacher_content_for_release.store');
Route::post('/lms/{role}/{schoolName}/{schoolId}/content-for-release/{meetingContentId}/edit', [TeacherContentReleaseController::class, 'teacherContentForReleaseEdit'])->name('lms.teacher_content_for_release.edit');
Route::put('/lms/{role}/{schoolName}/{schoolId}/content-for-release/{meetingContentId}/activate', [TeacherContentReleaseController::class, 'teacherContentForReleaseActivate'])->name('lms.teacher_content_for_release.activate');
Route::get('/lms/{role}/{schoolName}/{schoolId}/content-for-release/paginate', [TeacherContentReleaseController::class, 'paginateTeacherContentForRelease'])->name('lms.teacher_content_for_release.paginate');
Route::get('/lms/{role}/{schoolName}/{schoolId}/content-for-release/rombel-kelas/{schoolClassId}/subject/{mapelId}/semester/{semester}/service/{serviceId}/review-meetings/paginate', [TeacherContentReleaseController::class, 'paginateTeacherContentForReleaseReviewMeeting'])->name('lms.teacher_content_for_release_review_meeting.paginate');

// assessment management
Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-assessment-management', [TeacherAssessmentController::class, 'teacherAssessmentManagement'])->name('lms.teacher_assessment_management.view');
Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-assessment-management/{mode}/{parentAssessmentId}', [TeacherAssessmentController::class, 'teacherAssessmentManagement'])->name('lms.teacher_assessment_management.mode.view');
Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-assessment-management/{assessmentId}/edit/view', [TeacherAssessmentController::class, 'teacherAssessmentManagementEdit'])->name('lms.teacher_assessment_management_edit.view');

// form
Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-assessment-management/form', [TeacherAssessmentController::class, 'teacherFormAssessmentManagement'])->name('lms.teacher_form_assessment_management.form');
Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-assessment-management/{assessmentId}/edit/form', [TeacherAssessmentController::class, 'teacherFormAssessmentManagementEdit'])->name('lms.teacher_form_assessment_management.edit');
Route::post('/lms/{role}/{schoolName}/{schoolId}/teacher-assessment-management/validate-step-form/store', [TeacherAssessmentController::class, 'teacherFormAssessmentValidateStep'])->name('lms.teacher_assessment_management_validate_step.form');
Route::post('/lms/{role}/{schoolName}/{schoolId}/teacher-assessment-management/store', [TeacherAssessmentController::class, 'teacherFormAssessmentManagementStore'])->name('lms.teacher_assessment_management.store');
Route::post('/lms/{role}/{schoolName}/{schoolId}/teacher-assessment-management/{assessmentId}/edit', [TeacherAssessmentController::class, 'teacherAssessmentManagementEditSubmission'])->name('lms.teacher_assessment_management.edit');
Route::put('/lms/{role}/{schoolName}/{schoolId}/teacher-assessment-management/{assessmentId}/activate', [TeacherAssessmentController::class, 'teacherFormAssessmentManagementActivate'])->name('lms.teacher_assessment_management.activate');
Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-assessment-management/paginate', [TeacherAssessmentController::class, 'paginateTeacherAssessmentManagement'])->name('lms.teacher_assessment_management.paginate');

// question bank management
Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-question-bank-management', [TeacherQuestionBankController::class, 'teacherQuestionBankManagement'])->name('lms.teacher_question_bank_management.view');
Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-question-bank-management/kurikulum/{kurikulumId}/kelas/{kelasId}/mapel/{mapelId}/source/{source}/review/question-type/{questionType}/question-category/{questionCategory}/{questionId}/edit/{babId?}/{subBabId?}', [TeacherQuestionBankController::class, 'teacherQuestionBankManagementEdit'])->name('lms.teacher_question_bank_management.edit.view');
Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-question-bank-management/kurikulum/{kurikulumId}/kelas/{kelasId}/mapel/{mapelId}/source/{source}/review/question-type/{questionType}/question-category/{questionCategory}/{babId?}/{subBabId?}', [TeacherQuestionBankController::class, 'teacherQuestionBankManagementDetail'])->name('lms.teacher_question_bank_management.detail.view');
Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-question-bank-management/paginate', [TeacherQuestionBankController::class, 'paginateTeacherQuestionBankManagement'])->name('lms.teacher_question_bank_management.paginate');

// question bank for release
Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-question-bank-for-release', [TeacherQuestionBankReleaseController::class, 'teacherQuestionBankForRelease'])->name('lms.teacher_question_bank_for_release.view');
Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-question-bank-for-release/review/{assessmentQuestionId}', [TeacherQuestionBankReleaseController::class, 'teacherReviewQuestionBankForRelease'])->name('lms.teacher_review_question_bank_for_release.view');
Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-question-bank-for-release/form', [TeacherQuestionBankReleaseController::class, 'teacherFormQuestionBankForRelease'])->name('lms.teacher_question_bank_for_release.form');
Route::post('/lms/{role}/{schoolName}/{schoolId}/teacher-question-bank-for-release/store', [TeacherQuestionBankReleaseController::class, 'teacherQuestionBankForReleaseStore'])->name('lms.teacher_question_bank_for_release.store');
Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-question-bank-for-release/paginate', [TeacherQuestionBankReleaseController::class, 'paginateTeacherQuestionBankForRelease'])->name('lms.teacher_question_bank_for_release.paginate');
Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-question-bank-for-release/review/{assessmentQuestionId}/paginate', [TeacherQuestionBankReleaseController::class, 'paginateTeacherReviewQuestionBankForRelease'])->name('lms.teacher_review_question_bank_for_release.paginate');

// teacher assessment grading
Route::get('/lms/{role}/{schoolName}/{schoolId}/assessment-grading', [TeacherAssessmentGradingController::class, 'assessmentGradingManagement'])->name('lms.assessment_grading_management.view');
Route::get('/lms/{role}/{schoolName}/{schoolId}/assessment-grading/{assessmentId}/mode/{mode}/student-list', [TeacherAssessmentGradingController::class, 'assessmentGradingStudentList'])->name('lms.assessment_grading_student_list.view');
Route::get('/lms/{role}/{schoolName}/{schoolId}/assessment-grading/{assessmentId}/mode/{mode}/student-list/{studentId}/scoring', [TeacherAssessmentGradingController::class, 'assessmentGradingStudentAnswer'])->name('lms.assessment_grading_student_answer.view');

// crud
Route::post('/lms/{role}/{schoolName}/{schoolId}/assessment-grading/{assessmentId}/student-list/{studentId}/scoring/submission/{schoolAssessmentQuestionId}', [TeacherAssessmentGradingController::class, 'submitAssessmentStudentScore'])->name('lms.assessment_grading_student_answer.submission');
Route::post('/lms/{role}/{schoolName}/{schoolId}/assessment-grading/{assessmentId}/student-list/{studentId}/scoring/submission/{submissionId}/project', [TeacherAssessmentGradingController::class, 'submitAssessmentStudentProjectScore'])->name('lms.assessment_grading_student_project.submission');
Route::get('/lms/{role}/{schoolName}/{schoolId}/assessment-grading/paginate', [TeacherAssessmentGradingController::class, 'paginateAssessmentGrading'])->name('lms.assessment_grading.paginate');
Route::get('/lms/{role}/{schoolName}/{schoolId}/assessment-grading/{assessmentId}/mode/{mode}/student-list/paginate', [TeacherAssessmentGradingController::class, 'paginateAssessmentGradingStudentList'])->name('lms.assessment_grading_student_list.paginate');
Route::get('/lms/{role}/{schoolName}/{schoolId}/assessment-grading/{assessmentId}/mode/{mode}/student-list/{studentId}/scoring/paginate', [TeacherAssessmentGradingController::class, 'paginateAssessmentGradingStudentAnswer'])->name('lms.assessment_grading_student_answer.paginate');
Route::get('/lms/{role}/{schoolName}/{schoolId}/assessment-grading/{assessmentId}/student-list/{studentId}/scoring/project', [TeacherAssessmentGradingController::class, 'paginateAssessmentGradingStudentProject'])->name('lms.assessment_grading_student_project');
