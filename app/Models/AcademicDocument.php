<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AcademicDocument extends Model
{
    use HasFactory;

    protected $table = 'academic_documents';

    protected $fillable = [
        'title',
        'original_filename',
        'file_path',
        'school_partner_id',
        'owner_user_id',
        'subject_id',
    ];

    /*
    |--------------------------------------------------------------------------
    | SHEETS
    |--------------------------------------------------------------------------
    */

    public function sheets(): HasMany
    {
        return $this->hasMany(
            AcademicDocumentSheet::class,
            'academic_document_id',
            'id'
        )->orderBy('sheet_index');
    }

    /*
    |--------------------------------------------------------------------------
    | OWNER / UPLOADER
    |--------------------------------------------------------------------------
    */

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(
            UserAccount::class,
            'owner_user_id',
            'id'
        );
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(
            UserAccount::class,
            'owner_user_id',
            'id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SCHOOL
    |--------------------------------------------------------------------------
    */

    public function schoolPartner(): BelongsTo
    {
        return $this->belongsTo(
            SchoolPartner::class,
            'school_partner_id',
            'id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SUBJECT / MAPEL
    |--------------------------------------------------------------------------
    */

    public function subject(): BelongsTo
    {
        return $this->belongsTo(
            Mapel::class,
            'subject_id',
            'id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | WORD CONTENT
    |--------------------------------------------------------------------------
    */

    public function wordContent(): HasOne
    {
        return $this->hasOne(
            AcademicDocumentWordContent::class,
            'academic_document_id',
            'id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | WORD COMMENTS
    |--------------------------------------------------------------------------
    */

    public function wordComments(): HasMany
    {
        return $this->hasMany(
            AcademicDocumentWordComment::class,
            'academic_document_id',
            'id'
        );
    }
}