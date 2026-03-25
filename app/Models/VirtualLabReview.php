<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VirtualLabReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'virtual_lab_id',
        'student_id',
        'user_id',
        'rating',
        'comment',
        'is_helpful',
    ];

    protected $casts = [
        'is_helpful' => 'boolean',
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
}
