<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonScheduleItem extends Model
{
    use HasFactory;

    // Menentukan nama tabel secara eksplisit
    protected $table = 'lesson_schedule_items';

    // Mengizinkan penyimpanan data secara massal (Mass Assignment)
    protected $fillable = [
        'lesson_schedule_id',
        'teacher_id',
        'mapel_id',
        'teacher_name',
        'subject_name',
        'day_of_week',
        'start_time',
        'end_time',
        'color',
    ];

    /**
     * Relasi kembali ke tabel Induk (Many-to-One)
     */
    public function schedule()
    {
        return $this->belongsTo(LessonSchedule::class, 'lesson_schedule_id', 'id');
    }
}