<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PracticeExamAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'practice_exam_id',
        'student_id',
        'user_id',
        'started_at',
        'submitted_at',
        'time_spent_seconds',
        'attempt_number',
        'correct_answers',
        'wrong_answers',
        'unanswered',
        'score',
        'is_completed',
        'passed',
        'status',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
        'is_completed' => 'boolean',
        'passed' => 'boolean',
    ];

    public function PracticeExam()
    {
        return $this->belongsTo(PracticeExam::class, 'practice_exam_id');
    }

    public function Student()
    {
        return $this->belongsTo(StudentProfile::class, 'student_id');
    }

    public function UserAccount()
    {
        return $this->belongsTo(UserAccount::class, 'user_id');
    }

    public function Answers()
    {
        return $this->hasMany(PracticeExamAnswer::class, 'practice_exam_attempt_id');
    }

    public function getProgressPercentageAttribute()
    {
        $totalQuestions = $this->PracticeExam->Questions()->count();
        if ($totalQuestions === 0) {
            return 0;
        }
        
        $answered = $this->Answers()->count();
        return round(($answered / $totalQuestions) * 100);
    }

    public function getFormattedScoreAttribute()
    {
        return number_format($this->score, 2);
    }

    public function getFormattedTimeSpentAttribute()
    {
        $hours = floor($this->time_spent_seconds / 3600);
        $minutes = floor(($this->time_spent_seconds % 3600) / 60);
        $seconds = $this->time_spent_seconds % 60;
        
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }
}
