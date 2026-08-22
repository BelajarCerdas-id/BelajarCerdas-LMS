<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExtracurricularPeriod extends Model
{
    protected $fillable = [
        'extracurricular_id',
        'label',
        'sequence',
        'is_active',
        'nilai_downloaded_at',
        'nilai_uploaded_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'nilai_downloaded_at' => 'datetime',
        'nilai_uploaded_at' => 'datetime',
    ];

    public function extracurricular()
    {
        return $this->belongsTo(
            Extracurricular::class,
            'extracurricular_id'
        );
    }

    public function values()
    {
        return $this->hasMany(
            ExtracurricularValue::class,
            'period_id'
        );
    }
}