<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VirtualLabView extends Model
{
    use HasFactory;

    protected $fillable = [
        'virtual_lab_id',
        'student_id',
        'user_id',
        'watched_duration_seconds',
        'last_position_seconds',
        'is_completed',
        'completed_at',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function VirtualLab()
    {
        return $this->belongsTo(VirtualLab::class, 'virtual_lab_id');
    }

    public function Student()
    {
        return $this->belongsTo(StudentProfile::class, 'student_id');
    }

    public function UserAccount()
    {
        return $this->belongsTo(UserAccount::class, 'user_id');
    }

    public function getProgressPercentageAttribute()
    {
        if (!$this->VirtualLab->duration_seconds) {
            return 0;
        }
        
        return round(($this->watched_duration_seconds / $this->VirtualLab->duration_seconds) * 100);
    }
}
