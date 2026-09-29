<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TkaTryoutPeriodSchOverride extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tka_tryout_period_id',
        'school_partner_id',
        'is_review',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function UserAccount()
    {
        return $this->belongsTo(UserAccount::class, 'user_id');
    }

    public function TkaTryoutPeriod()
    {
        return $this->belongsTo(TkaTryoutPeriod::class, 'tka_tryout_period_id');
    }

    public function SchoolPartner()
    {
        return $this->belongsTo(SchoolPartner::class, 'school_partner_id');
    }

    public function TkaTryoutSession()
    {
        return $this->hasMany(TkaTryoutSession::class, 'tka_tryout_period_sch_override_id');
    }

    public function TkaTryoutSubject()
    {
        return $this->hasMany(TkaTryoutSubject::class, 'tka_tryout_period_sch_override_id');
    }
}