<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Yayasan extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_yayasan',
        'npwp',
        'alamat',
        'logo',
        'kontak',
        'email',
    ];

    public function SchoolPartners()
    {
        return $this->hasMany(SchoolPartner::class, 'yayasan_id');
    }

    public function YayasanProfiles()
    {
        return $this->hasMany(YayasanProfile::class, 'yayasan_id');
    }
}
