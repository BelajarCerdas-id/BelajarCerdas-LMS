<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRule extends Model
{
    use HasFactory;

    protected $casts = [
        'allowed_extension' => 'array',
    ];

    protected $fillable = [
        'service_id',
        'upload_type',
        'allowed_extension',
        'max_size_mb',
        'is_repeatable',
    ];

    public function LmsContentItem()
    {
        return $this->hasMany(LmsContent::class, 'lms_content_id');
    }

    public function Service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }
}