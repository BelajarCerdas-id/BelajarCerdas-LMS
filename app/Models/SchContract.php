<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchContract extends Model
{
    use HasFactory;

    protected $casts = [
        'start_contract' => 'date',
        'end_contract' => 'date',
    ];

    protected $fillable = [
        'user_id',
        'school_partner_id',
        'feature_id',
        'feature_price_id',
        'contract_number',
        'start_contract',
        'end_contract',
        'init_student_count',
        'price_per_student',
        'total_term',
        'status',
    ];

    public function UserAccount() {
        return $this->belongsTo(UserAccount::class, 'user_id');
    }

    public function SchoolPartner() {
        return $this->belongsTo(SchoolPartner::class, 'school_partner_id');
    }

    public function Feature() {
        return $this->belongsTo(Feature::class, 'feature_id');
    }

    public function FeaturePrice() {
        return $this->belongsTo(FeaturePrice::class, 'feature_price_id');
    }

    public function SchContractTerm() {
        return $this->hasMany(SchContractTerm::class, 'contract_id');
    }
}