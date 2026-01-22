<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubBab extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'sub_bab',
        'kode',
        'bab_id',
        'mapel_id',
        'kelas_id',
        'fase_id',
        'kurikulum_id',
        'school_partner_id',
        'status_sub_bab',
    ];

    public function UserAccount()
    {
        return $this->belongsTo(UserAccount::class, 'user_id');
    }

    public function Bab()
    {
        return $this->belongsTo(Bab::class, 'bab_id');
    }

    public function Mapel()
    {
        return $this->belongsTo(Mapel::class, 'mapel_id');
    }

    public function Kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function Fase()
    {
        return $this->belongsTo(Fase::class, 'fase_id');
    }

    public function Kurikulum()
    {
        return $this->belongsTo(Kurikulum::class, 'kurikulum_id');
    }

    public function SchoolPartner()
    {
        return $this->belongsTo(SchoolPartner::class, 'school_partner_id');
    }

    // LMS QUESTION BANK
    public function LmsQuestionBank()
    {
        return $this->hasMany(LmsQuestionBank::class, 'sub_bab_id');
    }
}
