<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TkaExamQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'tka_exam_id',
        'lms_question_bank_id',
        'question_number',
        'points',
        'subject_category',
    ];

    public function TkaExam()
    {
        return $this->belongsTo(TkaExam::class, 'tka_exam_id');
    }

    public function QuestionBank()
    {
        return $this->belongsTo(LmsQuestionBank::class, 'lms_question_bank_id');
    }

    public function Answers()
    {
        return $this->hasMany(TkaExamAnswer::class, 'tka_exam_question_id');
    }
}
