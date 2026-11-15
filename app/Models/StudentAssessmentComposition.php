<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentAssessmentComposition extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_assessment_id',
        'sac_id',
        'user_id',
    ];

    public function SchoolAssessment()
    {
        return $this->belongsTo(SchoolAssessment::class, 'school_assessment_id');
    }

    public function SchoolAssessmentComposition()
    {
        return $this->belongsTo(SchoolAssessmentComposition::class, 'sac_id');
    }

    public function UserAccount()
    {
        return $this->belongsTo(UserAccount::class, 'user_id');
    }
}
