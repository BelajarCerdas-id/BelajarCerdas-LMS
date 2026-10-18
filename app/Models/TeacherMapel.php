<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherMapel extends Model
{
    use HasFactory;

    protected $table = 'teacher_mapels';

    protected $fillable = [
        'user_id',
        'mapel_id',
        'school_class_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function UserAccount()
    {
        return $this->belongsTo(UserAccount::class, 'user_id');
    }

    public function Mapel()
    {
        return $this->belongsTo(Mapel::class, 'mapel_id');
    }

    public function SchoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'school_class_id');
    }

    public function SubjectAttendance()
    {
        return $this->hasMany(
            SubjectAttendance::class,
            'teacher_subject_id'
        );
    }
}