<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchReflQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'school_partner_id',
        'title',
        'question',
        'tahun_ajaran',
    ];

    public function SchReflAnswer()
    {
        return $this->hasOne(SchReflAnswer::class, 'sch_refl_question_id');
    }

    public function SchReflTarget()
    {
        return $this->hasMany(SchReflTarget::class, 'sch_refl_question_id');
    }

    public function UserAccount()
    {
        return $this->belongsTo(UserAccount::class, 'user_id');
    }

    public function schoolPartner()
    {
        return $this->belongsTo(SchoolPartner::class, 'school_partner_id');
    }
}