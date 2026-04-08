<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LibraryStudent extends Model
{
    protected $table = 'library_student';

    protected $fillable = [
        'book_id',
        'title',
        'genre',
        'student_name'
    ];
}