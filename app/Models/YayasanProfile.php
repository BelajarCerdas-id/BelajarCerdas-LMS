<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class YayasanProfile extends Model
{
    use HasFactory;

    protected $table = 'yayasan_profiles';

    protected $fillable = [
        'user_id',
        'yayasan_id',
        'nama_lengkap',
    ];

    public function UserAccount()
    {
        return $this->belongsTo(UserAccount::class, 'user_id');
    }

    public function Yayasan()
    {
        return $this->belongsTo(Yayasan::class, 'yayasan_id');
    }
}
