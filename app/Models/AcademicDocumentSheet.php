<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicDocumentSheet extends Model
{
    use HasFactory;

    protected $table = 'academic_document_sheets';

    protected $fillable = [
        'academic_document_id',
        'name',
        'sheet_index',
    ];

    protected $casts = [
        'sheet_index' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | DOCUMENT
    |--------------------------------------------------------------------------
    */

    public function document(): BelongsTo
    {
        return $this->belongsTo(
            AcademicDocument::class,
            'academic_document_id',
            'id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CELLS
    |--------------------------------------------------------------------------
    */

    public function cells(): HasMany
    {
        return $this->hasMany(
            AcademicDocumentCell::class,
            'academic_document_sheet_id',
            'id'
        )->orderBy('row_number')
         ->orderBy('column_number');
    }

    /*
    |--------------------------------------------------------------------------
    | COMMENTS
    |--------------------------------------------------------------------------
    */

    public function comments(): HasMany
    {
        return $this->hasMany(
            AcademicDocumentComment::class,
            'academic_document_sheet_id',
            'id'
        )->latest();
    }
}