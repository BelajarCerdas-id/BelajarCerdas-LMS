<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fase extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nama_fase',
        'kode',
        'kurikulum_id',
    ];

    public function UserAccount()
    {
        return $this->belongsTo(UserAccount::class, 'user_id');
    }

    public function Kurikulum()
    {
        return $this->belongsTo(Kurikulum::class, 'kurikulum_id');
    }

    public function Kelas()
    {
        return $this->hasOne(Kelas::class, 'fase_id');
    }

    public function Mapel()
    {
        return $this->hasMany(Mapel::class, 'fase_id');
    }

    public function Bab()
    {
        return $this->hasMany(Bab::class, 'fase_id');
    }

    public function SchoolClass()
    {
        return $this->hasOne(SchoolClass::class, 'fase_id');
    }
}
