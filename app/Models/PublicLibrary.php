<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PublicLibrary extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'publisher',
        'subject',
        'class_level',
        'description',
        'thumbnail_path',
        'file_path',
        'original_file_name',
        'file_extension',
        'file_mime',
        'file_size',
    ];

    public function UserAccount()
    {
        return $this->belongsTo(UserAccount::class, 'user_id');
    }
}
