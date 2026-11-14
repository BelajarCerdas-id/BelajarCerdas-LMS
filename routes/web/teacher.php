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
Route::get('/lms/{role}/{schoolName}/{schoolId}/subject-attendance/classes', [SubjectAttendanceController::class, 'teacherClassList'])->name('Lms.TeacherClassListSubjectAttendance.View');
Route::get('/lms/{role}/{schoolName}/{schoolId}/subject-attendance/classes/subject-teacher/{subjectTeacherId}/meeting-list', [SubjectAttendanceController::class, 'subjectAttendanceMeetingList'])->name('Lms.TeacherSubjectAttendanceMeetingList.View');
Route::get('/lms/{role}/{schoolName}/{schoolId}/subject-attendance/classes/subject-teacher/{subjectTeacherId}/meeting-list/{meetingNumber}/semester/{semester}/meeting-management', [SubjectAttendanceController::class, 'subjectAttendanceMeetingManagement'])->name('Lms.TeacherSubjectAttendanceMeetingManagement.View');

// subject attendance data
Route::get('/lms/{role}/{schoolName}/{schoolId}/subject-attendance/classes/subject-teacher/{subjectTeacherId}/data', [SubjectAttendanceController::class, 'getSubjectAttendanceData'])->name('Lms.TeacherSubjectAttendance.Data');

// crud
// announcement
Route::post('/lms/{role}/{schoolName}/{schoolId}/subject-attendance/classes/subject-teacher/{subjectTeacherId}/meeting-list/{meetingNumber}/semester/{semester}/meeting-management/announcement-store', [SubjectAttendanceController::class, 'announcementStore'])->name('Lms.TeacherSubjectAttendanceMeetingManagement.Announcement.Store');

// submit attendance student
Route::post('/lms/{role}/{schoolName}/{schoolId}/subject-attendance/classes/subject-teacher/{subjectTeacherId}/meeting-list/{meetingNumber}/semester/{semester}/student-attendance/store', [SubjectAttendanceController::class, 'storeStudentAttendance'])->name('Lms.StudentAttendance.Store');

// paginate
Route::get('/lms/{role}/{schoolName}/{schoolId}/subject-attendance/classes/paginate', [SubjectAttendanceController::class, 'paginateTeacherClassList'])->name('Lms.TeacherClassListSubjectAttendance.Paginate');
Route::get('/lms/{role}/{schoolName}/{schoolId}/subject-attendance/classes/subject-teacher/{subjectTeacherId}/meeting-list/semester/{semester}/paginate', [SubjectAttendanceController::class, 'paginateSubjectAttendanceMeetingList'])->name('Lms.TeacherSubjectAttendanceMeetingList.Paginate');
Route::get('/lms/{role}/{schoolName}/{schoolId}/subject-attendance/classes/subject-teacher/{subjectTeacherId}/meeting-list/{meetingNumber}/semester/{semester}/meeting-management/announcement/paginate', [SubjectAttendanceController::class, 'paginateAnnouncementList'])->name('Lms.TeacherSubjectAttendanceMeetingManagement.Announcement.Paginate');
Route::get('/lms/{role}/{schoolName}/{schoolId}/subject-attendance/classes/subject-teacher/{subjectTeacherId}/meeting-list/{meetingNumber}/semester/{semester}/meeting-management/material/paginate', [SubjectAttendanceController::class, 'paginateMaterialList'])->name('Lms.TeacherSubjectAttendanceMeetingManagement.Material.Paginate');
Route::get('/lms/{role}/{schoolName}/{schoolId}/subject-attendance/classes/subject-teacher/{subjectTeacherId}/meeting-list/{meetingNumber}/semester/{semester}/meeting-management/assessment/paginate', [SubjectAttendanceController::class, 'paginateAssessmentList'])->name('Lms.TeacherSubjectAttendanceMeetingManagement.Assessment.Paginate');
Route::get('/lms/{role}/{schoolName}/{schoolId}/subject-attendance/classes/subject-teacher/{subjectTeacherId}/meeting-list/{meetingNumber}/semester/{semester}/student-attendance/paginate', [SubjectAttendanceController::class, 'paginateStudentAttendance'])->name('Lms.StudentAttendance.Paginate');
Route::get('/lms/{role}/{schoolName}/{schoolId}/subject-attendance/classes/subject-teacher/{subjectTeacherId}/meeting-list/{meetingNumber}/semester/{semester}/chart', [SubjectAttendanceController::class, 'getAttendanceChart']);

// TEACHER DAILY AGENDA
Route::get('/lms/{role}/{schoolName}/{schoolId}/daily-agenda', [TeacherDailyAgendaController::class, 'index'])->name('Lms.TeacherDailyAgenda.View');
Route::get('/lms/{role}/{schoolName}/{schoolId}/daily-agenda/{dayOfWeek}/class/{classId}/subject-teacher/{subjectId}/form', [TeacherDailyAgendaController::class, 'teacherDailyAgendaForm'])->name('Lms.TeacherDailyAgendaForm.View');
Route::get('/lms/{role}/{schoolName}/{schoolId}/daily-agenda/history', [TeacherDailyAgendaController::class, 'teacherDailyAgendaHistory'])->name('Lms.TeacherDailyAgenda.History.View');

// load detail data
Route::get('/lms/{role}/{schoolName}/{schoolId}/daily-agenda/{dayOfWeek}/class/{classId}/subject-teacher/{subjectId}/form-detail', [TeacherDailyAgendaController::class, 'teacherDailyAgendaFormDetail'])->name('Lms.TeacherDailyAgendaForm.Detail');

// crud
Route::post('/lms/{role}/{schoolName}/{schoolId}/daily-agenda/{dayOfWeek}/class/{classId}/subject-teacher/{subjectId}/form-detail/submit', [TeacherDailyAgendaController::class, 'teacherDailyAgendaSubmitForm'])->name('Lms.TeacherDailyAgendaForm.Store');

// paginate
Route::get('/lms/{role}/{schoolName}/{schoolId}/daily-agenda/paginate', [TeacherDailyAgendaController::class, 'paginateTeacherDailyAgenda'])->name('Lms.TeacherDailyAgenda.Paginate');
Route::get('/lms/{role}/{schoolName}/{schoolId}/daily-agenda/history/paginate', [TeacherDailyAgendaController::class, 'paginateTeacherDailyAgendaHistory'])->name('Lms.TeacherDailyAgenda.History.Paginate');

// TEACHER GRADEBOOK MANAGEMENT
// views
Route::get('/lms/{role}/{schoolName}/{schoolId}/gradebook/classes', [TeacherGradebookController::class, 'teacherClassList'])->name('Lms.TeacherClassList.View');
Route::get('/lms/{role}/{schoolName}/{schoolId}/gradebook/classes/subject-teacher/{subjectTeacherId}', [TeacherGradebookController::class, 'gradebookManagement'])->name('Lms.TeacherGradebook.View');
Route::get('/lms/{role}/{schoolName}/{schoolId}/gradebook/classes/subject-teacher/{subjectTeacherId}/assessment-type/{assessmentTypeId}/student/{studentId}/preview/semester/{semester}', [GradebookAssessmentController::class, 'teacherGradebookAssessmentPreview'])->name('Lms.TeacherGradebookAssessment.View');

// paginate
Route::get('/lms/{role}/{schoolName}/{schoolId}/gradebook/classes/paginate', [TeacherGradebookController::class, 'paginateTeacherClassList'])->name('Lms.TeacherClassList.Paginate');
Route::get('/lms/{role}/{schoolName}/{schoolId}/gradebook/classes/subject-teacher/{subjectTeacherId}/paginate', [TeacherGradebookController::class, 'paginateGradebookManagement'])->name('Lms.TeacherGradebook.Paginate');
Route::get('/lms/{role}/{schoolName}/{schoolId}/gradebook/classes/subject-teacher/{subjectTeacherId}/assessment-type/{assessmentTypeId}/student/{studentId}/preview/semester/{semester}/paginate', [GradebookAssessmentController::class, 'paginateTeacherGradebookAssessment'])->name('Lms.TeacherGradebookAssessment.Paginate');

// gradebook export
Route::get('/lms/{role}/{schoolName}/{schoolId}/gradebook/classes/subject-teacher/{subjectTeacherId}/semester/{semester}/export', [TeacherGradebookController::class, 'exportGradebook']);

// update nilai
Route::post(
    '/lms/{role}/{schoolName}/{schoolId}/gradebook/classes/subject-teacher/{subjectTeacherId}/assessment-type/{assessmentTypeId}/student/{studentId}/semester/{semester}/bulk-update',
    [GradebookAssessmentController::class, 'bulkUpdateScore']
);

// TEACHER GRADE LEDGER MANAGEMENT
// views
Route::get('/lms/{role}/{schoolName}/{schoolId}/grade-ledger/classes', [TeacherGradeLedgerController::class, 'teacherClassList'])->name('Lms.TeacherClassListGradeLedger.View');
Route::get('/lms/{role}/{schoolName}/{schoolId}/grade-ledger/classes/subject-teacher/{subjectTeacherId}', [TeacherGradeLedgerController::class, 'teacherGradeLedger'])->name('Lms.TeacherGradeLedger.View');

// paginate
Route::get('/lms/{role}/{schoolName}/{schoolId}/grade-ledger/classes/paginate', [TeacherGradeLedgerController::class, 'paginateTeacherClassList'])->name('Lms.TeacherClassListGradeLedger.Paginate');
Route::get('/lms/{role}/{schoolName}/{schoolId}/grade-ledger/classes/subject-teacher/{subjectTeacherId}/paginate', [TeacherGradeLedgerController::class, 'paginateTeacherGradeLedger'])->name('Lms.TeacherGradeLedger.Paginate');

// grade ledger export
Route::get('/lms/{role}/{schoolName}/{schoolId}/grade-ledger/classes/subject-teacher/{subjectTeacherId}/semester/{semester}/export', [TeacherGradeLedgerController::class, 'exportGradeLedger']);

// TEACHER ACADEMIC TRANSCRIPT MANAGEMENT
// views
Route::get('/lms/{role}/{schoolName}/{schoolId}/academic-transcript/classes', [TeacherAcademicTranscriptController::class, 'teacherClassList'])->name('Lms.TeacherClassListAcademicTranscript.View');
Route::get('/lms/{role}/{schoolName}/{schoolId}/academic-transcript/classes/subject-teacher/{subjectTeacherId}', [TeacherAcademicTranscriptController::class, 'teacherAcademicTranscript'])->name('Lms.TeacherAcademicTranscript.View');

// paginate
Route::get('/lms/{role}/{schoolName}/{schoolId}/academic-transcript/classes/paginate', [TeacherAcademicTranscriptController::class, 'paginateTeacherClassList'])->name('Lms.TeacherClassListAcademicTranscript.Paginate');
Route::get('/lms/{role}/{schoolName}/{schoolId}/academic-transcript/classes/subject-teacher/{subjectTeacherId}/paginate', [TeacherAcademicTranscriptController::class, 'paginateTeacherAcademicTranscript'])->name('Lms.TeacherAcademicTranscript.Paginate');

// academic transcript export
Route::get('/lms/{role}/{schoolName}/{schoolId}/academic-transcript/classes/subject-teacher/{subjectTeacherId}/export', [TeacherAcademicTranscriptController::class, 'exportAcademicTranscript']);

// Polling Teacher
Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-polling', [TeacherInformationController::class, 'teacherPollingView'])->name('Lms.TeacherPolling.View');
Route::post('/lms/{role}/{schoolName}/{schoolId}/teacher-polling/save', [TeacherInformationController::class, 'savePollingData'])->name('Lms.TeacherPolling.Save');
Route::delete('/lms/{role}/{schoolName}/{schoolId}/teacher-polling/{id}', [TeacherInformationController::class, 'deletePoll'])->name('Lms.TeacherPolling.Destroy');
Route::post('/lms/{role}/{schoolName}/{schoolId}/teacher/polling/vote', [TeacherInformationController::class, 'submitVote'])->name('Lms.TeacherPolling.SubmitVote');
Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-polling/{id}/breakdown', [TeacherInformationController::class, 'getPollingBreakdown'])->name('Lms.TeacherPolling.Breakdown');

// Jadwal & Kalender Akademik Guru
Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-schedule-calendar', [TeacherInformationController::class, 'teacherScheduleCalendarView'])
    ->name('Lms.TeacherScheduleCalendar.View');

// Rute detail kelas
Route::get('/lms/guru/api/get-students/{classId}', [LmsController::class, 'getStudentsForAttendance'])
    ->name('Lms.Guru.GetStudents');
Route::post('/lms/teacher/pengumuman/store', [LmsController::class, 'storePengumuman'])->name('Lms.Teacher.Pengumuman.Store');
Route::post('/lms/student/announcement/mark-as-read', [LmsController::class, 'markAsRead'])->name('Lms.Student.Announcement.Read');
Route::get('/lms/{role}/sekolah/{schoolName}/{schoolId}/kelas/{scheduleId}', [LmsController::class, 'classDetailView'])
    ->name('Lms.Teacher.Class.Detail');
Route::post('/lms/teacher/attendance/store', [LmsController::class, 'saveAttendance'])->name('Lms.Teacher.Attendance.Store');
Route::get('/lms/teacher/tugas/{taskId}/submissions', [LmsController::class, 'getTaskSubmissions'])->name('Lms.Teacher.Tugas.Submissions');
Route::post('/lms/teacher/tugas/grades', [LmsController::class, 'saveTaskGrades'])->name('Lms.Teacher.Tugas.Grades');

// 👇 RUTE JADWAL HEADMASTER
Route::get('/lms/{schoolId}/teacher-schedule/get-data/{classId}', [HeadmasterController::class, 'getScheduleDataAjax']);
Route::post('/lms/{role}/{schoolName}/{schoolId}/teacher-schedule/save', [HeadmasterController::class, 'saveSchedule']);
Route::post('/lms/{role}/{schoolName}/{schoolId}/pengumuman/store', [HeadmasterController::class, 'storePengumuman'])->name('Lms.Kepsek.Pengumuman.Store');

        // --------------------------------------------------------------------
// ROUTES STUDENT DASHBOARD
// --------------------------------------------------------------------
Route::get('/lms/{role}/{schoolName}/{schoolId}/student/dashboard', [StudentDashboardController::class, 'index'])->name('Lms.Student.Dashboard');
Route::post('/student/announcement/mark-read', [StudentDashboardController::class, 'markAnnouncementAsRead'])->name('Lms.StudentAnnouncement.MarkRead');

// daily reflection
Route::get('/lms/{role}/{schoolName}/{schoolId}/student/daily-reflection/paginate', [StudentDashboardController::class, 'getDailyReflection'])->name('Lms.Student.DailyReflection.Paginate');

// submit form daily reflection
Route::post('/lms/{role}/{schoolName}/{schoolId}/student/daily-reflection/store', [StudentDashboardController::class, 'dailyReflectionStore'])->name('Lms.Student.DailyReflection.Store');

// show & read student content
Route::get('/lms/{role}/{schoolName}/{schoolId}/student/content/{meetingId}', [StudentDashboardController::class, 'showStudentContent'])->name('Student.Dashboard.Content');
Route::post('/lms/{role}/{schoolName}/{schoolId}/student/content/{meetingId}/mark-read', [StudentDashboardController::class, 'markStudentContentRead'])->name('Student.Dashboard.Content.MarkRead');
