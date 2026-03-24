<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PracticeExamAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'practice_exam_attempt_id',
        'practice_exam_question_id',
        'student_answer',
        'is_correct',
        'points_earned',
        'time_spent_seconds',
        'viewed_explanation',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'viewed_explanation' => 'boolean',
    ];

    public function ExamAttempt()
    {
        return $this->belongsTo(PracticeExamAttempt::class, 'practice_exam_attempt_id');
    }

    public function ExamQuestion()
    {
        return $this->belongsTo(PracticeExamQuestion::class, 'practice_exam_question_id');
    }
}
