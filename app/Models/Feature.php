<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feature extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_fitur',
        'status_fitur',
    ];

    public function FeaturePrice() {
        return $this->hasOne(FeaturePrice::class, 'feature_id');
    }
}
