<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProctorAuditLog extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'actor_id',
        'actor_role',
        'action',
        'school_partner_id',
        'school_assessment_id',
        'student_id',
        'attempt_id',
        'detail',
        'ip',
        'created_at',
    ];

    protected $casts = [
        'detail' => 'array',
        'created_at' => 'datetime',
    ];
}
