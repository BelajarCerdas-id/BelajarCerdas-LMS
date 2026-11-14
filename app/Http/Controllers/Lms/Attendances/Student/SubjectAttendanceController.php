<?php

namespace App\Http\Controllers\Lms\Attendances\Student;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\LmsMeetingContent;
use App\Models\SchoolAssessment;
use App\Models\SchoolAssessmentType;
use App\Models\SchoolPartner;
use App\Models\SubjectAttendance;
use App\Models\TeacherMapel;
use App\Models\UserAccount;
use App\Services\ClassName\ClassNameService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SubjectAttendanceController extends Controller
{
    private function extractClassLevel($className)
    {
        $classNameService = new ClassNameService();
        return $classNameService->extractClassLevel($className);
    }

    private function resolveClassLevel($class): ?int
    {
        $classNameService = new ClassNameService();
        return $classNameService->resolveClassLevel($class);
    }

    // function teacher class list view
    public function teacherClassList($role, $schoolName, $schoolId)
    {
        return view('features.lms.teacher.subject-attendance.teacher-class-list', compact('role', 'schoolName', 'schoolId'));
    }

    // function paginate teacher class list
    public function paginateTeacherClassList(Request $request, $role, $schoolName, $schoolId)
    {
        $user = Auth::user();

        $schoolPartner = SchoolPartner::findOrFail($schoolId);
        $jenjang = strtoupper($schoolPartner->jenjang_sekolah);

        // DEFAULT LEVEL BERDASARKAN JENJANG
        $startLevelMap = [
            'SD'  => 1,  'MI'  => 1,
            'SMP' => 7,  'MTS' => 7,
            'SMA' => 10, 'SMK' => 10,
            'MA'  => 10, 'MAK' => 10,
        ];

        $defaultLevel = $startLevelMap[$jenjang] ?? 1;
        
        $subjectTeacher = TeacherMapel::with(['Mapel', 'SchoolClass' => function ($query) {
                $query->withCount('StudentSchoolClass');
            },
            'SchoolClass.UserAccount.SchoolStaffProfile'
        ])
        ->where('user_id', $user->id)->where('is_active', 1)->get();

        // TAHUN AJARAN
        $tahunAjaran = $subjectTeacher->pluck('SchoolClass.tahun_ajaran')->unique()->sortDesc()->values();

        $searchYear = $request->filled('search_year') ? $request->search_year : ($tahunAjaran->first() ?? null);

        // FILTER BERDASARKAN TAHUN AJARAN
        $schoolClasses = $subjectTeacher->where('SchoolClass.tahun_ajaran', $searchYear)->values();

        // LEVEL KELAS UNIK
        $classLevels = $schoolClasses->pluck('SchoolClass.class_name')->map(fn($c) => (int) $this->extractClassLevel($c))->unique()->sort()->values();

        $selectedClass = $request->filled('search_class') ? $this->resolveClassLevel($request->search_class) : ($classLevels->first() ?? $defaultLevel);

        // FILTER ROMBEL SESUAI LEVEL
        $schoolClasses = $schoolClasses->filter(fn($item) => (int)$this->extractClassLevel($item->SchoolClass->class_name) === $selectedClass)->values();

        // AMBIL MAPEL GURU
        $subjects = $schoolClasses->unique('mapel_id')->map(function ($item) {
            return [
                'id' => $item->mapel_id,
                'name' => $item->Mapel->mata_pelajaran ?? '-',
            ];
        })->values();

        // Filter berdasarkan level kelas
        if ($selectedClass) {
            $subjectTeacher = $subjectTeacher->filter(function ($item) use ($selectedClass) {

                if (!$item || !$item->SchoolClass->class_name) {
                    return false;
                }

                return $this->extractClassLevel($item->SchoolClass->class_name) == $selectedClass;
            });
        }

        $searchSubject = $request->filled('search_subject') ? (int) $request->search_subject : null;

        if ($searchSubject) {
            $schoolClasses = $schoolClasses->filter(function ($item) use ($searchSubject) {
                return $item->mapel_id == $searchSubject;
            })->values();
        }

        return response()->json([
            'data' => $schoolClasses,
            'tahunAjaran'   => $tahunAjaran,
            'selectedYear'  => $searchYear,
            'selectedClass' => $selectedClass,
            'className'     => $classLevels,
            'subject' => $subjects,
            'subjectAttendanceMeetingList' => '/lms/:role/:schoolName/:schoolId/subject-attendance/classes/subject-teacher/:subjectTeacherId/meeting-list',
        ]);
    }

    // function subject attendance meeting list view
    public function subjectAttendanceMeetingList($role, $schoolName, $schoolId, $subjectTeacherId)
    {
        $subjectTeacher = TeacherMapel::with(['Mapel', 'SchoolClass'])->findOrFail($subjectTeacherId);

        return view('features.lms.teacher.subject-attendance.subject-attendance-meeting-list', compact('role', 'schoolName', 'schoolId', 'subjectTeacherId', 'subjectTeacher'));
    }

    // function paginate subject attendance meeting list
    public function paginateSubjectAttendanceMeetingList(Request $request, $role, $schoolName, $schoolId, $subjectTeacherId, $semester)
    {
        $subjectTeacher = TeacherMapel::with(['Mapel', 'SchoolClass'])->findOrFail($subjectTeacherId);

        $meetings = LmsMeetingContent::with(['LmsContent.SchoolLmsContent' => function ($query) use ($schoolId) {
            $query->where('school_partner_id', $schoolId)->where('is_active', true);
        }])->where('is_active', true)->whereHas('LmsContent', function ($query) use ($schoolId) {
            $query->where('is_active', 1); // ambil content global aktif

            $query->where(function ($q) use ($schoolId) {

                // Jika ada override untuk sekolah
                $q->whereHas('SchoolLmsContent', function ($qOverride) use ($schoolId) {
                    $qOverride->where('school_partner_id', $schoolId)->where('is_active', 1);
                })

                // Jika tidak ada override, pakai global
                ->orWhere(function ($qGlobal) use ($schoolId) {
                    $qGlobal->whereNull('school_partner_id')->whereDoesntHave('SchoolLmsContent', function ($qCheck) use ($schoolId) {
                        $qCheck->where('school_partner_id', $schoolId);
                    });
                });
            });
        })->where('school_class_id', $subjectTeacher->SchoolClass->id)->where('mapel_id', $subjectTeacher->mapel_id)->where('semester', $semester)
        ->orderBy('meeting_number')->get()->groupBy('meeting_number')->map(function ($items) {
            return [
                'meeting_id'     => $items->first()->id,
                'meeting_number' => $items->first()->meeting_number,
                'meeting_date'   => $items->first()->meeting_date,
                'contents'       => $items->map(function ($item) {
                    return [
                        'service' => $item->Service->name ?? '-',
                        'content_id' => $item->lms_content_id
                    ];
                })
            ];
        })
        ->values();

        return response()->json([
            'data' => $meetings,
            'subjectAttendanceMeetingManagement' => '/lms/:role/:schoolName/:schoolId/subject-attendance/classes/subject-teacher/:subjectTeacherId/meeting-list/:meetingNumber/semester/:semester/meeting-management',
        ]);
    }

    // function subject attendance meeting management view
    public function subjectAttendanceMeetingManagement($role, $schoolName, $schoolId, $subjectTeacherId, $meetingNumber, $semester)
    {
        $subjectTeacher = TeacherMapel::with(['Mapel', 'SchoolClass'])->findOrFail($subjectTeacherId);

        return view('features.lms.teacher.subject-attendance.subject-attendance-meeting-management', compact('role', 'schoolName', 'schoolId', 'subjectTeacher', 'meetingNumber',
        'subjectTeacherId', 'semester', 'meetingNumber'));
    }

    // function paginate announcement list
    public function paginateAnnouncementList(Request $request, $role, $schoolName, $schoolId, $subjectTeacherId, $meetingNumber, $semester)
    {
        $subjectTeacher = TeacherMapel::with(['Mapel', 'SchoolClass'])->findOrFail($subjectTeacherId);

        $getAnnouncement = Announcement::where('school_partner_id', $schoolId)->where(function($query) use ($subjectTeacher) {
            $query->where('target_class_id', $subjectTeacher->SchoolClass->id)->orWhereNull('target_class_id'); 
        })->orderBy('created_at', 'desc')->get();

        return response()->json([
            'data' => $getAnnouncement,
        ]);
    }

    // function paginate material list
    public function paginateMaterialList(Request $request, $role, $schoolName, $schoolId, $subjectTeacherId, $meetingNumber, $semester)
    {
        $subjectTeacher = TeacherMapel::with(['Mapel', 'SchoolClass'])->findOrFail($subjectTeacherId);

        $meetingContents = LmsMeetingContent::with(['LmsContent', 'Service', 'LmsContent.LmsContentItem'])->where('school_class_id', $subjectTeacher->SchoolClass->id)
        ->where('mapel_id', $subjectTeacher->mapel_id)->where('semester', $semester)->where('meeting_number', $meetingNumber)->where('is_active', true)
        ->orderBy('created_at', 'desc')->get();

        return response()->json([
            'data' => $meetingContents,
        ]);
    }

    public function paginateAssessmentList(Request $request, $role, $schoolName, $schoolId, $subjectTeacherId, $meetingNumber, $semester)
    {
        $subjectTeacher = TeacherMapel::with(['Mapel', 'SchoolClass'])->findOrFail($subjectTeacherId);

        $query = SchoolAssessment::with(['Mapel', 'SchoolClass', 'SchoolAssessmentType'])->where('school_class_id', $subjectTeacher->SchoolClass->id)
        ->where('mapel_id', $subjectTeacher->mapel_id)->where('semester', $semester);

        // FILTER TYPE
        if ($request->assessment_type_id) {
            $query->where('assessment_type_id', $request->assessment_type_id);
        }

        $assessments = $query->orderBy('created_at', 'desc')->get();

        // TYPE FILTER
        $assessmentTypes = SchoolAssessmentType::where('school_partner_id', $schoolId)->where('is_active', true)->orderBy('name')->get();

        return response()->json([
            'data' => $assessments,
            'assessment_types' => $assessmentTypes,
            'assessmentGradingStudentList' => '/lms/:role/:schoolName/:schoolId/assessment-grading/:assessmentId/mode/:mode/student-list',
        ]);
    }

    // function announcement store
    public function announcementStore(Request $request, $role, $schoolName, $schoolId, $subjectTeacherId, $meetingNumber, $semester)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'type' => 'required',
            'content' => 'required',
        ], [
            'title.required' => 'Harap isi judul pengumuman.',
            'type.required' => 'Harap pilih jenis pengumuman.',
            'content.required' => 'Harap isi konten pengumuman.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }
        
        try {
            $user = Auth::user();
            
            $schoolId = $request->school_id ?? $user->school_partner_id ?? 1;

            $Announcement = Announcement::create([
                'school_partner_id' => $schoolId,
                'target_class_id' => $request->class_id, // Bisa null jika global, atau ID kelas jika spesifik
                'author_id' => $user->id,
                'author_role' => $user->role,
                'target' => 'Siswa',
                'title' => $request->title,
                'type' => $request->type,
                'content' => $request->input('content'),
            ]);

            return response()->json(['success' => true, 'message' => 'Pengumuman berhasil disiarkan!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal: ' . $e->getMessage()], 500);
        }
    }

    public function paginateStudentAttendance(Request $request, $role, $schoolName, $schoolId, $subjectTeacherId, $meetingNumber, $semester) 
    {

        $teacherMapel = TeacherMapel::with('SchoolClass')->findOrFail($subjectTeacherId);

        $students = UserAccount::with(['StudentProfile', 'StudentSchoolClass', 'SubjectAttendance' => function ($query) use ($subjectTeacherId, $meetingNumber, $semester) 
            {
                $query->where('subject_teacher_id', $subjectTeacherId)->where('meeting_number', $meetingNumber)->where('semester', $semester);
            }
        ])
        ->whereHas('StudentSchoolClass', function ($query) use ($teacherMapel) {
            $query->where('school_class_id', $teacherMapel->school_class_id);
        })
        ->where('role', 'Siswa')->get()->sortBy(fn($s) => strtolower($s->StudentProfile->nama_lengkap ?? ''))->values();

        return response()->json([
            'data' => $students
        ]);
    }

    public function getAttendanceChart(Request $request, $role, $schoolName, $schoolId, $subjectTeacherId, $meetingNumber, $semester) 
    {
        $teacherMapel = TeacherMapel::findOrFail($subjectTeacherId);

        $totalSiswa = UserAccount::whereHas('StudentSchoolClass', function ($query) use ($teacherMapel) {
            $query->where('school_class_id', $teacherMapel->school_class_id);
        })
        ->where('role', 'Siswa')->count();

        $attendances = SubjectAttendance::where('subject_teacher_id', $subjectTeacherId)->where('meeting_number', $meetingNumber)->where('semester', $semester);

        return response()->json([
            'total_siswa' => $totalSiswa,

            'hadir' => (clone $attendances)->where('attendance_status', 'hadir')->count(),

            'izin' => (clone $attendances)->where('attendance_status', 'izin')->count(),

            'sakit' => (clone $attendances)->where('attendance_status', 'sakit')->count(),

            'alpa' => (clone $attendances)->where('attendance_status', 'alpa')->count(),
        ]);
    }

    public function storeStudentAttendance(Request $request, $role, $schoolName, $schoolId, $subjectTeacherId, $meetingNumber, $semester) 
    {

        foreach ($request->attendances as $attendance) {

            SubjectAttendance::updateOrCreate(
                [
                    'student_id' => $attendance['student_id'],
                    'meeting_number' => $meetingNumber,
                    'semester' => $semester,
                    'subject_teacher_id' => $subjectTeacherId,
                ],
                [
                    'attendance_status' => $attendance['status'],
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Presensi berhasil disimpan'
        ]);
    }
}
