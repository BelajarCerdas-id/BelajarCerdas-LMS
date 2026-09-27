<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AcademicDocumentComment extends Model
{
    use HasFactory;

    protected $table = 'academic_document_comments';

    protected $fillable = [
        'academic_document_sheet_id',
        'user_id',
        'coordinate',
        'comment',
        'is_revision',
        'status',
        'resolved_at',
        'resolved_by',
    ];

    protected $casts = [
        'is_revision' => 'boolean',
        'resolved_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | SHEET
    |--------------------------------------------------------------------------
    */

    public function sheet(): BelongsTo
    {
        return $this->belongsTo(
            AcademicDocumentSheet::class,
            'academic_document_sheet_id',
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
    | RESOLVED BY
    |--------------------------------------------------------------------------
    */

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(
            UserAccount::class,
            'resolved_by',
            'id'
        );
    }
}