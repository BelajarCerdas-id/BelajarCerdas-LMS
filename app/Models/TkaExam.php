<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TkaExam extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'school_partner_id',
        'title',
        'description',
        'thumbnail_path',
        'subjects',
        'difficulty',
        'passing_score',
        'duration_minutes',
        'total_questions',
        'randomize_questions',
        'show_results_immediately',
        'start_date',
        'end_date',
        'status',
        'is_active',
    ];

    protected $casts = [
        'subjects' => 'array',
        'randomize_questions' => 'boolean',
        'show_results_immediately' => 'boolean',
        'is_active' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function UserAccount()
    {
        return $this->belongsTo(UserAccount::class, 'user_id');
    }

    public function SchoolPartner()
    {
        return $this->belongsTo(SchoolPartner::class, 'school_partner_id');
    }

    public function Questions()
    {
        return $this->hasMany(TkaExamQuestion::class, 'tka_exam_id')->orderBy('question_number');
    }

    public function Attempts()
    {
        return $this->hasMany(TkaExamAttempt::class, 'tka_exam_id');
    }

    public function getThumbnailUrlAttribute()
    {
        if ($this->thumbnail_path) {
            return asset('storage/' . $this->thumbnail_path);
        }
        return asset('assets/images/default-tka.png');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')->where('is_active', true);
    }

    public function isAvailable()
    {
        $now = now();
        
        if ($this->start_date && $now->lt($this->start_date)) {
            return false;
        }
        
        if ($this->end_date && $now->gt($this->end_date)) {
            return false;
        }
        
        return true;
    }

    public function studentAttempt($studentId)
    {
        return $this->Attempts()->where('student_id', $studentId)->latest()->first();
    }
}
