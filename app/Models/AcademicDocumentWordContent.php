<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AcademicDocumentWordContent extends Model
{
    protected $table = 'academic_document_word_contents';

    protected $fillable = [
        'academic_document_id',
        'title',
        'content',
        'last_saved_at',
    ];

    protected $casts = [
        'last_saved_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | DOCUMENT
    |--------------------------------------------------------------------------
    */

    public function academicDocument(): BelongsTo
    {
        return $this->belongsTo(
            AcademicDocument::class,
            'academic_document_id',
            'id'
        );
    }
}