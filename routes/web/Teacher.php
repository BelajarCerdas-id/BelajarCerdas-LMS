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
Route::get('/lms/{role}/{schoolName}/{schoolId}/subject-attendance/classes', [SubjectAttendanceController::class, 'teacherClassList'])->name('lms.teacher_class_list_subject_attendance.view');
Route::get('/lms/{role}/{schoolName}/{schoolId}/subject-attendance/classes/subject-teacher/{subjectTeacherId}/meeting-list', [SubjectAttendanceController::class, 'subjectAttendanceMeetingList'])->name('lms.teacher_subject_attendance_meeting_list.view');
Route::get('/lms/{role}/{schoolName}/{schoolId}/subject-attendance/classes/subject-teacher/{subjectTeacherId}/meeting-list/{meetingNumber}/semester/{semester}/meeting-management', [SubjectAttendanceController::class, 'subjectAttendanceMeetingManagement'])->name('lms.teacher_subject_attendance_meeting_management.view');

// subject attendance data
Route::get('/lms/{role}/{schoolName}/{schoolId}/subject-attendance/classes/subject-teacher/{subjectTeacherId}/data', [SubjectAttendanceController::class, 'getSubjectAttendanceData'])->name('lms.teacher_subject_attendance.data');

// crud
// announcement
Route::post('/lms/{role}/{schoolName}/{schoolId}/subject-attendance/classes/subject-teacher/{subjectTeacherId}/meeting-list/{meetingNumber}/semester/{semester}/meeting-management/announcement-store', [SubjectAttendanceController::class, 'announcementStore'])->name('lms.teacher_subject_attendance_meeting_management.announcement.store');

// submit attendance student
Route::post('/lms/{role}/{schoolName}/{schoolId}/subject-attendance/classes/subject-teacher/{subjectTeacherId}/meeting-list/{meetingNumber}/semester/{semester}/student-attendance/store', [SubjectAttendanceController::class, 'storeStudentAttendance'])->name('lms.student_attendance.store');

// paginate
Route::get('/lms/{role}/{schoolName}/{schoolId}/subject-attendance/classes/paginate', [SubjectAttendanceController::class, 'paginateTeacherClassList'])->name('lms.teacher_class_list_subject_attendance.paginate');
Route::get('/lms/{role}/{schoolName}/{schoolId}/subject-attendance/classes/subject-teacher/{subjectTeacherId}/meeting-list/semester/{semester}/paginate', [SubjectAttendanceController::class, 'paginateSubjectAttendanceMeetingList'])->name('lms.teacher_subject_attendance_meeting_list.paginate');
Route::get('/lms/{role}/{schoolName}/{schoolId}/subject-attendance/classes/subject-teacher/{subjectTeacherId}/meeting-list/{meetingNumber}/semester/{semester}/meeting-management/announcement/paginate', [SubjectAttendanceController::class, 'paginateAnnouncementList'])->name('lms.teacher_subject_attendance_meeting_management.announcement.paginate');
Route::get('/lms/{role}/{schoolName}/{schoolId}/subject-attendance/classes/subject-teacher/{subjectTeacherId}/meeting-list/{meetingNumber}/semester/{semester}/meeting-management/material/paginate', [SubjectAttendanceController::class, 'paginateMaterialList'])->name('lms.teacher_subject_attendance_meeting_management.material.paginate');
Route::get('/lms/{role}/{schoolName}/{schoolId}/subject-attendance/classes/subject-teacher/{subjectTeacherId}/meeting-list/{meetingNumber}/semester/{semester}/meeting-management/assessment/paginate', [SubjectAttendanceController::class, 'paginateAssessmentList'])->name('lms.teacher_subject_attendance_meeting_management.assessment.paginate');
Route::get('/lms/{role}/{schoolName}/{schoolId}/subject-attendance/classes/subject-teacher/{subjectTeacherId}/meeting-list/{meetingNumber}/semester/{semester}/student-attendance/paginate', [SubjectAttendanceController::class, 'paginateStudentAttendance'])->name('lms.student_attendance.paginate');
Route::get('/lms/{role}/{schoolName}/{schoolId}/subject-attendance/classes/subject-teacher/{subjectTeacherId}/meeting-list/{meetingNumber}/semester/{semester}/chart', [SubjectAttendanceController::class, 'getAttendanceChart']);

// TEACHER DAILY AGENDA
Route::get('/lms/{role}/{schoolName}/{schoolId}/daily-agenda', [TeacherDailyAgendaController::class, 'index'])->name('lms.teacher_daily_agenda.view');
Route::get('/lms/{role}/{schoolName}/{schoolId}/daily-agenda/{dayOfWeek}/class/{classId}/subject-teacher/{subjectId}/form', [TeacherDailyAgendaController::class, 'teacherDailyAgendaForm'])->name('lms.teacher_daily_agenda_form.view');
Route::get('/lms/{role}/{schoolName}/{schoolId}/daily-agenda/history', [TeacherDailyAgendaController::class, 'teacherDailyAgendaHistory'])->name('lms.teacher_daily_agenda.history.view');

// load detail data
Route::get('/lms/{role}/{schoolName}/{schoolId}/daily-agenda/{dayOfWeek}/class/{classId}/subject-teacher/{subjectId}/form-detail', [TeacherDailyAgendaController::class, 'teacherDailyAgendaFormDetail'])->name('lms.teacher_daily_agenda_form.detail');

// crud
Route::post('/lms/{role}/{schoolName}/{schoolId}/daily-agenda/{dayOfWeek}/class/{classId}/subject-teacher/{subjectId}/form-detail/submit', [TeacherDailyAgendaController::class, 'teacherDailyAgendaSubmitForm'])->name('lms.teacher_daily_agenda_form.store');

// paginate
Route::get('/lms/{role}/{schoolName}/{schoolId}/daily-agenda/paginate', [TeacherDailyAgendaController::class, 'paginateTeacherDailyAgenda'])->name('lms.teacher_daily_agenda.paginate');
Route::get('/lms/{role}/{schoolName}/{schoolId}/daily-agenda/history/paginate', [TeacherDailyAgendaController::class, 'paginateTeacherDailyAgendaHistory'])->name('lms.teacher_daily_agenda.history.paginate');

// TEACHER GRADEBOOK MANAGEMENT
// views
Route::get('/lms/{role}/{schoolName}/{schoolId}/gradebook/classes', [TeacherGradebookController::class, 'teacherClassList'])->name('lms.teacher_class_list.view');
Route::get('/lms/{role}/{schoolName}/{schoolId}/gradebook/classes/subject-teacher/{subjectTeacherId}', [TeacherGradebookController::class, 'gradebookManagement'])->name('lms.teacher_gradebook.view');
Route::get('/lms/{role}/{schoolName}/{schoolId}/gradebook/classes/subject-teacher/{subjectTeacherId}/assessment-type/{assessmentTypeId}/student/{studentId}/preview/semester/{semester}', [GradebookAssessmentController::class, 'teacherGradebookAssessmentPreview'])->name('lms.teacher_gradebook_assessment.view');

// paginate
Route::get('/lms/{role}/{schoolName}/{schoolId}/gradebook/classes/paginate', [TeacherGradebookController::class, 'paginateTeacherClassList'])->name('lms.teacher_class_list.paginate');
Route::get('/lms/{role}/{schoolName}/{schoolId}/gradebook/classes/subject-teacher/{subjectTeacherId}/paginate', [TeacherGradebookController::class, 'paginateGradebookManagement'])->name('lms.teacher_gradebook.paginate');
Route::get('/lms/{role}/{schoolName}/{schoolId}/gradebook/classes/subject-teacher/{subjectTeacherId}/assessment-type/{assessmentTypeId}/student/{studentId}/preview/semester/{semester}/paginate', [GradebookAssessmentController::class, 'paginateTeacherGradebookAssessment'])->name('lms.teacher_gradebook_assessment.paginate');

// gradebook export
Route::get('/lms/{role}/{schoolName}/{schoolId}/gradebook/classes/subject-teacher/{subjectTeacherId}/semester/{semester}/export', [TeacherGradebookController::class, 'exportGradebook']);

// update nilai
Route::post(
    '/lms/{role}/{schoolName}/{schoolId}/gradebook/classes/subject-teacher/{subjectTeacherId}/assessment-type/{assessmentTypeId}/student/{studentId}/semester/{semester}/bulk-update',
    [GradebookAssessmentController::class, 'bulkUpdateScore']
);

// TEACHER GRADE LEDGER MANAGEMENT
// views
Route::get('/lms/{role}/{schoolName}/{schoolId}/grade-ledger/classes', [TeacherGradeLedgerController::class, 'teacherClassList'])->name('lms.teacher_class_list_grade_ledger.view');
Route::get('/lms/{role}/{schoolName}/{schoolId}/grade-ledger/classes/subject-teacher/{subjectTeacherId}', [TeacherGradeLedgerController::class, 'teacherGradeLedger'])->name('lms.teacher_grade_ledger.view');

// paginate
Route::get('/lms/{role}/{schoolName}/{schoolId}/grade-ledger/classes/paginate', [TeacherGradeLedgerController::class, 'paginateTeacherClassList'])->name('lms.teacher_class_list_grade_ledger.paginate');
Route::get('/lms/{role}/{schoolName}/{schoolId}/grade-ledger/classes/subject-teacher/{subjectTeacherId}/paginate', [TeacherGradeLedgerController::class, 'paginateTeacherGradeLedger'])->name('lms.teacher_grade_ledger.paginate');

// grade ledger export
Route::get('/lms/{role}/{schoolName}/{schoolId}/grade-ledger/classes/subject-teacher/{subjectTeacherId}/semester/{semester}/export', [TeacherGradeLedgerController::class, 'exportGradeLedger']);

// TEACHER ACADEMIC TRANSCRIPT MANAGEMENT
// views
Route::get('/lms/{role}/{schoolName}/{schoolId}/academic-transcript/classes', [TeacherAcademicTranscriptController::class, 'teacherClassList'])->name('lms.teacher_class_list_academic_transcript.view');
Route::get('/lms/{role}/{schoolName}/{schoolId}/academic-transcript/classes/subject-teacher/{subjectTeacherId}', [TeacherAcademicTranscriptController::class, 'teacherAcademicTranscript'])->name('lms.teacher_academic_transcript.view');

// paginate
Route::get('/lms/{role}/{schoolName}/{schoolId}/academic-transcript/classes/paginate', [TeacherAcademicTranscriptController::class, 'paginateTeacherClassList'])->name('lms.teacher_class_list_academic_transcript.paginate');
Route::get('/lms/{role}/{schoolName}/{schoolId}/academic-transcript/classes/subject-teacher/{subjectTeacherId}/paginate', [TeacherAcademicTranscriptController::class, 'paginateTeacherAcademicTranscript'])->name('lms.teacher_academic_transcript.paginate');

// academic transcript export
Route::get('/lms/{role}/{schoolName}/{schoolId}/academic-transcript/classes/subject-teacher/{subjectTeacherId}/export', [TeacherAcademicTranscriptController::class, 'exportAcademicTranscript']);

// Polling Teacher
Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-polling', [TeacherInformationController::class, 'teacherPollingView'])->name('lms.teacher_polling.view');
Route::post('/lms/{role}/{schoolName}/{schoolId}/teacher-polling/save', [TeacherInformationController::class, 'savePollingData'])->name('lms.teacher_polling.save');
Route::delete('/lms/{role}/{schoolName}/{schoolId}/teacher-polling/{id}', [TeacherInformationController::class, 'deletePoll'])->name('lms.teacher_polling.destroy');
Route::post('/lms/{role}/{schoolName}/{schoolId}/teacher/polling/vote', [TeacherInformationController::class, 'submitVote'])->name('lms.teacher_polling.submit_vote');
Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-polling/{id}/breakdown', [TeacherInformationController::class, 'getPollingBreakdown'])->name('lms.teacher_polling.breakdown');

// Jadwal & Kalender Akademik Guru
Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-schedule-calendar', [TeacherInformationController::class, 'teacherScheduleCalendarView'])
    ->name('lms.teacher_schedule_calendar.view');

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
Route::post('/student/announcement/mark-read', [StudentDashboardController::class, 'markAnnouncementAsRead'])->name('lms.student_announcement.mark_read');

// daily reflection
Route::get('/lms/{role}/{schoolName}/{schoolId}/student/daily-reflection/paginate', [StudentDashboardController::class, 'getDailyReflection'])->name('lms.student.daily_reflection.paginate');

// submit form daily reflection
Route::post('/lms/{role}/{schoolName}/{schoolId}/student/daily-reflection/store', [StudentDashboardController::class, 'dailyReflectionStore'])->name('lms.student.daily_reflection.store');

// show & read student content
Route::get('/lms/{role}/{schoolName}/{schoolId}/student/content/{meetingId}', [StudentDashboardController::class, 'showStudentContent'])->name('student.dashboard.content');
Route::post('/lms/{role}/{schoolName}/{schoolId}/student/content/{meetingId}/mark-read', [StudentDashboardController::class, 'markStudentContentRead'])->name('student.dashboard.content.mark_read');
