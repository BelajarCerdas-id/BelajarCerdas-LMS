<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TkaExamAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'tka_exam_attempt_id',
        'tka_exam_question_id',
        'student_answer',
        'is_correct',
        'points_earned',
        'time_spent_seconds',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
    ];

    public function ExamAttempt()
    {
        return $this->belongsTo(TkaExamAttempt::class, 'tka_exam_attempt_id');
    }

    public function ExamQuestion()
    {
        return $this->belongsTo(TkaExamQuestion::class, 'tka_exam_question_id');
    }
}
