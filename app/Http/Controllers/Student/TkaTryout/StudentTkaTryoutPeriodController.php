<?php

namespace App\Http\Controllers\Student\TkaTryout;

use App\Helpers\TimezoneHelper;
use App\Http\Controllers\Controller;
use App\Models\SchoolPartner;
use App\Models\StudentSchoolClass;
use App\Models\TkaTryoutPeriod;
use Illuminate\Support\Facades\Auth;

class StudentTkaTryoutPeriodController extends Controller
{
    public function index($role, $schoolName, $schoolId)
    {
        return view('features.lms.student.tka-tryout.tka-tryout-period-list', compact('role', 'schoolName', 'schoolId'));
    }

    public function paginatePeriodList($role, $schoolName, $schoolId)
    {
        $user = Auth::user();

        $schoolClass = StudentSchoolClass::with(['SchoolClass'])->where('student_id', $user->id)->where(function ($query) {
            $query->whereNull('academic_action')->orWhere('academic_action', '');
        })->first();

        if (!$schoolClass || !$schoolClass->SchoolClass) {
            return response()->json([
                'data' => []
            ]);
        }

        $schoolPartner = SchoolPartner::findOrFail($schoolId);
        $timezone = TimezoneHelper::getSchoolTimezone($schoolPartner);

        $periodList = TkaTryoutPeriod::with(['TkaTryoutSession', 'TkaTryoutSubject', 'TkaTryoutPeriodSchOverride' => function ($query) use ($schoolId) {
            $query->where('school_partner_id', $schoolId)->with(['TkaTryoutSession', 'TkaTryoutSubject']);
            },
        ])->where('tahun_ajaran', $schoolClass->SchoolClass->tahun_ajaran)->get();

        $periodList = $periodList->map(function ($period) use ($timezone) {
            $override = $period->TkaTryoutPeriodSchOverride->first();

            $source = $override ?: $period;

            $startDate = $source->start_date;
            $endDate = $source->end_date;
            $isReview = $source->is_review;

            $sessions = $source->TkaTryoutSession;
            $subjects = $source->TkaTryoutSubject;

            $today = TimezoneHelper::now($timezone)->startOfDay();
            $startDateCarbon = $startDate ? TimezoneHelper::parse($startDate, $timezone)->startOfDay() : null;
            $endDateCarbon = $endDate ? TimezoneHelper::parse($endDate, $timezone)->endOfDay() : null;

            if ($startDateCarbon && $today->lt($startDateCarbon)) {
                $periodStatus = 'upcoming';
            } elseif ($endDateCarbon && $today->gt($endDateCarbon)) {
                $periodStatus = 'finished';
            } else {
                $periodStatus = 'ongoing';
            }

            return [
                'id' => $period->id,
                'user_id' => $period->user_id,
                'tahun_ajaran' => $period->tahun_ajaran,
                'period_number' => $period->period_number,
                'is_review' => $isReview,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'period_status' => $periodStatus,
                'tka_tryout_session' => $sessions,
                'tka_tryout_subject' => $subjects,
                'is_override' => (bool) $override,
                'timezone' => $timezone,
                'timezone_label' => TimezoneHelper::getTimezoneLabel($timezone),
            ];
        })->values();

        return response()->json([
            'data' => $periodList,
        ]);
    }

    public function checkPeriod($role, $schoolName, $schoolId, $periodId)
    {
        $user = Auth::user();

        $schoolClass = StudentSchoolClass::with(['SchoolClass'])->where('student_id', $user->id)->where(function ($query) {
            $query->whereNull('academic_action')->orWhere('academic_action', '');
        })->first();

        if (!$schoolClass || !$schoolClass->SchoolClass) {
            return response()->json([
                'success' => false,
                'message' => 'Data kelas siswa tidak ditemukan.'
            ], 404);
        }

        $schoolPartner = SchoolPartner::findOrFail($schoolId);
        $timezone = TimezoneHelper::getSchoolTimezone($schoolPartner);

        $period = TkaTryoutPeriod::with(['TkaTryoutPeriodSchOverride' => function ($query) use ($schoolId) {
            $query->where('school_partner_id', $schoolId);
        }])->where('id', $periodId)->where('tahun_ajaran', $schoolClass->SchoolClass->tahun_ajaran)->first();

        if (!$period) {
            return response()->json([
                'success' => false,
                'message' => 'Gelombang Tryout TKA tidak ditemukan.'
            ], 404);
        }

        $override = $period->TkaTryoutPeriodSchOverride->first();

        $startDate = $override?->start_date ?? $period->start_date;
        $endDate = $override?->end_date ?? $period->end_date;
        $isReview = $override?->is_review ?? $period->is_review;

        $startDate = $startDate ? TimezoneHelper::parse($startDate, $timezone)->startOfDay() : null;
        $endDate = $endDate ? TimezoneHelper::parse($endDate, $timezone)->endOfDay() : null;

        $now = TimezoneHelper::now($timezone);

        if ($startDate && $now->lt($startDate)) {
            $periodStatus = 'upcoming';
        } elseif ($endDate && $now->gt($endDate)) {
            $periodStatus = 'finished';
        } else {
            $periodStatus = 'ongoing';
        }

        return response()->json([
            'success' => true,
            'data' => [
                'period_id' => $period->id,
                'period_status' => $periodStatus,
                'is_review' => (bool) $isReview,
                'start_date' => $startDate?->toDateString(),
                'end_date' => $endDate?->toDateString(),
                'timezone' => $timezone,
                'timezone_label' => TimezoneHelper::getTimezoneLabel($timezone),
            ]
        ]);
    }
}