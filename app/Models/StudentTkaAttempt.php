<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentTkaAttempt extends Model
{
    use HasFactory;

    protected $casts = [
        'question_order' => 'array',
    ];

    protected $fillable = [
        'student_id',
        'kelas_id',
        'mapel_id',
        'question_order',
        'total_question',
        'status',
        'attempt_type',
    ];

    public function StudentTkaAnswer()
    {
        return $this->hasMany(StudentTkaAnswer::class, 'attempt_id');
    }

    public function UserAccount()
    {
        return $this->belongsTo(UserAccount::class, 'student_id');
    }

    public function Kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function Mapel()
    {
        return $this->belongsTo(Mapel::class, 'mapel_id');
    }
}