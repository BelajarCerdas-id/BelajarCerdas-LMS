<?php

namespace App\Http\Controllers\Parent\TkaTryout;

use App\Helpers\TimezoneHelper;
use App\Http\Controllers\Controller;
use App\Models\ParentProfile;
use App\Models\SchoolPartner;
use App\Models\StudentProfile;
use App\Models\StudentSchoolClass;
use App\Models\TkaTryoutPeriod;
use App\Models\UserAccount;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ParentTkaTryoutController extends Controller
{
    private function getAnakInfo($studentId = null)
    {
        $user = Auth::user();

        $profilOrangTua = ParentProfile::where('user_id', $user->id)->first();

        if (!$profilOrangTua) {
            return null;
        }

        $studentQuery = StudentProfile::where('parent_id', $user->id);

        if ($studentId) {
            $studentQuery->where('user_id', $studentId);
        }

        $studentProfile = $studentQuery->orderBy('nama_lengkap')->first();

        if (!$studentProfile) {
            return null;
        }

        $classRecord = DB::table('student_school_classes')->where('student_id', $studentProfile->user_id)->where('student_class_status', 'active')->first();

        return (object)[
            'user_id'   => $studentProfile->user_id,
            'class_id'  => $classRecord->school_class_id ?? null,
            'school_id' => $profilOrangTua->school_partner_id,
        ];
    }

    private function getTkaTryoutPeriodStatus($startDate, $endDate, Carbon $now, $timezone) 
    {
        $start = Carbon::createFromFormat('Y-m-d H:i:s', $startDate->format('Y-m-d') . ' 00:00:00', $timezone);
        $end = Carbon::createFromFormat('Y-m-d H:i:s', $endDate->format('Y-m-d') . ' 23:59:59', $timezone);

        if ($now->lt($start)) {
            return 'upcoming';
        }

        if ($now->between($start, $end)) {
            return 'active';
        }

        return 'finished';
    }

    private function getTkaTryoutSessionStatus(Carbon $startDateTime, Carbon $endDateTime, Carbon $now): string
    {
        if ($now->lt($startDateTime)) {
            return 'upcoming';
        }

        if ($now->lte($endDateTime)) {
            return 'ongoing';
        }

        return 'passed';
    }

    private function formatTkaTryoutDateRange($startDate, $endDate, $timezone) 
    {
        $start = Carbon::createFromFormat('Y-m-d H:i:s', $startDate->format('Y-m-d') . ' 00:00:00', $timezone);
        $end = Carbon::createFromFormat('Y-m-d H:i:s', $endDate->format('Y-m-d') . ' 00:00:00', $timezone);

        if ($start->year === $end->year && $start->month === $end->month) {
            return $start->format('d') . ' - ' . $end->locale('id')->translatedFormat('d M Y');
        }

        if ($start->year === $end->year) {
            return $start->locale('id')->translatedFormat('d M') . ' - ' . $end->locale('id')->translatedFormat('d M Y');
        }

        return $start->locale('id')->translatedFormat('d M Y') . ' - ' . $end->locale('id')->translatedFormat('d M Y');
    }
    
    public function index($role, $schoolName, $schoolId, $studentId = null)
    {
        return view('features.lms.parents.tka-tryout.parent-tka-tryout-monitoring', compact('role', 'schoolName', 'schoolId', 'studentId'));
    }

    public function loadStudentInformation($role, $schoolName, $schoolId, $studentId = null)
    {
        $anak = $this->getAnakInfo($studentId);

        abort_if(!$anak || !$anak->school_id, 404, 'Data Sekolah tidak ditemukan.');

        $student = UserAccount::with('StudentProfile')->where('id', $anak->user_id)->first();

        $studentClass = StudentSchoolClass::where('student_id', $anak->user_id)->where('student_class_status', 'active')->where(function ($query) {
            $query->whereNull('academic_action')->orWhere('academic_action', '!=', '');
        })->first();

        return response()->json([
            'student' => [
                'id' => $student->id,
                'name' => $student->StudentProfile?->nama_lengkap,
                'class' => $studentClass->SchoolClass?->class_name,
                'school_year' => $studentClass->SchoolClass?->tahun_ajaran
            ],
        ]);
    }

    public function loadTkaTryoutSchedule($role, $schoolName, $schoolId, $studentId = null)
    {
        $anak = $this->getAnakInfo($studentId);

        abort_if(!$anak || !$anak->school_id, 404, 'Data Sekolah tidak ditemukan.');

        $studentId = $anak->user_id;
        $schoolPartner = SchoolPartner::find($anak->school_id);
        $timezone = TimezoneHelper::getSchoolTimezone($schoolPartner);
        $now = TimezoneHelper::now($timezone);

        $periods = TkaTryoutPeriod::query()->with(['TkaTryoutPeriodSchOverride' => function ($query) use ($anak) {
                $query->where('school_partner_id', $anak->school_id);
            },
        ])->orderByDesc('period_number')->get();

        $periods = $periods->map(function ($period) use ($studentId, $now, $timezone) {
            $override = $period->TkaTryoutPeriodSchOverride->first();

            $startDate = $override?->start_date ?? $period->start_date;
            $endDate = $override?->end_date ?? $period->end_date;

            if (!$startDate || !$endDate) {
                return null;
            }

            if ($override) {
                $sessions = $override->TkaTryoutSession()->whereHas('TkaTryoutSessionStudent', function ($query) use ($studentId) {
                    $query->where('student_id', $studentId);
                })->orderByDesc('session_date')->orderByDesc('start_time')->get();

                $subjects = $override->TkaTryoutSubject()->with('Mapel')->get();
            } else {
                $sessions = $period->TkaTryoutSession()->whereNull('tka_tryout_period_sch_override_id')->whereHas('TkaTryoutSessionStudent', function ($query) use ($studentId) {
                    $query->where('student_id', $studentId);
                })->orderByDesc('session_date')->orderByDesc('start_time')->get();

                $subjects = $period->TkaTryoutSubject()->with('Mapel')->get();
            }

            $sessions = $sessions->map(function ($session) use ($subjects, $now, $timezone) {
                $subject = $subjects->first(function ($item) use ($session) {
                    return $item->subject_date?->format('Y-m-d') === $session->session_date?->format('Y-m-d');
                });

                $startDateTime = Carbon::createFromFormat('Y-m-d H:i', $session->session_date->format('Y-m-d') . ' ' . $session->start_time->format('H:i'), $timezone);
                $endDateTime = Carbon::createFromFormat('Y-m-d H:i', $session->session_date->format('Y-m-d') . ' ' . $session->end_time->format('H:i'), $timezone);

                return [
                    'id' => $session->id,
                    'session_date' => $session->session_date->format('Y-m-d'),
                    'date' => $session->session_date->locale('id')->translatedFormat('l, d F Y'),
                    'subject' => $subject?->Mapel?->mata_pelajaran ?? 'Mata Pelajaran',
                    'start_time' => $session->start_time->format('H:i'),
                    'end_time' => $session->end_time->format('H:i'),
                    'total_question' => $subject?->total_question ?? 0,
                    'duration' => $subject?->duration ?? $startDateTime->diffInMinutes($endDateTime),
                    'status' => $this->getTkaTryoutSessionStatus($startDateTime, $endDateTime, $now),
                ];
            })->values();

            if ($sessions->isEmpty()) {
                return null;
            }

            return [
                'id' => $period->id,
                'period_number' => $period->period_number,
                'title' => 'Tryout TKA Periode ' . $period->period_number,
                'status' => $this->getTkaTryoutPeriodStatus($startDate, $endDate, $now, $timezone),
                'date_range' => $this->formatTkaTryoutDateRange($startDate, $endDate, $timezone),
                'sessions' => $sessions,
                'is_override' => (bool) $override,
            ];
        })->filter()->sortByDesc('period_number')->values();

        return response()->json([
            'periods' => $periods,
            'timezone' => $timezone,
            'timezone_label' => TimezoneHelper::getTimezoneLabel($timezone),
        ]);
    }

    public function loadTkaTryoutResult($role, $schoolName, $schoolId, $studentId = null)
    {
        $anak = $this->getAnakInfo($studentId);

        abort_if(!$anak || !$anak->school_id, 404, 'Data Sekolah tidak ditemukan.');

        $studentId = $anak->id ?? $studentId;

        $schoolPartner = SchoolPartner::find($schoolId);

        $timezone = TimezoneHelper::getSchoolTimezone($schoolPartner);

        $periods = TkaTryoutPeriod::query()
            ->with([
                'TkaTryoutSession' => function ($query) use ($studentId) {
                    $query->with([
                        'TkaTryoutSessionStudent' => function ($query) use ($studentId) {
                            $query->where('student_id', $studentId);
                        },
                        'StudentTkaAttempt' => function ($query) use ($studentId) {
                            $query->where('student_id', $studentId)
                                ->where('attempt_type', 'tryout')
                                ->with([
                                    'Mapel',
                                    'StudentTkaTryoutAnswer',
                                ])
                                ->orderByDesc('created_at');
                        },
                    ])
                    ->orderBy('session_date')
                    ->orderBy('session_number');
                },
                'TkaTryoutSubject' => function ($query) {
                    $query->with('Mapel')
                        ->orderBy('subject_date')
                        ->orderBy('id');
                },
                'TkaTryoutPeriodSchOverride' => function ($query) use ($schoolId, $studentId) {
                    $query->where('school_partner_id', $schoolId)
                        ->with([
                            'TkaTryoutSession' => function ($query) use ($studentId) {
                                $query->with([
                                    'TkaTryoutSessionStudent' => function ($query) use ($studentId) {
                                        $query->where('student_id', $studentId);
                                    },
                                    'StudentTkaAttempt' => function ($query) use ($studentId) {
                                        $query->where('student_id', $studentId)
                                            ->where('attempt_type', 'tryout')
                                            ->with([
                                                'Mapel',
                                                'StudentTkaTryoutAnswer',
                                            ])
                                            ->orderByDesc('created_at');
                                    },
                                ])
                                ->orderBy('session_date')
                                ->orderBy('session_number');
                            },
                            'TkaTryoutSubject' => function ($query) {
                                $query->with('Mapel')
                                    ->orderBy('subject_date')
                                    ->orderBy('id');
                            },
                        ]);
                },
            ])
            ->orderByDesc('period_number')
            ->get();

        if ($periods->isEmpty()) {
            return response()->json([
                'success' => true,
                'timezone' => $timezone,
                'timezone_label' => TimezoneHelper::getTimezoneLabel($timezone),
                'student' => [
                    'id' => $studentId,
                    'name' => $anak->name ?? $anak->nama ?? null,
                ],
                'periods' => [],
            ]);
        }

        $resultPeriods = [];

        foreach ($periods as $period) {
            $override = $period->TkaTryoutPeriodSchOverride
                ->firstWhere('school_partner_id', $schoolId);

            $sessions = $override
                ? $override->TkaTryoutSession
                : $period->TkaTryoutSession;

            $subjects = $override
                ? $override->TkaTryoutSubject
                : $period->TkaTryoutSubject;

            $periodStart = $override?->start_date ?? $period->start_date;
            $periodEnd = $override?->end_date ?? $period->end_date;

            if (!$periodStart || !$periodEnd) {
                continue;
            }

            $periodStartDate = TimezoneHelper::formatDate(
                $periodStart,
                $timezone,
                'Y-m-d'
            );

            $periodEndDate = TimezoneHelper::formatDate(
                $periodEnd,
                $timezone,
                'Y-m-d'
            );

            if (!$periodStartDate || !$periodEndDate) {
                continue;
            }

            $sessions = $sessions
                ->filter(function ($session) use ($studentId) {
                    return $session->TkaTryoutSessionStudent
                        ->where('student_id', $studentId)
                        ->isNotEmpty();
                })
                ->sortBy(function ($session) use ($timezone) {
                    $date = $session->session_date
                        ? TimezoneHelper::formatDate(
                            $session->session_date,
                            $timezone,
                            'Y-m-d'
                        )
                        : '9999-99-99';

                    return $date . '-' . str_pad(
                        (string) $session->session_number,
                        5,
                        '0',
                        STR_PAD_LEFT
                    );
                })
                ->values();

            if ($sessions->isEmpty()) {
                continue;
            }

            $sessionResults = [];

            foreach ($sessions as $index => $session) {
                $sessionDate = $session->session_date;

                if (!$sessionDate) {
                    continue;
                }

                $sessionDateValue = TimezoneHelper::formatDate(
                    $sessionDate,
                    $timezone,
                    'Y-m-d'
                );

                $subject = $subjects
                    ->filter(function ($item) use ($sessionDateValue, $timezone) {
                        if (!$item->subject_date) {
                            return false;
                        }

                        $subjectDateValue = TimezoneHelper::formatDate(
                            $item->subject_date,
                            $timezone,
                            'Y-m-d'
                        );

                        return $subjectDateValue === $sessionDateValue;
                    })
                    ->sortBy('id')
                    ->first();

                $attempt = $session->StudentTkaAttempt
                    ->where('student_id', $studentId)
                    ->where('attempt_type', 'tryout')
                    ->sortByDesc('created_at')
                    ->first();

                $mapel = $subject?->Mapel;

                if (!$mapel && $attempt?->Mapel) {
                    $mapel = $attempt->Mapel;
                }

                if (!$mapel) {
                    continue;
                }

                $score = null;
                $correctAnswer = 0;

                $totalQuestion = (int) (
                    $subject?->total_question ??
                    $attempt?->total_question ??
                    0
                );

                if ($attempt) {
                    $answers = $attempt->StudentTkaTryoutAnswer;

                    $correctAnswer = $answers
                        ->filter(function ($answer) {
                            return strtolower(trim((string) $answer->status_answer)) === 'correct';
                        })
                        ->count();

                    if (!$totalQuestion) {
                        $totalQuestion = $answers->count();
                    }

                    $hasQuestionScore = $answers->contains(function ($answer) {
                        return $answer->question_score !== null;
                    });

                    if ($hasQuestionScore) {
                        $score = round(
                            $answers->sum(function ($answer) {
                                return (float) ($answer->question_score ?? 0);
                            }),
                            2
                        );
                    } elseif ($totalQuestion > 0) {
                        $score = round(
                            ($correctAnswer / $totalQuestion) * 100,
                            2
                        );
                    }
                }

                $sessionResults[] = [
                    'id' => $session->id,
                    'day_number' => $index + 1,
                    'session_number' => $session->session_number,
                    'date' => TimezoneHelper::formatDate(
                        $sessionDate,
                        $timezone,
                        'd F Y'
                    ),
                    'date_iso' => $sessionDateValue,
                    'subject_id' => $subject?->subject_id ?? $attempt?->mapel_id,
                    'subject' => $mapel->mata_pelajaran ?? $mapel->name ?? '-',
                    'score' => $score,
                    'total_question' => $totalQuestion,
                    'correct_answer' => $correctAnswer,
                    'status' => $score !== null
                        ? 'completed'
                        : 'not_attempted',
                ];
            }

            if (empty($sessionResults)) {
                continue;
            }

            $scoredSessions = collect($sessionResults)
                ->filter(function ($session) {
                    return $session['score'] !== null;
                })
                ->values();

            $scores = $scoredSessions
                ->pluck('score')
                ->map(function ($score) {
                    return (float) $score;
                })
                ->values();

            $averageScore = $scores->isNotEmpty()
                ? round($scores->avg(), 2)
                : null;

            $highestScore = $scores->isNotEmpty()
                ? round($scores->max(), 2)
                : null;

            $bestSubject = null;

            if ($scoredSessions->isNotEmpty()) {
                $bestSession = $scoredSessions
                    ->sortByDesc(function ($session) {
                        return (float) $session['score'];
                    })
                    ->first();

                $bestSubject = $bestSession['subject'];
            }

            $resultPeriods[] = [
                'id' => $period->id,
                'period_number' => $period->period_number,
                'title' => 'Tryout TKA Periode ' . $period->period_number,
                'status' => 'completed',
                'is_review' => (bool) (
                    $override?->is_review ??
                    $period->is_review
                ),
                'date_range' => TimezoneHelper::formatDate(
                    $periodStart,
                    $timezone,
                    'd F Y'
                ) . ' - ' . TimezoneHelper::formatDate(
                    $periodEnd,
                    $timezone,
                    'd F Y'
                ),
                'start_date' => $periodStartDate,
                'end_date' => $periodEndDate,
                'total_subjects' => count($sessionResults),
                'completed_subjects' => $scoredSessions->count(),
                'average_score' => $averageScore,
                'highest_score' => $highestScore,
                'best_subject' => $bestSubject,
                'sessions' => $sessionResults,
            ];
        }

        return response()->json([
            'success' => true,
            'timezone' => $timezone,
            'timezone_label' => TimezoneHelper::getTimezoneLabel($timezone),
            'student' => [
                'id' => $studentId,
                'name' => $anak->name ?? $anak->nama ?? null,
            ],
            'periods' => $resultPeriods,
        ]);
    }

public function loadTkaTryoutInsight($role, $schoolName, $schoolId, $studentId = null)
{
    $anak = $this->getAnakInfo($studentId);

    abort_if(
        !$anak || !$anak->school_id,
        404,
        'Data Sekolah tidak ditemukan.'
    );

    abort_if(
        (int) $anak->school_id !== (int) $schoolId,
        403,
        'Akses data siswa tidak diizinkan.'
    );

    $studentId = $anak->user_id ?? $anak->id;

    $schoolPartner = SchoolPartner::findOrFail($schoolId);
    $timezone = TimezoneHelper::getSchoolTimezone($schoolPartner);

    $periods = TkaTryoutPeriod::query()
        ->with([
            'TkaTryoutSession' => function ($query) use ($studentId) {
                $query->with([
                    'TkaTryoutSessionStudent' => function ($query) use ($studentId) {
                        $query->where('student_id', $studentId);
                    },
                    'StudentTkaAttempt' => function ($query) use ($studentId) {
                        $query->where('student_id', $studentId)
                            ->where('attempt_type', 'tryout')
                            ->with([
                                'Mapel',
                                'StudentTkaTryoutAnswer'
                            ]);
                    }
                ]);
            },
            'TkaTryoutSubject.Mapel',
            'TkaTryoutPeriodSchOverride' => function ($query) use ($schoolId, $studentId) {
                $query->where('school_partner_id', $schoolId)
                    ->with([
                        'TkaTryoutSession' => function ($query) use ($studentId) {
                            $query->with([
                                'TkaTryoutSessionStudent' => function ($query) use ($studentId) {
                                    $query->where('student_id', $studentId);
                                },
                                'StudentTkaAttempt' => function ($query) use ($studentId) {
                                    $query->where('student_id', $studentId)
                                        ->where('attempt_type', 'tryout')
                                        ->with([
                                            'Mapel',
                                            'StudentTkaTryoutAnswer'
                                        ]);
                                }
                            ]);
                        },
                        'TkaTryoutSubject.Mapel'
                    ]);
            }
        ])
        ->orderBy('period_number')
        ->get();

    $calculateAttemptResult = function ($attempt) {
        if (!$attempt) {
            return null;
        }

        $answers = $attempt->StudentTkaTryoutAnswer ?? collect();

        $totalQuestion = (int) (
            $attempt->total_question ?: $answers->count()
        );

        if ($totalQuestion <= 0) {
            return [
                'score' => 0,
                'total_question' => 0,
                'correct_answer' => 0,
            ];
        }

        $hasQuestionScore = $answers->contains(function ($answer) {
            return $answer->question_score !== null;
        });

        if ($hasQuestionScore) {
            $score = (float) $answers->sum(function ($answer) {
                return is_numeric($answer->question_score)
                    ? (float) $answer->question_score
                    : 0;
            });

            $correctAnswer = (int) $answers->filter(function ($answer) {
                return is_numeric($answer->question_score)
                    && (float) $answer->question_score > 0;
            })->count();
        } else {
            $correctAnswer = (int) $answers->filter(function ($answer) {
                return in_array(
                    strtolower((string) $answer->status_answer),
                    ['correct', 'benar', 'true', '1'],
                    true
                );
            })->count();

            $score = round(
                ($correctAnswer / $totalQuestion) * 100,
                2
            );
        }

        return [
            'score' => $score,
            'total_question' => $totalQuestion,
            'correct_answer' => $correctAnswer,
        ];
    };

    $buildSessionData = function ($session, $subjects) use (
        $studentId,
        $calculateAttemptResult,
        $timezone
    ) {
        $isAssigned = $session->TkaTryoutSessionStudent
            ->where('student_id', $studentId)
            ->isNotEmpty();

        if (!$isAssigned) {
            return null;
        }

        $sessionDate = $session->session_date;

        if (!$sessionDate) {
            return null;
        }

        $sessionDateIso = $sessionDate->format('Y-m-d');

        $subject = $subjects
            ->filter(function ($item) use ($sessionDateIso) {
                if (!$item->subject_date) {
                    return false;
                }

                return $item->subject_date->format('Y-m-d') === $sessionDateIso;
            })
            ->sortByDesc('id')
            ->first();

        $attempt = $session->StudentTkaAttempt
            ->where('student_id', $studentId)
            ->where('tka_tryout_session_id', $session->id)
            ->where('attempt_type', 'tryout')
            ->sortByDesc('created_at')
            ->first();

        $result = $attempt
            ? $calculateAttemptResult($attempt)
            : null;

        $sessionEnd = null;

        if ($sessionDateIso) {
            $endTime = $session->end_time
                ? $session->end_time->format('H:i:s')
                : '23:59:59';

            $sessionEnd = \Carbon\Carbon::parse(
                "{$sessionDateIso} {$endTime}",
                $timezone
            );
        }

        $now = now($timezone);

        if ($attempt) {
            $status = 'completed';
            $statusLabel = 'Telah dikerjakan';
        } elseif ($sessionEnd && $sessionEnd->lt($now)) {
            $status = 'missed';
            $statusLabel = 'Tidak dikerjakan';
        } else {
            $status = 'not_started';
            $statusLabel = 'Belum dikerjakan';
        }

        $mapel = $subject?->Mapel ?: $attempt?->Mapel;

        return [
            'id' => $session->id,
            'session_number' => (int) $session->session_number,
            'date' => $sessionDate->locale('id')->translatedFormat('d F Y'),
            'date_iso' => $sessionDateIso,
            'start_time' => $session->start_time
                ? $session->start_time->format('H:i')
                : null,
            'end_time' => $session->end_time
                ? $session->end_time->format('H:i')
                : null,
            'subject_id' => $subject?->subject_id ?? $attempt?->mapel_id,
            'subject' => $mapel?->mata_pelajaran
                ?? $mapel?->name
                ?? '-',
            'score' => $result['score'] ?? null,
            'total_question' => $result['total_question'] ?? null,
            'correct_answer' => $result['correct_answer'] ?? null,
            'status' => $status,
            'status_label' => $statusLabel,
            'attempt_status' => $attempt?->status,
            'attempt_id' => $attempt?->id,
            'period_start_date' => null,
            'period_end_date' => null,
        ];
    };

    $sessionRows = collect();

    foreach ($periods as $period) {
        $override = $period->TkaTryoutPeriodSchOverride
            ->where('school_partner_id', $schoolId)
            ->sortByDesc('id')
            ->first();

        if ($override) {
            $sessions = $override->TkaTryoutSession;
            $subjects = $override->TkaTryoutSubject;
            $periodStartDate = $override->start_date
                ? $override->start_date->format('Y-m-d')
                : ($period->start_date
                    ? $period->start_date->format('Y-m-d')
                    : null);
            $periodEndDate = $override->end_date
                ? $override->end_date->format('Y-m-d')
                : ($period->end_date
                    ? $period->end_date->format('Y-m-d')
                    : null);
            $isReview = (bool) $override->is_review;
        } else {
            $sessions = $period->TkaTryoutSession;
            $subjects = $period->TkaTryoutSubject;
            $periodStartDate = $period->start_date
                ? $period->start_date->format('Y-m-d')
                : null;
            $periodEndDate = $period->end_date
                ? $period->end_date->format('Y-m-d')
                : null;
            $isReview = (bool) $period->is_review;
        }

        foreach ($sessions as $session) {
            $sessionData = $buildSessionData(
                $session,
                $subjects
            );

            if (!$sessionData) {
                continue;
            }

            $sessionData['period_id'] = $period->id;
            $sessionData['period_number'] = (int) $period->period_number;
            $sessionData['period_title'] =
                'Tryout TKA Periode ' . $period->period_number;
            $sessionData['period_start_date'] = $periodStartDate;
            $sessionData['period_end_date'] = $periodEndDate;
            $sessionData['is_review'] = $isReview;

            $sessionRows->push($sessionData);
        }
    }

    $sessionRows = $sessionRows
        ->filter(function ($session) {
            return !empty($session['date_iso']);
        })
        ->sort(function ($a, $b) {
            $dateCompare = strcmp(
                $b['date_iso'],
                $a['date_iso']
            );

            if ($dateCompare !== 0) {
                return $dateCompare;
            }

            if ($a['session_number'] !== $b['session_number']) {
                return $b['session_number']
                    <=> $a['session_number'];
            }

            return $b['id'] <=> $a['id'];
        })
        ->values();

    if ($sessionRows->isEmpty()) {
        return response()->json([
            'success' => true,
            'student' => [
                'id' => $studentId,
                'name' => $anak->name
                    ?? $anak->nama
                    ?? null,
            ],
            'school' => [
                'id' => $schoolPartner->id,
                'name' => $schoolPartner->school_name
                    ?? $schoolPartner->name
                    ?? null,
            ],
            'timezone' => $timezone,
            'data' => [
                'latest_session' => null,
                'previous_session' => null,
                'performance' => [
                    'latest_average' => 0,
                    'previous_average' => null,
                    'difference' => null,
                    'status' => 'neutral',
                ],
                'subjects' => [],
                'sessions' => [],
                'completed_sessions' => [],
            ],
        ]);
    }

    $completedSessionRows = $sessionRows
        ->filter(function ($session) {
            return $session['status'] === 'completed'
                && $session['score'] !== null
                && is_numeric($session['score']);
        })
        ->values();

    $latestSession = $completedSessionRows->first();
    $previousSession = $completedSessionRows->skip(1)->first();

    $latestAverage = $latestSession
        ? (float) $latestSession['score']
        : 0;

    $previousAverage = $previousSession
        ? (float) $previousSession['score']
        : null;

    $performanceDifference = null;
    $performanceStatus = 'neutral';

    if ($previousSession) {
        $performanceDifference = round(
            $latestAverage - $previousAverage,
            2
        );

        $performanceStatus = match (true) {
            $performanceDifference > 0 => 'up',
            $performanceDifference < 0 => 'down',
            default => 'stable',
        };
    } elseif ($latestSession) {
        $performanceStatus = 'new';
    }

    $subjectHistory = [];

    foreach ($completedSessionRows as $session) {
        $subjectId = $session['subject_id'];

        if (!$subjectId) {
            continue;
        }

        if (!isset($subjectHistory[$subjectId])) {
            $subjectHistory[$subjectId] = [];
        }

        $subjectHistory[$subjectId][] = $session;
    }

    $subjects = collect();

    foreach ($subjectHistory as $subjectId => $history) {
        usort($history, function ($a, $b) {
            $dateCompare = strcmp(
                $b['date_iso'],
                $a['date_iso']
            );

            if ($dateCompare !== 0) {
                return $dateCompare;
            }

            if ($a['session_number'] !== $b['session_number']) {
                return $b['session_number']
                    <=> $a['session_number'];
            }

            return $b['id'] <=> $a['id'];
        });

        $latest = $history[0] ?? null;
        $previous = $history[1] ?? null;

        if (!$latest) {
            continue;
        }

        $difference = $previous
            ? round(
                (float) $latest['score']
                - (float) $previous['score'],
                2
            )
            : null;

        $status = match (true) {
            !$previous => 'new',
            $difference > 0 => 'up',
            $difference < 0 => 'down',
            default => 'stable',
        };

        $subjects->push([
            'subject_id' => $subjectId,
            'subject_name' => $latest['subject'],
            'latest_score' => $latest['score'],
            'latest_date' => $latest['date'],
            'latest_date_iso' => $latest['date_iso'],
            'latest_session_id' => $latest['id'],
            'latest_session_number' => $latest['session_number'],
            'previous_score' => $previous['score'] ?? null,
            'previous_date' => $previous['date'] ?? null,
            'previous_date_iso' => $previous['date_iso'] ?? null,
            'previous_session_id' => $previous['id'] ?? null,
            'previous_session_number' => $previous['session_number'] ?? null,
            'difference' => $difference,
            'status' => $status,
            'history' => array_values($history),
        ]);
    }

    $subjects = $subjects
        ->sort(function ($a, $b) {
            if ($a['latest_score'] !== $b['latest_score']) {
                return $b['latest_score']
                    <=> $a['latest_score'];
            }

            return strcmp(
                $b['latest_date_iso'],
                $a['latest_date_iso']
            );
        })
        ->values();

    return response()->json([
        'success' => true,
        'student' => [
            'id' => $studentId,
            'name' => $anak->name
                ?? $anak->nama
                ?? null,
        ],
        'school' => [
            'id' => $schoolPartner->id,
            'name' => $schoolPartner->school_name
                ?? $schoolPartner->name
                ?? null,
        ],
        'timezone' => $timezone,
        'data' => [
            'latest_session' => $latestSession,
            'previous_session' => $previousSession,
            'performance' => [
                'latest_average' => $latestAverage,
                'previous_average' => $previousAverage,
                'difference' => $performanceDifference,
                'status' => $performanceStatus,
            ],
            'subjects' => $subjects->values(),
            'sessions' => $sessionRows->values(),
            'completed_sessions' => $completedSessionRows->values(),
        ],
    ]);
}
}