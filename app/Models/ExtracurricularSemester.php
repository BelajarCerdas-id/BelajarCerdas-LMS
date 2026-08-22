<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExtracurricularSemester extends Model
{
    protected $fillable = [
        'extracurricular_id',
        'label',
        'semester',
        'fase',
        'started_at',
        'finished_at',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function extracurricular()
    {
        return $this->belongsTo(
            Extracurricular::class,
            'extracurricular_id'
        );
    }
}