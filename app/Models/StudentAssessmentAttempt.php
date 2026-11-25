<?php

namespace App\Models;

use App\Models\StudentAssessmentAnticheatViolation;
use Illuminate\Database\Eloquent\Model;

/**
 * One student's run of one exam.
 *
 * expire_time invariant: it is ONLY ever written as the attempt's own
 * start_time + assessment duration (ProctorService::openAttempt), plus
 * pause shifts on proctor resume (paused seconds are given back). The
 * proctor stop/terminate paths finalize attempts but never rewrite
 * expire_time — a rewritten deadline destroyed the audit trail and left
 * stuck in_progress rows (incident 2026-11-25). The exam debugger
 * (AssessmentDebugController) is the single sanctioned manual override.
 */
class StudentAssessmentAttempt extends Model
{

    protected $casts = [
        'start_time' => 'datetime',
        'expire_time' => 'datetime',
        'last_heartbeat_at' => 'datetime',
        'finalized_at' => 'datetime',
        'proctor_paused_at' => 'datetime',
    ];
    protected $fillable = [
        'student_id',
        'school_assessment_id',
        'start_time',
        'expire_time',
        'tab_switch_count',
        'strike_limit',
        'last_heartbeat_at',
        'proctor_paused_at',
        'status',
        'finalized_at',
    ];

    public function AnticheatViolations()
    {
        return $this->hasMany(StudentAssessmentAnticheatViolation::class, 'student_assessment_attempt_id');
    }

    public function UserAccount()
    {
        return $this->belongsTo(UserAccount::class, 'student_id');
    }

    public function SchoolAssessment()
    {
        return $this->belongsTo(SchoolAssessment::class, 'school_assessment_id');
    }
}