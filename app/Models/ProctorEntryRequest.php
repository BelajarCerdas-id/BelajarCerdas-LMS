<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProctorEntryRequest extends Model
{
    protected $fillable = [
        'school_assessment_id',
        'student_id',
        'decided_by',
        'status',
        'decided_at',
        'revoke_status',
        'revoke_requested_by',
        'revoke_expires_at',
        'revoke_decided_by',
        'revoke_decided_at',
    ];

    protected $casts = [
        'decided_at' => 'datetime',
        'revoke_expires_at' => 'datetime',
        'revoke_decided_at' => 'datetime',
    ];

    public function assessment()
    {
        return $this->belongsTo(SchoolAssessment::class, 'school_assessment_id');
    }

    public function student()
    {
        return $this->belongsTo(UserAccount::class, 'student_id');
    }
}
