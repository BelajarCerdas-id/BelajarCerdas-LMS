<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolLmsContent extends Model
{
    use HasFactory;

    protected $fillable = [
        'lms_content_id',
        'school_partner_id',
        'is_active',
    ];

    public function lmsContent()
    {
        return $this->belongsTo(LmsContent::class, 'lms_content_id');
    }

    public function schoolPartner()
    {
        return $this->belongsTo(SchoolPartner::class, 'school_partner_id');
    }
}
