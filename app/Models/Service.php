<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'kurikulum_id',
        'name',
        'school_partner_status',
    ];

    public function Kurikulum()
    {
        return $this->belongsTo(Kurikulum::class, 'kurikulum_id');
    }

    public function ServiceRule()
    {
        return $this->hasMany(ServiceRule::class, 'service_id');
    }

    public function LmsContent()
    {
        return $this->hasMany(LmsContent::class, 'service_id');
    }
}