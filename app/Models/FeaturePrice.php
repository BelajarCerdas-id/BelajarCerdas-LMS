<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeaturePrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'feature_id',
        'variant_name',
        'variant_type',
        'duration',
        'price',
    ];

    public function Feature() {
        return $this->belongsTo(Feature::class, 'feature_id');
    }
}
