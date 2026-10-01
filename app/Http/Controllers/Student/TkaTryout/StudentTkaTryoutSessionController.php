<?php

namespace App\Http\Controllers\Student\TkaTryout;

use App\Helpers\TimezoneHelper;
use App\Http\Controllers\Controller;
use App\Models\SchoolPartner;
use App\Models\StudentSchoolClass;
use App\Models\TkaTryoutPeriod;
use App\Models\TkaTryoutPeriodSchOverride;
use App\Models\TkaTryoutSession;
use App\Models\TkaTryoutSubject;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class StudentTkaTryoutSessionController extends Controller
{
    public function index($role, $schoolName, $schoolId, $periodId)
    {
        $schoolPartner = SchoolPartner::findOrFail($schoolId);
        $timezone = TimezoneHelper::getSchoolTimezone($schoolPartner);

        $period = TkaTryoutPeriod::with(['TkaTryoutPeriodSchOverride' => function ($query) use ($schoolId) {
            $query->where('school_partner_id', $schoolId);
        }])->findOrFail($periodId);

        $override = $period->TkaTryoutPeriodSchOverride->first();

        $startDate = $override?->start_date ?? $period->start_date;
        $endDate = $override?->end_date ?? $period->end_date;

        $startDate = $startDate ? TimezoneHelper::parse($startDate, $timezone)->startOfDay() : null;
        $endDate = $endDate ? TimezoneHelper::parse($endDate, $timezone)->endOfDay() : null;

        $now = TimezoneHelper::now($timezone);

        if ($startDate && $now->lt($startDate)) {
            return redirect()->route('lms.student.tka-tryout-period.view', [
                'role' => $role,
                'schoolName' => $schoolName,
                'schoolId' => $schoolId
            ])->with('period_status', 'upcoming');
        }

        if ($endDate && $now->gt($endDate)) {
            return redirect()->route('lms.student.tka-tryout-period.view', [
                'role' => $role,
                'schoolName' => $schoolName,
                'schoolId' => $schoolId
            ])->with('period_status', 'finished');
        }

        return view('features.lms.student.tka-tryout.tka-tryout-session-list', compact('role', 'schoolName', 'schoolId', 'periodId'));
    }

    public function paginateSessionList($role, $schoolName, $schoolId, $periodId)
    {
        $user = Auth::user();
        $period = TkaTryoutPeriod::findOrFail($periodId);
        $schoolPartner = SchoolPartner::findOrFail($schoolId);
        $timezone = TimezoneHelper::getSchoolTimezone($schoolPartner);
        $timezoneLabel = TimezoneHelper::getTimezoneLabel($timezone);

        $override = TkaTryoutPeriodSchOverride::where('tka_tryout_period_id', $periodId)->where('school_partner_id', $schoolId)->first();

        $studentClassId = StudentSchoolClass::query()->where('student_id', $user->id)->whereHas('SchoolClass', function ($query) {
            $query->where('status_class', 'active');
        })->first()?->SchoolClass?->kelas_id;

        $sessionsQuery = TkaTryoutSession::query()->where('tka_tryout_period_id', $periodId)->with(['TkaTryoutSessionStudent' => function ($query) use ($user) {
            $query->where('student_id', $user->id);
        }]);

        $subjectsQuery = TkaTryoutSubject::query()->where('tka_tryout_period_id', $periodId)->whereHas('Mapel', function($query) use ($studentClassId) {
            $query->where('kelas_id', $studentClassId);
        });

        if ($override) {
            $sessionsQuery->where('tka_tryout_period_sch_override_id', $override->id);
            $subjectsQuery->where('tka_tryout_period_sch_override_id', $override->id);
        } else {
            $sessionsQuery->whereNull('tka_tryout_period_sch_override_id');
            $subjectsQuery->whereNull('tka_tryout_period_sch_override_id');
        }

        $sessions = $sessionsQuery->orderBy('session_date')->orderBy('session_number')->get();
        $subjects = $subjectsQuery->with(['Mapel'])->orderBy('subject_date')->get();

        $now = TimezoneHelper::now($timezone);

        $data = $sessions->groupBy(function ($session) {
            return Carbon::parse($session->session_date)->format('Y-m-d');
        })->map(function ($dateSessions) use ($subjects, $now, $timezone) {
            return [
                'date' => $dateSessions->first()?->session_date
                    ? Carbon::parse($dateSessions->first()->session_date)->format('Y-m-d')
                    : null,
                'sessions' => $dateSessions->map(function ($session) use ($subjects, $now, $timezone) {
                    $sessionDate = Carbon::parse($session->session_date)->format('Y-m-d');
                    $startTime = Carbon::parse($session->start_time)->format('H:i');
                    $endTime = Carbon::parse($session->end_time)->format('H:i');

                    $startDateTime = TimezoneHelper::parse(
                        $sessionDate . ' ' . $startTime . ':00',
                        $timezone
                    );

                    $endDateTime = TimezoneHelper::parse(
                        $sessionDate . ' ' . $endTime . ':00',
                        $timezone
                    );

                    if ($now->lt($startDateTime)) {
                        $sessionStatus = 'upcoming';
                    } elseif ($now->betweenIncluded($startDateTime, $endDateTime)) {
                        $sessionStatus = 'ongoing';
                    } else {
                        $sessionStatus = 'finished';
                    }

                    $studentSession = $session->TkaTryoutSessionStudent->first();
                    $isRegistered = $studentSession && (int) $studentSession->status === 1;

                    $sessionSubjects = $subjects->filter(function ($subject) use ($sessionDate) {
                        return Carbon::parse($subject->subject_date)->format('Y-m-d') === $sessionDate;
                    });

                    return [
                        'id' => $session->id,
                        'session_number' => $session->session_number,
                        'session_date' => $sessionDate,
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                        'start_datetime' => $startDateTime->format('Y-m-d\TH:i:sP'),
                        'end_datetime' => $endDateTime->format('Y-m-d\TH:i:sP'),
                        'session_status' => $sessionStatus,
                        'is_registered' => $isRegistered,
                        'can_start' => $isRegistered && $sessionStatus === 'ongoing',
                        'can_review' => $isRegistered && $sessionStatus === 'finished',
                        'subjects' => $sessionSubjects->map(function ($subject) {
                            return [
                                'id' => $subject->id,
                                'subject_id' => $subject->subject_id,
                                'subject_name' => $subject->Mapel?->mata_pelajaran,
                                'subject_date' => Carbon::parse($subject->subject_date)->format('Y-m-d'),
                                'total_question' => $subject->total_question,
                                'duration' => $subject->duration,
                                'is_active' => $subject->is_active,
                            ];
                        })->values(),
                    ];
                })->values(),
            ];
        })->values();

        $registeredSessions = $sessions->filter(function ($session) {
            return $session->TkaTryoutSessionStudent->isNotEmpty();
        })->values();

        return response()->json([
            'status' => true,
            'data' => $data,
            'period' => [
                'id' => $period->id,
                'period_number' => $period->period_number,
                'tahun_ajaran' => $period->tahun_ajaran,
                'start_date' => ($override?->start_date ?? $period->start_date) ? Carbon::parse($override?->start_date ?? $period->start_date)->format('Y-m-d') : null,
                'end_date' => ($override?->end_date ?? $period->end_date) ? Carbon::parse($override?->end_date ?? $period->end_date)->format('Y-m-d') : null,
                'is_override' => (bool) $override,
            ],
            'student' => [
                'id' => $user->id,
                'registered_session_count' => $registeredSessions->count(),
                'registered_sessions' => $registeredSessions->map(function ($session) {
                    return [
                        'id' => $session->id,
                        'session_number' => $session->session_number,
                        'session_date' => Carbon::parse($session->session_date)->format('Y-m-d'),
                        'start_time' => Carbon::parse($session->start_time)->format('H:i'),
                        'end_time' => Carbon::parse($session->end_time)->format('H:i'),
                    ];
                })->values(),
            ],
            'timezone' => $timezone,
            'timezone_label' => $timezoneLabel,
            'server_now' => $now->format('Y-m-d H:i:sP'),
            'total_session' => $sessions->count(),
            'total_subject' => $subjects->count(),
            'total_date' => $data->count(),
        ]);
    }
}