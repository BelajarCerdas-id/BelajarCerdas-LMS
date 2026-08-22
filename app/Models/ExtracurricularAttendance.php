<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExtracurricularAttendance extends Model
{
    protected $fillable = [
    'meeting_id',
    'student_profile_id',
    'status',
    ];

    public function meeting()
    {
        return $this->belongsTo(
            ExtracurricularMeeting::class,
            'meeting_id'
        );
    }

    public function student()
    {
        return $this->belongsTo(
            StudentProfile::class,
            'student_profile_id'
        );
    }
}