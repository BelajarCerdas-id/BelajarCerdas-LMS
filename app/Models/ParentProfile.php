<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParentProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'school_partner_id',
        'student_id',
        'nama_lengkap',
        'pekerjaan',
        'alamat',
    ];

    // Relasi balik ke User Account
    public function UserAccount()
    {
        return $this->belongsTo(UserAccount::class, 'user_id');
    }
}