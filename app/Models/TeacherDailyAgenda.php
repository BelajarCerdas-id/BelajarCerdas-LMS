<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherDailyAgenda extends Model
{
    use HasFactory;

    protected $casts = [
        'agenda_date' => 'date',
    ];

    protected $fillable = [
        'teacher_id',
        'school_partner_id',
        'school_class_id',
        'mapel_id',
        'agenda_date',
        'learning_activity',
        'feedback',
        'status',
    ];

    public function UserAccount()
    {
        return $this->belongsTo(UserAccount::class, 'teacher_id');
    }

    public function SchoolPartner()
    {
        return $this->belongsTo(SchoolPartner::class, 'school_partner_id');
    }

    public function SchoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'school_class_id');
    }

    public function Mapel()
    {
        return $this->belongsTo(Mapel::class, 'mapel_id');
    }
}