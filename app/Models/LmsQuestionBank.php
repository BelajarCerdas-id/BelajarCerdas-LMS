<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LmsQuestionBank extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'school_partner_id',
        'kurikulum_id',
        'kelas_id',
        'mapel_id',
        'bab_id',
        'sub_bab_id',
        'questions',
        'difficulty',
        'bloom',
        'explanation',
        'status_bank_soal',
        'tipe_soal',
        'question_source',
    ];

    public function LmsQuestionOption()
    {
        return $this->hasMany(LmsQuestionOption::class, 'question_id');
    }

    public function SchoolQuestionBank()
    {
        return $this->hasMany(SchoolQuestionBank::class, 'question_id');
    }

    public function UserAccount()
    {
        return $this->belongsTo(UserAccount::class, 'user_id');
    }

    public function SchoolPartner()
    {
        return $this->belongsTo(SchoolPartner::class, 'school_partner_id');
    }

    public function Kurikulum()
    {
        return $this->belongsTo(Kurikulum::class, 'kurikulum_id');
    }

    public function Kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function Mapel()
    {
        return $this->belongsTo(Mapel::class, 'mapel_id');
    }

    public function Bab()
    {
        return $this->belongsTo(Bab::class, 'bab_id');
    }

    public function SubBab()
    {
        return $this->belongsTo(SubBab::class, 'sub_bab_id');
    }
}