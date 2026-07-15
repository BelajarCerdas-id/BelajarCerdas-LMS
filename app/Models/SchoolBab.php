<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolBab extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_partner_id',
        'bab_id',
        'is_active',
    ];

    public function SchoolPartner()
    {
        return $this->belongsTo(SchoolPartner::class, 'school_partner_id');
    }

    public function Bab()
    {
        return $this->belongsTo(Bab::class, 'bab_id');
    }
}
