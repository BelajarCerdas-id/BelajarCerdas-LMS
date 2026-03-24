<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LearningResource extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'resource_type',
        'subject',
        'class_level',
        'description',
        'author',
        'file_path',
        'file_name',
        'file_size',
        'thumbnail_path',
        'preview_pages',
        'status',
    ];

    public function UserAccount()
    {
        return $this->belongsTo(UserAccount::class, 'user_id');
    }
}
