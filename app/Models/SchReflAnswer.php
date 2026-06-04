<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchReflAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'school_class_id',
        'sch_refl_question_id',
        'answer',
        'emotion_status',
    ];

    public function UserAccount()
    {
        return $this->belongsTo(UserAccount::class, 'user_id');
    }

    public function SchReflQuestion()
    {
        return $this->belongsTo(SchReflQuestion::class, 'sch_refl_question_id');
    }

    public function SchoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'school_class_id');
    }
}