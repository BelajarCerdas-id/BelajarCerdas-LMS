<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchReflTarget extends Model
{
    use HasFactory;

    protected $fillable = [
        'sch_refl_question_id',
        'target_class_id',
    ];

    public function SchReflQuestion()
    {
        return $this->belongsTo(SchReflQuestion::class, 'sch_refl_question_id');
    }

    public function Kelas()
    {
        return $this->belongsTo(Kelas::class, 'target_class_id');
    }
}