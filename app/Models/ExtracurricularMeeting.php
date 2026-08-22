<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExtracurricularMeeting extends Model
{
    protected $fillable = [
        'extracurricular_id',
        'meeting_number',
        'meeting_date',
        'title',
    ];

    protected $casts = [
        'meeting_date' => 'date',
    ];

    public function extracurricular()
    {
        return $this->belongsTo(
            Extracurricular::class,
            'extracurricular_id'
        );
    }

    public function attendances()
    {
        return $this->hasMany(
            ExtracurricularAttendance::class,
            'meeting_id',
            'id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Alias agar bisa dipanggil $meeting->attendance()
    |--------------------------------------------------------------------------
    */

    public function attendance()
    {
        return $this->attendances();
    }
}