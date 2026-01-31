<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LmsContentItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'lms_content_id',
        'service_rule_id',
        'value_text',
        'value_file',
        'original_filename',
    ];

    public function LmsContent()
    {
        return $this->belongsTo(LmsContent::class, 'lms_content_id');
    }

    public function ServiceRule()
    {
        return $this->belongsTo(ServiceRule::class, 'service_rule_id');
    }
}
