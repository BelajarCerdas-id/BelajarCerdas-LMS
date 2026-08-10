<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolFoundationFinanceAccess extends Model
{
    use HasFactory;
    protected $fillable = [
        'school_partner_id',
        'link',
        'status_access',
    ];

    public function SchoolPartner()
    {
        return $this->belongsTo(SchoolPartner::class, 'school_partner_id');
    }
}
