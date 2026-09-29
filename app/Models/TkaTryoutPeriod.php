<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TkaTryoutPeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tahun_ajaran',
        'period_number',
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

    public function TkaTryoutSession()
    {
        return $this->hasMany(TkaTryoutSession::class, 'tka_tryout_period_id');
    }

    public function TkaTryoutPeriodSchOverride()
    {
        return $this->hasMany(TkaTryoutPeriodSchOverride::class, 'tka_tryout_period_id');
    }

    public function TkaTryoutSubject()
    {
        return $this->hasMany(TkaTryoutSubject::class, 'tka_tryout_period_id')
            ->whereNull('tka_tryout_period_sch_override_id');
    }
}