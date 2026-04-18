<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonSchedule extends Model
{
    use HasFactory;

    protected $table = 'lesson_schedules';

    protected $fillable = [
        'school_partner_id',
        'class_id',
        'class_name',
        'status',
    ];

    /**
     * Relasi ke detail jam pelajaran (One-to-Many)
     * Satu header jadwal memiliki banyak item pelajaran
     */
    public function items()
    {
        return $this->hasMany(LessonScheduleItem::class, 'lesson_schedule_id', 'id');
    }
}
