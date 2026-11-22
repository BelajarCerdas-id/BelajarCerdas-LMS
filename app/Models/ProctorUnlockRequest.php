<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProctorUnlockRequest extends Model
{
    protected $fillable = [
        'student_assessment_attempt_id',
        'requested_by',
        'decided_by',
        'status',
        'expires_at',
        'decided_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'decided_at' => 'datetime',
    ];

    public function attempt()
    {
        return $this->belongsTo(StudentAssessmentAttempt::class, 'student_assessment_attempt_id');
    }
}
