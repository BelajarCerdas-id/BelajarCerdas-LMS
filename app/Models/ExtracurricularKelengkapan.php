<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExtracurricularKelengkapan extends Model
{
    protected $table = 'extracurricular_completions';

    protected $fillable = [
        'extracurricular_id',

        'silabus',
        'silabus_file',

        'prota',
        'prota_file',

        'prosem',
        'prosem_file',

        'rpp',
        'rpp_file',

        'comment',
    ];

    protected $casts = [
        'silabus' => 'boolean',
        'prota'   => 'boolean',
        'prosem'  => 'boolean',
        'rpp'     => 'boolean',
    ];

    public function extracurricular()
    {
        return $this->belongsTo(
            Extracurricular::class,
            'extracurricular_id'
        );
    }

    public function getTotalDocumentAttribute()
    {
        return collect([
            $this->silabus,
            $this->prota,
            $this->prosem,
            $this->rpp,
        ])->filter()->count();
    }

    public function getIsCompleteAttribute()
    {
        return $this->total_document === 4;
    }
}