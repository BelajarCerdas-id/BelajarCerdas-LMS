<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolStaffProfile extends Model
{
    use HasFactory;

    // Pastikan nama tabel benar
    protected $table = 'school_staff_profiles';

    protected $fillable = [
        'user_id',
        'yayasan_id',
        'school_partner_id',
        'enrollment_type',
        'nama_lengkap',
        'nik',
        'personal_email',
    ];

    // Relasi yang sudah kamu buat
    public function UserAccount()
    {
        return $this->belongsTo(UserAccount::class, 'user_id');
    }

    public function SchoolPartner()
    {
        return $this->belongsTo(SchoolPartner::class, 'school_partner_id');
    }
}
