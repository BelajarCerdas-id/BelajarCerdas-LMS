<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class UserAccount extends Authenticatable
{
    use HasFactory;
    protected $fillable = [
        'email',
        'password',
        'no_hp',
        'role',
        'status_akun',
    ];

    // PROFILE USERS
    public function OfficeProfile() {
        return $this->hasOne(OfficeProfile::class, 'user_id');
    }
}
