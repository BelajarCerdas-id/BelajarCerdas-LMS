<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicDocumentWordComment extends Model
{
    protected $table = 'academic_document_word_comments';

    protected $fillable = [
        'academic_document_id',
        'user_id',
        'parent_id',
        'page_number',
        'selected_text',
        'comment_text',
        'anchor_key',
        'resolved_at',
    ];

    protected $casts = [
        'page_number' => 'integer',
        'resolved_at' => 'datetime',
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

    /*
    |--------------------------------------------------------------------------
    | USER
    |--------------------------------------------------------------------------
    */

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            UserAccount::class,
            'user_id',
            'id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | PARENT COMMENT
    |--------------------------------------------------------------------------
    */

    public function parent(): BelongsTo
    {
        return $this->belongsTo(
            self::class,
            'parent_id',
            'id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | REPLIES
    |--------------------------------------------------------------------------
    */

    public function replies(): HasMany
    {
        return $this->hasMany(
            self::class,
            'parent_id',
            'id'
        )->with('user')->latest();
    }

    /*
    |--------------------------------------------------------------------------
    | STATUS
    |--------------------------------------------------------------------------
    */

    public function getIsResolvedAttribute(): bool
    {
        return !is_null($this->resolved_at);
    }
}