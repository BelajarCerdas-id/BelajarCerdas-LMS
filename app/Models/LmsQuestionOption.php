<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LmsQuestionOption extends Model
{
    use HasFactory;
    protected $casts = [
        'extra_data' => 'array',
    ];
    protected $fillable = [
        'question_id',
        'options_key',
        'options_value',
        'is_correct',
        'extra_data',
    ];

    public function LmsQuestionBank()
    {
        return $this->belongsTo(LmsQuestionBank::class, 'question_id');
    }
}
