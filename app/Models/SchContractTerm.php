<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchContractTerm extends Model
{
    use HasFactory;

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'paid_at' => 'datetime',
    ];

    protected $fillable = [
        'contract_id',
        'term_number',
        'period_start',
        'period_end',
        'amount',
        'paid_at',
        'status',
    ];

    public function SchContract()
    {
        return $this->belongsTo(SchContract::class, 'contract_id');
    }

    public function SchTermStudent()
    {
        return $this->hasMany(SchTermStudent::class, 'term_id');
    }
}
