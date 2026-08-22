<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExtracurricularValue extends Model
{
    protected $table = 'extracurricular_nilai';

    protected $fillable = [
    'period_id',
    'student_profile_id',
    'student_name',
    'nisn',
    'kelas',
    'tipe_kelas',
    'total_absen',
    'total_pertemuan',
    'nilai',
    'deskripsi',
];

    protected $casts = [
        'total_absen' =>
            'integer',

        'total_pertemuan' =>
            'integer',
    ];


    /*
    |--------------------------------------------------------------------------
    | PERIODE
    |--------------------------------------------------------------------------
    */

    public function period()
    {
        return $this->belongsTo(
            ExtracurricularPeriod::class,
            'period_id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | SISWA
    |--------------------------------------------------------------------------
    */

    public function student()
    {
        return $this->belongsTo(
            StudentProfile::class,
            'student_profile_id'
        );
    }
}