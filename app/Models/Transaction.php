<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'school_partner_id',
        'feature_id',
        'feature_variant_id',
        'order_id',
        'payment_method',
        'snap_token',
        'transaction_status',
        'price',
        'transaction_source',
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
        return $this->belongsTo(FeaturePrice::class, 'feature_variant_id');
    }

    public function schoolLmsSubscription() {
        return $this->hasMany(SchoolLmsSubscription::class, 'transaction_id');
    }
}
