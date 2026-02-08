<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolQuestionBank extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id',
        'school_partner_id',
        'is_active',
    ];

    public function LmsQuestionBank()
    {
        return $this->belongsTo(LmsQuestionBank::class, 'question_id');
    }

    public function SchoolPartner()
    {
        return $this->belongsTo(SchoolPartner::class, 'school_partner_id');
    }
}