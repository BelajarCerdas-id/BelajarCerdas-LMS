<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentMode extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
    ];

    public function SchoolAssessmentType() {
        return $this->hasMany(SchoolAssessmentType::class, 'assessment_mode_id');
    }
}
