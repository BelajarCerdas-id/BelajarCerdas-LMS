<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_partner_id',
        'teacher_id',
        'target_class_id',
        'title',
        'content',
        'type',
        'views_count',
    ];
}