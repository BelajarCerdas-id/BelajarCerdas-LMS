<?php

use App\Http\Controllers\GradebookAssessmentController;
use App\Http\Controllers\HeadmasterController;
use App\Http\Controllers\Lms\Attendances\Student\SubjectAttendanceController;
use App\Http\Controllers\LmsController;
use App\Http\Controllers\StudentDashboardController;
use App\Http\Controllers\TeacherAcademicTranscriptController;
use App\Http\Controllers\TeacherDailyAgendaController;
use App\Http\Controllers\TeacherGradebookController;
use App\Http\Controllers\TeacherGradeLedgerController;
use App\Http\Controllers\TeacherInformationController;
use Illuminate\Support\Facades\Route;

// TEACHER SUBJECT ATTENDANCE MANAGEMENT
// views
Route::get('/lms/{role}/{schoolName}/{schoolId}/subject-attendance/classes', [SubjectAttendanceController::class, 'teacherClassList'])->name('lms.teacherClassListSubjectAttendance.view');
Route::get('/lms/{role}/{schoolName}/{schoolId}/subject-attendance/classes/subject-teacher/{subjectTeacherId}/meeting-list', [SubjectAttendanceController::class, 'subjectAttendanceMeetingList'])->name('lms.teacherSubjectAttendanceMeetingList.view');
Route::get('/lms/{role}/{schoolName}/{schoolId}/subject-attendance/classes/subject-teacher/{subjectTeacherId}/meeting-list/{meetingNumber}/semester/{semester}/meeting-management', [SubjectAttendanceController::class, 'subjectAttendanceMeetingManagement'])->name('lms.teacherSubjectAttendanceMeetingManagement.view');

// subject attendance data
Route::get('/lms/{role}/{schoolName}/{schoolId}/subject-attendance/classes/subject-teacher/{subjectTeacherId}/data', [SubjectAttendanceController::class, 'getSubjectAttendanceData'])->name('lms.teacherSubjectAttendance.data');

// crud
// announcement
Route::post('/lms/{role}/{schoolName}/{schoolId}/subject-attendance/classes/subject-teacher/{subjectTeacherId}/meeting-list/{meetingNumber}/semester/{semester}/meeting-management/announcement-store', [SubjectAttendanceController::class, 'announcementStore'])->name('lms.teacherSubjectAttendanceMeetingManagement.announcement.store');

// submit attendance student
Route::post('/lms/{role}/{schoolName}/{schoolId}/subject-attendance/classes/subject-teacher/{subjectTeacherId}/meeting-list/{meetingNumber}/semester/{semester}/student-attendance/store', [SubjectAttendanceController::class, 'storeStudentAttendance'])->name('lms.studentAttendance.store');

// paginate
Route::get('/lms/{role}/{schoolName}/{schoolId}/subject-attendance/classes/paginate', [SubjectAttendanceController::class, 'paginateTeacherClassList'])->name('lms.teacherClassListSubjectAttendance.paginate');
Route::get('/lms/{role}/{schoolName}/{schoolId}/subject-attendance/classes/subject-teacher/{subjectTeacherId}/meeting-list/semester/{semester}/paginate', [SubjectAttendanceController::class, 'paginateSubjectAttendanceMeetingList'])->name('lms.teacherSubjectAttendanceMeetingList.paginate');
Route::get('/lms/{role}/{schoolName}/{schoolId}/subject-attendance/classes/subject-teacher/{subjectTeacherId}/meeting-list/{meetingNumber}/semester/{semester}/meeting-management/announcement/paginate', [SubjectAttendanceController::class, 'paginateAnnouncementList'])->name('lms.teacherSubjectAttendanceMeetingManagement.announcement.paginate');
Route::get('/lms/{role}/{schoolName}/{schoolId}/subject-attendance/classes/subject-teacher/{subjectTeacherId}/meeting-list/{meetingNumber}/semester/{semester}/meeting-management/material/paginate', [SubjectAttendanceController::class, 'paginateMaterialList'])->name('lms.teacherSubjectAttendanceMeetingManagement.material.paginate');
Route::get('/lms/{role}/{schoolName}/{schoolId}/subject-attendance/classes/subject-teacher/{subjectTeacherId}/meeting-list/{meetingNumber}/semester/{semester}/meeting-management/assessment/paginate', [SubjectAttendanceController::class, 'paginateAssessmentList'])->name('lms.teacherSubjectAttendanceMeetingManagement.assessment.paginate');
Route::get('/lms/{role}/{schoolName}/{schoolId}/subject-attendance/classes/subject-teacher/{subjectTeacherId}/meeting-list/{meetingNumber}/semester/{semester}/student-attendance/paginate', [SubjectAttendanceController::class, 'paginateStudentAttendance'])->name('lms.studentAttendance.paginate');
Route::get('/lms/{role}/{schoolName}/{schoolId}/subject-attendance/classes/subject-teacher/{subjectTeacherId}/meeting-list/{meetingNumber}/semester/{semester}/chart', [SubjectAttendanceController::class, 'getAttendanceChart']);

// TEACHER DAILY AGENDA
Route::get('/lms/{role}/{schoolName}/{schoolId}/daily-agenda', [TeacherDailyAgendaController::class, 'index'])->name('lms.teacherDailyAgenda.view');
Route::get('/lms/{role}/{schoolName}/{schoolId}/daily-agenda/{dayOfWeek}/class/{classId}/subject-teacher/{subjectId}/form', [TeacherDailyAgendaController::class, 'teacherDailyAgendaForm'])->name('lms.teacherDailyAgendaForm.view');
Route::get('/lms/{role}/{schoolName}/{schoolId}/daily-agenda/history', [TeacherDailyAgendaController::class, 'teacherDailyAgendaHistory'])->name('lms.teacherDailyAgenda.history.view');

// load detail data
Route::get('/lms/{role}/{schoolName}/{schoolId}/daily-agenda/{dayOfWeek}/class/{classId}/subject-teacher/{subjectId}/form-detail', [TeacherDailyAgendaController::class, 'teacherDailyAgendaFormDetail'])->name('lms.teacherDailyAgendaForm.detail');

// crud
Route::post('/lms/{role}/{schoolName}/{schoolId}/daily-agenda/{dayOfWeek}/class/{classId}/subject-teacher/{subjectId}/form-detail/submit', [TeacherDailyAgendaController::class, 'teacherDailyAgendaSubmitForm'])->name('lms.teacherDailyAgendaForm.store');

// paginate
Route::get('/lms/{role}/{schoolName}/{schoolId}/daily-agenda/paginate', [TeacherDailyAgendaController::class, 'paginateTeacherDailyAgenda'])->name('lms.teacherDailyAgenda.paginate');
Route::get('/lms/{role}/{schoolName}/{schoolId}/daily-agenda/history/paginate', [TeacherDailyAgendaController::class, 'paginateTeacherDailyAgendaHistory'])->name('lms.teacherDailyAgenda.history.paginate');

// TEACHER GRADEBOOK MANAGEMENT
// views
Route::get('/lms/{role}/{schoolName}/{schoolId}/gradebook/classes', [TeacherGradebookController::class, 'teacherClassList'])->name('lms.teacherClassList.view');
Route::get('/lms/{role}/{schoolName}/{schoolId}/gradebook/classes/subject-teacher/{subjectTeacherId}', [TeacherGradebookController::class, 'gradebookManagement'])->name('lms.teacherGradebook.view');
Route::get('/lms/{role}/{schoolName}/{schoolId}/gradebook/classes/subject-teacher/{subjectTeacherId}/assessment-type/{assessmentTypeId}/student/{studentId}/preview/semester/{semester}', [GradebookAssessmentController::class, 'teacherGradebookAssessmentPreview'])->name('lms.teacherGradebookAssessment.view');

// paginate
Route::get('/lms/{role}/{schoolName}/{schoolId}/gradebook/classes/paginate', [TeacherGradebookController::class, 'paginateTeacherClassList'])->name('lms.teacherClassList.paginate');
Route::get('/lms/{role}/{schoolName}/{schoolId}/gradebook/classes/subject-teacher/{subjectTeacherId}/paginate', [TeacherGradebookController::class, 'paginateGradebookManagement'])->name('lms.teacherGradebook.paginate');
Route::get('/lms/{role}/{schoolName}/{schoolId}/gradebook/classes/subject-teacher/{subjectTeacherId}/assessment-type/{assessmentTypeId}/student/{studentId}/preview/semester/{semester}/paginate', [GradebookAssessmentController::class, 'paginateTeacherGradebookAssessment'])->name('lms.teacherGradebookAssessment.paginate');

// gradebook export
Route::get('/lms/{role}/{schoolName}/{schoolId}/gradebook/classes/subject-teacher/{subjectTeacherId}/semester/{semester}/export', [TeacherGradebookController::class, 'exportGradebook']);

// update nilai
Route::post(
    '/lms/{role}/{schoolName}/{schoolId}/gradebook/classes/subject-teacher/{subjectTeacherId}/assessment-type/{assessmentTypeId}/student/{studentId}/semester/{semester}/bulk-update',
    [GradebookAssessmentController::class, 'bulkUpdateScore']
);

// TEACHER GRADE LEDGER MANAGEMENT
// views
Route::get('/lms/{role}/{schoolName}/{schoolId}/grade-ledger/classes', [TeacherGradeLedgerController::class, 'teacherClassList'])->name('lms.teacherClassListGradeLedger.view');
Route::get('/lms/{role}/{schoolName}/{schoolId}/grade-ledger/classes/subject-teacher/{subjectTeacherId}', [TeacherGradeLedgerController::class, 'teacherGradeLedger'])->name('lms.teacherGradeLedger.view');

// paginate
Route::get('/lms/{role}/{schoolName}/{schoolId}/grade-ledger/classes/paginate', [TeacherGradeLedgerController::class, 'paginateTeacherClassList'])->name('lms.teacherClassListGradeLedger.paginate');
Route::get('/lms/{role}/{schoolName}/{schoolId}/grade-ledger/classes/subject-teacher/{subjectTeacherId}/paginate', [TeacherGradeLedgerController::class, 'paginateTeacherGradeLedger'])->name('lms.teacherGradeLedger.paginate');

// grade ledger export
Route::get('/lms/{role}/{schoolName}/{schoolId}/grade-ledger/classes/subject-teacher/{subjectTeacherId}/semester/{semester}/export', [TeacherGradeLedgerController::class, 'exportGradeLedger']);

// TEACHER ACADEMIC TRANSCRIPT MANAGEMENT
// views
Route::get('/lms/{role}/{schoolName}/{schoolId}/academic-transcript/classes', [TeacherAcademicTranscriptController::class, 'teacherClassList'])->name('lms.teacherClassListAcademicTranscript.view');
Route::get('/lms/{role}/{schoolName}/{schoolId}/academic-transcript/classes/subject-teacher/{subjectTeacherId}', [TeacherAcademicTranscriptController::class, 'teacherAcademicTranscript'])->name('lms.teacherAcademicTranscript.view');

// paginate
Route::get('/lms/{role}/{schoolName}/{schoolId}/academic-transcript/classes/paginate', [TeacherAcademicTranscriptController::class, 'paginateTeacherClassList'])->name('lms.teacherClassListAcademicTranscript.paginate');
Route::get('/lms/{role}/{schoolName}/{schoolId}/academic-transcript/classes/subject-teacher/{subjectTeacherId}/paginate', [TeacherAcademicTranscriptController::class, 'paginateTeacherAcademicTranscript'])->name('lms.teacherAcademicTranscript.paginate');

// academic transcript export
Route::get('/lms/{role}/{schoolName}/{schoolId}/academic-transcript/classes/subject-teacher/{subjectTeacherId}/export', [TeacherAcademicTranscriptController::class, 'exportAcademicTranscript']);

// Polling Teacher
Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-polling', [TeacherInformationController::class, 'teacherPollingView'])->name('lms.teacherPolling.view');
Route::post('/lms/{role}/{schoolName}/{schoolId}/teacher-polling/save', [TeacherInformationController::class, 'savePollingData'])->name('lms.teacherPolling.save');
Route::delete('/lms/{role}/{schoolName}/{schoolId}/teacher-polling/{id}', [TeacherInformationController::class, 'deletePoll'])->name('lms.teacherPolling.destroy');
Route::post('/lms/{role}/{schoolName}/{schoolId}/teacher/polling/vote', [TeacherInformationController::class, 'submitVote'])->name('lms.teacherPolling.submitVote');
Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-polling/{id}/breakdown', [TeacherInformationController::class, 'getPollingBreakdown'])->name('lms.teacherPolling.breakdown');

// Jadwal & Kalender Akademik Guru
Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-schedule-calendar', [TeacherInformationController::class, 'teacherScheduleCalendarView'])
    ->name('lms.teacherScheduleCalendar.view');

// Rute detail kelas
Route::get('/lms/guru/api/get-students/{classId}', [LmsController::class, 'getStudentsForAttendance'])
    ->name('lms.guru.get_students');
Route::post('/lms/teacher/pengumuman/store', [LmsController::class, 'storePengumuman'])->name('lms.teacher.pengumuman.store');
Route::post('/lms/student/announcement/mark-as-read', [LmsController::class, 'markAsRead'])->name('lms.student.announcement.read');
Route::get('/lms/{role}/sekolah/{schoolName}/{schoolId}/kelas/{scheduleId}', [LmsController::class, 'classDetailView'])
    ->name('lms.teacher.class.detail');
Route::post('/lms/teacher/attendance/store', [LmsController::class, 'saveAttendance'])->name('lms.teacher.attendance.store');
Route::get('/lms/teacher/tugas/{taskId}/submissions', [LmsController::class, 'getTaskSubmissions'])->name('lms.teacher.tugas.submissions');
Route::post('/lms/teacher/tugas/grades', [LmsController::class, 'saveTaskGrades'])->name('lms.teacher.tugas.grades');

// 👇 RUTE JADWAL HEADMASTER
Route::get('/lms/{schoolId}/teacher-schedule/get-data/{classId}', [HeadmasterController::class, 'getScheduleDataAjax']);
Route::post('/lms/{role}/{schoolName}/{schoolId}/teacher-schedule/save', [HeadmasterController::class, 'saveSchedule']);
Route::post('/lms/{role}/{schoolName}/{schoolId}/pengumuman/store', [HeadmasterController::class, 'storePengumuman'])->name('lms.kepsek.pengumuman.store');

        // --------------------------------------------------------------------
// ROUTES STUDENT DASHBOARD
// --------------------------------------------------------------------
Route::get('/lms/{role}/{schoolName}/{schoolId}/student/dashboard', [StudentDashboardController::class, 'index'])->name('lms.student.dashboard');
Route::post('/student/announcement/mark-read', [StudentDashboardController::class, 'markAnnouncementAsRead'])->name('lms.studentAnnouncement.markRead');

// daily reflection
Route::get('/lms/{role}/{schoolName}/{schoolId}/student/daily-reflection/paginate', [StudentDashboardController::class, 'getDailyReflection'])->name('lms.student.daily-reflection.paginate');

// submit form daily reflection
Route::post('/lms/{role}/{schoolName}/{schoolId}/student/daily-reflection/store', [StudentDashboardController::class, 'dailyReflectionStore'])->name('lms.student.daily-reflection.store');

// show & read student content
Route::get('/lms/{role}/{schoolName}/{schoolId}/student/content/{meetingId}', [StudentDashboardController::class, 'showStudentContent'])->name('student.dashboard.content');
Route::post('/lms/{role}/{schoolName}/{schoolId}/student/content/{meetingId}/mark-read', [StudentDashboardController::class, 'markStudentContentRead'])->name('student.dashboard.content.mark-read');
