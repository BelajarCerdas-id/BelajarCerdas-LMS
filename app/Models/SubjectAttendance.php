<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubjectAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'subject_teacher_id',
        'meeting_number',
        'semester',
        'attendance_status',
    ];

    public function Student()
    {
        return $this->belongsTo(UserAccount::class, 'student_id');
    }

    public function TeacherMapel()
    {
        return $this->belongsTo(TeacherMapel::class, 'subject_teacher_id');
    }
}