<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TkaTryoutSubject extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tka_tryout_period_id',
        'tka_tryout_period_sch_override_id',
        'subject_date',
        'subject_id',
        'total_question',
        'duration',
        'is_active',
    ];

    protected $casts = [
        'subject_date' => 'date',
        'is_active' => 'boolean',
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

    public function Mapel()
    {
        return $this->belongsTo(Mapel::class, 'subject_id');
    }
}