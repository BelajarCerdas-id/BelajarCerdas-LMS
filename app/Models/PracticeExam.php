<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PracticeExam extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'school_partner_id',
        'kurikulum_id',
        'kelas_id',
        'mapel_id',
        'bab_id',
        'sub_bab_id',
        'title',
        'description',
        'thumbnail_path',
        'exam_type',
        'difficulty',
        'duration_minutes',
        'total_questions',
        'passing_score',
        'randomize_questions',
        'show_explanation',
        'allow_retry',
        'status',
        'is_active',
    ];

    protected $casts = [
        'randomize_questions' => 'boolean',
        'show_explanation' => 'boolean',
        'allow_retry' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function UserAccount()
    {
        return $this->belongsTo(UserAccount::class, 'user_id');
    }

    public function SchoolPartner()
    {
        return $this->belongsTo(SchoolPartner::class, 'school_partner_id');
    }

    public function Kurikulum()
    {
        return $this->belongsTo(Kurikulum::class, 'kurikulum_id');
    }

    public function Kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function Mapel()
    {
        return $this->belongsTo(Mapel::class, 'mapel_id');
    }

    public function Bab()
    {
        return $this->belongsTo(Bab::class, 'bab_id');
    }

    public function SubBab()
    {
        return $this->belongsTo(SubBab::class, 'sub_bab_id');
    }

    public function Questions()
    {
        return $this->hasMany(PracticeExamQuestion::class, 'practice_exam_id')->orderBy('question_number');
    }

    public function Attempts()
    {
        return $this->hasMany(PracticeExamAttempt::class, 'practice_exam_id');
    }

    public function getThumbnailUrlAttribute()
    {
        if ($this->thumbnail_path) {
            return asset('storage/' . $this->thumbnail_path);
        }
        return asset('assets/images/default-practice.png');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('exam_type', $type);
    }

    public function studentAttempt($studentId)
    {
        return $this->Attempts()->where('student_id', $studentId)->latest()->first();
    }
}
