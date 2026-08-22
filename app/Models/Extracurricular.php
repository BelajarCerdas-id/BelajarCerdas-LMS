<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Extracurricular extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'school_partner_id',

        // Informasi Ekskul
        'name',
        'description',
        'type',

        // Pembina
        'coach',

        // Status
        'status'
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIP
    |--------------------------------------------------------------------------
    */

    public function school()
    {
        return $this->belongsTo(
            SchoolPartner::class,
            'school_partner_id'
        );
    }

    public function students()
    {
        return $this->hasMany(
            ExtracurricularStudent::class,
            'extracurricular_id'
        );
    }

    public function meetings()
    {
        return $this->hasMany(
            ExtracurricularMeeting::class,
            'extracurricular_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | ATTRIBUTE
    |--------------------------------------------------------------------------
    */

    public function getTypeBadgeColorAttribute()
    {
        return $this->type === 'wajib'
            ? 'bg-red-100 text-red-600'
            : 'bg-blue-100 text-blue-600';
    }

    public function getTypeTextAttribute()
    {
        return $this->type === 'wajib'
            ? 'WAJIB'
            : 'PILIHAN';
    }

    public function getTotalStudentAttribute()
    {
        return $this->students()->count();
    }

    public function getTotalMeetingAttribute()
    {
        return $this->meetings()->count();
    }

    public function kelengkapan()
    {
        return $this->hasOne(
            ExtracurricularKelengkapan::class,
            'extracurricular_id'
        );
    }

   public function periods()
{
    return $this->hasMany(
        ExtracurricularPeriod::class,
        'extracurricular_id'
    )->orderByDesc('sequence');
}

public function activePeriod()
{
    return $this->hasOne(
        ExtracurricularPeriod::class,
        'extracurricular_id'
    )->where('is_active', true);
}
}