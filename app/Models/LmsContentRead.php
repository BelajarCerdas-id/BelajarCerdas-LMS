<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LmsContentRead extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'lms_meeting_content_id',
        'status',
    ];

    public function UserAcccount()
    {
        return $this->belongsTo(UserAccount::class, 'student_id');
    }

    public function LmsMeetingContent()
    {
        return $this->belongsTo(LmsMeetingContent::class, 'lms_meeting_content_id');
    }
}
