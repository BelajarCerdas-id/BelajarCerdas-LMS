<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExtracurricularStudent extends Model
{
    protected $fillable = [
        'extracurricular_id',
        'school_partner_id',
        'student_profile_id',
        'student_name',
        'kelas',
        'tipe_kelas',
        'status',
    ];

    public function extracurricular()
    {
        return $this->belongsTo(
            Extracurricular::class,
            'extracurricular_id'
        );
    }

    public function studentProfile()
{
    return $this->belongsTo(
        \App\Models\StudentProfile::class,
        'student_profile_id'
    );
}

    /*
    |--------------------------------------------------------------------------
    | Relasi Absensi
    |--------------------------------------------------------------------------
    */

    public function attendances()
    {
        return $this->hasMany(
            ExtracurricularAttendance::class,
            'student_profile_id',
            'student_profile_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Cek hadir pada meeting tertentu
    |--------------------------------------------------------------------------
    */

    public function attendance($meetingId)
{
    return $this->attendances
        ->where('meeting_id', $meetingId)
        ->where('status', 'present') // atau 'hadir' sesuai isi database
        ->isNotEmpty();
}
}   