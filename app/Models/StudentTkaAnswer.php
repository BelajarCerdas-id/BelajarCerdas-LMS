<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentTkaAnswer extends Model
{
    use HasFactory;

    protected $casts = [
        'answer_value' => 'array',
    ];

    protected $fillable = [
        'attempt_id',
        'question_id',
        'answer_value',
        'question_score',
        'status_answer',
    ];

    public function StudentTkaAttempt()
    {
        return $this->belongsTo(StudentTkaAttempt::class, 'attempt_id');
    }

    public function LmsQuestionBank()
    {
        return $this->belongsTo(LmsQuestionBank::class, 'question_id');
    }
}