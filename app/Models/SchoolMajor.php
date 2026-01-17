<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolMajor extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_partner_id',
        'major_name',
        'major_code',
        'status_major',
    ];

    public function SchoolPartner()
    {
        return $this->belongsTo(SchoolPartner::class, 'school_partner_id');
    }

    public function SchoolClass()
    {
        return $this->hasMany(SchoolClass::class, 'major_id');
    }
}