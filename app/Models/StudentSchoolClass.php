<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentSchoolClass extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'school_class_id',
        'student_class_status',
        'academic_action',
    ];

    public function UserAccount() {
        return $this->belongsTo(UserAccount::class, 'student_id');
    }

    public function SchoolClass() {
        return $this->belongsTo(SchoolClass::class, 'school_class_id');
    }
}