<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolSubBab extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_partner_id',
        'sub_bab_id',
        'is_active',
    ];

    public function SchoolPartner()
    {
        return $this->belongsTo(SchoolPartner::class, 'school_partner_id');
    }

    public function Subbab()
    {
        return $this->belongsTo(SubBab::class, 'sub_bab_id');
    }
}
