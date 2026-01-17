<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolLmsSubscription extends Model
{
    use HasFactory;
    protected $casts = [
        'start_date' => 'datetime',
        'end_date'   => 'datetime',
    ];

    protected $fillable = [
        'school_partner_id',
        'transaction_id',
        'start_date',
        'end_date',
        'subscription_status',
    ];

    public function SchoolPartner()
    {
        return $this->belongsTo(SchoolPartner::class, 'school_partner_id');
    }

    public function Transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }
}
