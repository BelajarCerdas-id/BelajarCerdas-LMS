<?php

namespace App\Http\Controllers;

use App\Models\LessonScheduleItem;
use App\Models\LmsMeetingContent;
use App\Models\SubjectAttendance;
use App\Models\TeacherDailyAgenda;
use App\Models\TeacherMapel;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TeacherDailyAgendaController extends Controller
{
    public function index($role, $schoolName, $schoolId)
    {
        return view('features.lms.teacher.daily-agenda.teacher-daily-agenda', compact('role', 'schoolName', 'schoolId'));
    }

    public function paginateTeacherDailyAgenda(Request $request, $role, $schoolName, $schoolId)
    {
        $user = Auth::user();

        $dayMap = [
            'Monday'    => 'Senin',
            'Tuesday'   => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday'  => 'Kamis',
            'Friday'    => 'Jumat',
            'Saturday'  => 'Sabtu',
            'Sunday'    => 'Minggu',
        ];

        $today = $dayMap[Carbon::now()->format('l')];

        $agendaDate = now()->toDateString();

        $lessonSchedules = LessonScheduleItem::with(['schedule', 'schedule.SchoolClass'])->whereHas('schedule', function ($query) use ($schoolId) {
            $query->where('school_partner_id', $schoolId);
        })->where('teacher_id', $user->id)->where('day_of_week', $today)->orderBy('start_time')->get();

        $grouped = $lessonSchedules->groupBy(function ($item) {
            return $item->schedule->class_id . '-' . $item->mapel_id;
        });

        $data = $grouped->map(function ($items) use ($user, $schoolId, $agendaDate) {

            $first = $items->first();

            $isSubmitted = TeacherDailyAgenda::where('teacher_id', $user->id)->where('school_partner_id', $schoolId)->where('school_class_id', $first->schedule->class_id)
            ->where('mapel_id', $first->mapel_id)->whereDate('agenda_date', $agendaDate)->exists();

            return [

                'class_id' => $first->schedule->class_id,

                'mapel_id' => $first->mapel_id,

                'day_of_week' => $first->day_of_week,

                'rombel_class' => $first->schedule->SchoolClass->class_name . ' - ' . $first->schedule->SchoolClass->tahun_ajaran,

                'class_name' => $first->schedule->class_name,

                'subject_name' => $first->subject_name,

                'teacher_name' => $first->teacher_name,

                'start_time' => substr($items->min('start_time'), 0, 5),

                'end_time' => substr($items->max('end_time'), 0, 5),

                'total_session' => $items->count(),

                'status' => $isSubmitted ? 'submitted' : 'draft',
            ];

        })->values();

        return response()->json([
            'data' => $data,
            'totalDailyAgenda' => $data->count(),
            'teacherDailyAgendaForm' => '/lms/:role/:schoolName/:schoolId/daily-agenda/:dayOfWeek/class/:classId/subject-teacher/:subjectId/form',
        ]);
    }

    public function teacherDailyAgendaForm($role, $schoolName, $schoolId, $dayOfWeek, $classId, $subjectId)
    {
        $user = Auth::user();

        $teacherDailyAgenda = TeacherDailyAgenda::where(['teacher_id' => $user->id, 'school_partner_id' => $schoolId, 'school_class_id' => $classId, 'mapel_id' => $subjectId, 
            'agenda_date' => today(),
        ])->first();

        return view('features.lms.teacher.daily-agenda.teacher-daily-agenda-form', compact('role', 'schoolName', 'schoolId', 'dayOfWeek', 'classId', 'subjectId',
        'teacherDailyAgenda'));
    }

    public function teacherDailyAgendaFormDetail($role, $schoolName, $schoolId, $dayOfWeek, $classId, $subjectId)
    {
        $user = Auth::user();

        $items = LessonScheduleItem::with(['schedule', 'schedule.SchoolClass'])->whereHas('schedule', function ($query) use ($schoolId, $classId) {
            $query->where('school_partner_id', $schoolId)->where('class_id', $classId);
        })->where('teacher_id', $user->id)->where('day_of_week', $dayOfWeek)->where('mapel_id', $subjectId)->orderBy('start_time')->get();

        if ($items->isEmpty()) {
            return response()->json([
                'data' => null
            ]);
        }

        $first = $items->first();

        $agenda = TeacherDailyAgenda::where('teacher_id', $user->id)->where('school_partner_id', $schoolId)->where('school_class_id', $classId)
        ->where('mapel_id', $subjectId)->first();

        return response()->json([
            'data' => [

                'date' => now()->toDateString(),

                'rombel_class' => $first->schedule->SchoolClass->class_name . ' - ' . $first->schedule->SchoolClass->tahun_ajaran,

                'subject_name' => $first->subject_name,

                'start_time' => substr($items->min('start_time'), 0, 5),

                'end_time' => substr($items->max('end_time'), 0, 5),

                'total_session' => $items->count(),

                'sessions' => $items->values()->map(function ($item, $index) {
                    return [
                        'jp' => $index + 1,
                        'start_time' => substr($item->start_time, 0, 5),
                        'end_time' => substr($item->end_time, 0, 5),
                    ];
                }),

                'status' => $agenda ? 'submitted' : 'draft',

                'teacher_daily_agenda_id' => $agenda?->id,

                'learning_activity' => $agenda?->learning_activity,
            ]
        ]);
    }

    public function teacherDailyAgendaSubmitForm(Request $request, $role, $schoolName, $schoolId, $dayOfWeek, $classId, $subjectId)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'learning_activity' => 'required',
        ], [
            'learning_activity.required' => 'Harap isi uraian kegiatan.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {

            DB::beginTransaction();

            if ($request->filled('teacher_daily_agenda_id')) {

                $teacherDailyAgenda = TeacherDailyAgenda::findOrFail($request->teacher_daily_agenda_id);

                $teacherDailyAgenda->update([
                    'learning_activity' => $request->learning_activity,
                    'status' => 'submitted',
                ]);

            } else {

                $teacherDailyAgenda = TeacherDailyAgenda::create([
                    'teacher_id' => $user->id,
                    'school_partner_id' => $schoolId,
                    'school_class_id' => $classId,
                    'mapel_id' => $subjectId,
                    'agenda_date' => today(),
                    'learning_activity' => $request->learning_activity,
                    'status' => 'submitted',
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil menyimpan agenda harian.',
            ]);

        } catch (QueryException $e) {

            DB::rollBack();

            // Duplicate Key / Unique Constraint
            if ($e->getCode() == 23000) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Agenda harian untuk kelas dan mata pelajaran ini sudah pernah dibuat hari ini.',
                ], 409);
            }

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function teacherDailyAgendaHistory($role, $schoolName, $schoolId)
    {
        return view('features.lms.teacher.daily-agenda.teacher-daily-agenda-history', compact('role', 'schoolName', 'schoolId'));
    }

    public function paginateTeacherDailyAgendaHistory(Request $request, $role, $schoolName, $schoolId)
    {
        $user = Auth::user();

        $searchDate = $request->search_date ?? now()->toDateString();

        // Query Meeting
        $meetings = LmsMeetingContent::with(['UserAccount.SchoolStaffProfile', 'Mapel', 'SchoolClass'])->where('teacher_id', $user->id)
        ->where('school_partner_id', $schoolId);

        // Filter tanggal
        if ($request->filled('search_date')) {
            $meetings->whereDate('meeting_date', $searchDate);
        }

        // Filter guru
        if ($request->filled('search_teacher')) {
            $meetings->where('teacher_id', $request->search_teacher);
        }

        // Group meeting menjadi 1 data per hari
        $meetings = $meetings->orderByDesc('meeting_date')->orderBy('teacher_id')->orderBy('school_class_id')->orderBy('mapel_id')->get()->groupBy(function ($item) {
            return implode('-', [$item->meeting_date, $item->teacher_id, $item->school_class_id, $item->mapel_id]);
        });

        $result = [];

        foreach ($meetings as $meetingGroup) {

            $meeting = $meetingGroup->first();

            $dayMap = [
                'Monday'    => 'Senin',
                'Tuesday'   => 'Selasa',
                'Wednesday' => 'Rabu',
                'Thursday'  => 'Kamis',
                'Friday'    => 'Jumat',
                'Saturday'  => 'Sabtu',
                'Sunday'    => 'Minggu',
            ];

            $dayOfWeek = $dayMap[
                Carbon::parse($meeting->meeting_date)->format('l')
            ];

            $scheduleItems = LessonScheduleItem::where('teacher_id', $meeting->teacher_id)->where('mapel_id', $meeting->mapel_id)->where('day_of_week', $dayOfWeek)
            ->whereHas('schedule', function ($query) use ($meeting) {
                $query->where('class_id', $meeting->school_class_id);
            })->get();

            $startTime = $scheduleItems->isNotEmpty() ? substr($scheduleItems->min('start_time'), 0, 5) : '-';

            $endTime = $scheduleItems->isNotEmpty() ? substr($scheduleItems->max('end_time'), 0, 5) : '-';

            // Cari agenda
            $agenda = TeacherDailyAgenda::where('teacher_id', $meeting->teacher_id)->where('school_partner_id', $schoolId)->where('school_class_id', $meeting->school_class_id)
            ->where('mapel_id', $meeting->mapel_id)->whereDate('agenda_date', $meeting->meeting_date)->first();

            $teacherMapel = TeacherMapel::where('user_id', $meeting->teacher_id)->where('mapel_id', $meeting->mapel_id)
            ->where('school_class_id', $meeting->school_class_id)->first();

            $attendance = false;

            if ($teacherMapel) {
                $attendance = SubjectAttendance::where('subject_teacher_id', $teacherMapel->id)->where('meeting_number', $meeting->meeting_number)
                ->where('semester', $meeting->semester)->exists();
            }

            $result[] = [
                'teacher_agenda_id' => $agenda?->id,
                'teacher_id'        => $meeting->teacher_id,
                'teacher_name'      => $meeting->UserAccount->SchoolStaffProfile->nama_lengkap,
                'subject'           => $meeting->Mapel->mata_pelajaran,
                'school_class_name' => $meeting->SchoolClass->class_name,
                'school_year'       => $meeting->SchoolClass->tahun_ajaran,
                'meeting_date'      => $meeting->meeting_date,
                'agenda'            => $agenda,
                'learning_activity' => $agenda?->learning_activity,
                'feedback'          => $agenda?->feedback,
                'meeting_number'    => $meeting->meeting_number,
                'semester'          => $meeting->semester,
                'attendance'        => $attendance,
                'time'              => $startTime . ' - ' . $endTime,
            ];
        }

        $result = collect($result)
            ->sortBy(function ($item) {
                return strtolower($item['teacher_name']);
            })
            ->sortByDesc(function ($item) {
                return $item['meeting_date'];
            })
            ->values();

        // Filter Status Agenda
        if ($request->filled('search_status')) {

            if ($request->search_status === 'submitted') {
                $result = $result->whereNotNull('agenda');
            }

            if ($request->search_status === 'pending') {
                $result = $result->whereNull('agenda');
            }
        }

        $page = LengthAwarePaginator::resolveCurrentPage();

        $perPage = 20;

        $paginatedResult = new LengthAwarePaginator(
            $result->forPage($page, $perPage)->values(),
            $result->count(),
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );

        // Summary KPI
        $totalTeachingTeachers = $result->count();

        $totalSubmittedAgenda = $result->whereNotNull('agenda')->count();

        $totalPendingAgenda = $result->whereNull('agenda')->count();

        $complianceRate = $totalTeachingTeachers > 0 ? round(($totalSubmittedAgenda / $totalTeachingTeachers) * 100) : 0;

        return response()->json([
            'data' => $paginatedResult->items(),
            'links' => (string) $paginatedResult->links(),

            'summary' => [
                'totalTeachingTeachers' => $totalTeachingTeachers,
                'totalSubmittedAgenda'  => $totalSubmittedAgenda,
                'totalPendingAgenda'    => $totalPendingAgenda,
                'complianceRate'        => $complianceRate,
            ],
        ]);
    }
}
