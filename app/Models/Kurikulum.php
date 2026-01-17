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
}
