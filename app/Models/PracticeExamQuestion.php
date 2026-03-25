<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PracticeExamQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'practice_exam_id',
        'lms_question_bank_id',
        'question_number',
        'points',
    ];

    public function PracticeExam()
    {
        return $this->belongsTo(PracticeExam::class, 'practice_exam_id');
    }

    public function QuestionBank()
    {
        return $this->belongsTo(LmsQuestionBank::class, 'lms_question_bank_id');
    }

    public function Answers()
    {
        return $this->hasMany(PracticeExamAnswer::class, 'practice_exam_question_id');
    }
}
