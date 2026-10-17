<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherReflection extends Model
{
    use HasFactory;

    protected $table = 'teacher_reflections';

    protected $fillable = [
        'user_id',
        'school_partner_id',
        'mapel_id',
        'title',
        'status',
    ];

    /*
    |--------------------------------------------------------------------------
    | USER / OWNER
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
    | MAPEL
    |--------------------------------------------------------------------------
    */

    public function mapel(): BelongsTo
    {
        return $this->belongsTo(
            Mapel::class,
            'mapel_id',
            'id'
        );
    }
}