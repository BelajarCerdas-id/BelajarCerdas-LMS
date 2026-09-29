<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TkaTryoutSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tka_tryout_period_id',
        'tka_tryout_period_sch_override_id',
        'session_date',
        'session_number',
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'session_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    public function UserAccount()
    {
        return $this->belongsTo(UserAccount::class, 'user_id');
    }

    public function TkaTryoutPeriod()
    {
        return $this->belongsTo(TkaTryoutPeriod::class, 'tka_tryout_period_id');
    }

    public function TkaTryoutPeriodSchOverride()
    {
        return $this->belongsTo(TkaTryoutPeriodSchOverride::class, 'tka_tryout_period_sch_override_id');
    }

    public function TkaTryoutSessionStudent()
    {
        return $this->hasMany(TkaTryoutSessionStudent::class, 'tka_tryout_session_id');
    }

    public function StudentTkaAttempt()
    {
        return $this->hasMany(StudentTkaAttempt::class, 'tka_tryout_session_id');
    }
}