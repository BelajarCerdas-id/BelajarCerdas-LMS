<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kurikulum extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nama_kurikulum',
        'kode',
    ];

    public function UserAccount()
    {
        return $this->belongsTo(UserAccount::class, 'user_id');
    }

    // SYLLABUS
    public function Fase()
    {
        return $this->hasMany(Fase::class, 'kurikulum_id');
    }

    public function Kelas()
    {
        return $this->hasMany(Kelas::class, 'kurikulum_id');
    }

    public function Mapel()
    {
        return $this->hasMany(Mapel::class, 'kurikulum_id');
    }

    public function Bab()
    {
        return $this->hasMany(Bab::class, 'kurikulum_id');
    }

    public function SubBab()
    {
        return $this->hasMany(SubBab::class, 'kurikulum_id');
    }

    // LMS QUESTION BANK
    public function LmsQuestionBank()
    {
        return $this->hasMany(LmsQuestionBank::class, 'kurikulum_id');
    }

    // LMS SERVICE RULE
    public function Service()
    {
        return $this->hasMany(Service::class, 'kurikulum_id');
    }

    public function LmsContent()
    {
        return $this->hasMany(LmsContent::class, 'kurikulum_id');
    }
}
