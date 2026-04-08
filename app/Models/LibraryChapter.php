<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LibraryChapter extends Model
{
    protected $table = 'library_chapter';

    protected $fillable = [
        'mapel',
        'chapter_name'
    ];

    public function books()
    {
        return $this->hasMany(LibraryBook::class, 'chapter_id');
    }
}