<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentAssessmentSummary extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'root_assessment_id',
        'main_score',
        'susulan_score',
        'last_remedial_score',
        'pengayaan_score',
        'final_score',
        'score_source',
        'remedial_count',
        'last_remedial_assessment_id',
    ];

    public function UserAccount()
    {
        return $this->belongsTo(UserAccount::class, 'student_id');
    }

    public function SchoolAssessment()
    {
        return $this->belongsTo(SchoolAssessment::class, 'root_assessment_id');
    }
}