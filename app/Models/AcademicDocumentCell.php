<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AcademicDocumentCell extends Model
{
    use HasFactory;

    protected $table = 'academic_document_cells';

    protected $fillable = [
        'academic_document_sheet_id',
        'column_index',
        'coordinate',
        'row_number',
        'column_number',
        'value',
        'formula',
        'calculated_value',
        'data_type',
        'style',
        'is_merged',
        'merge_range',
    ];

    protected $casts = [
        'column_index' => 'integer',
        'row_number' => 'integer',
        'column_number' => 'integer',
        'is_merged' => 'boolean',
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
}