<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolAssessmentComposition extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_assessment_id',
        'composition_name',
    ];

    public function SchoolAssessment()
    {
        return $this->belongsTo(SchoolAssessment::class, 'school_assessment_id');
    }

    public function SchoolAssessmentQuestion()
    {
        return $this->hasMany(SchoolAssessmentQuestion::class, 'sac_id');
    }

    public function StudentAssessmentComposition()
    {
        return $this->hasMany(StudentAssessmentComposition::class, 'sac_id');
    }
}
