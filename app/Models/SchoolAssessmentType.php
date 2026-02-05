<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolAssessmentType extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'school_partner_id',
        'name',
        'assessment_mode',
        'is_remedial_allowed',
        'max_remedial_attempt',
        'is_active',
    ];

    public function UserAccount() {
        return $this->belongsTo(UserAccount::class, 'user_id');
    }

    public function SchoolPartner() {
        return $this->belongsTo(SchoolPartner::class, 'school_partner_id');
    }
}
