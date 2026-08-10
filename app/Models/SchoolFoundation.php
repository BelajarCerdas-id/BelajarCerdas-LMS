<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolFoundation extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_yayasan',
        'logo',
    ];

    public function SchoolPartner()
    {
        return $this->hasMany(SchoolPartner::class, 'school_foundation_id');
    }

    public function SchoolFoundationProfile()
    {
        return $this->hasMany(SchoolFoundationProfile::class, 'school_foundation_id');
    }
}
