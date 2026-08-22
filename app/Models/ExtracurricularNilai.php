<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExtracurricularNilai extends Model
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
        'nilai' => 'decimal:2',
    ];

    public function period()
    {
        return $this->belongsTo(
            ExtracurricularPeriod::class,
            'period_id'
        );
    }
}