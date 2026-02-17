<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LmsMeetingContent extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'school_class_id',
        'school_partner_id',
        'lms_content_id',
        'service_id',
        'semester',
        'meeting_number',
        'meeting_date',
        'is_active',
    ];

    public function UserAccount()
    {
        return $this->belongsTo(UserAccount::class, 'teacher_id');
    }

    public function SchoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'school_class_id');
    }

    public function SchoolPartner()
    {
        return $this->belongsTo(SchoolPartner::class, 'school_partner_id');
    }

    public function LmsContent()
    {
        return $this->belongsTo(LmsContent::class, 'lms_content_id');
    }

    public function Service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }
}