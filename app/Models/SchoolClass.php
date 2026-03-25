<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolClass extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_partner_id',
        'class_name',
        'fase_id',
        'kelas_id',
        'major_id',
        'wali_kelas_id',
        'tahun_ajaran',
        'status_class',
    ];

    public function TeacherMapel()
    {
        return $this->hasMany(TeacherMapel::class, 'school_class_id');
    }

    public function LmsMeetingContent()
    {
        return $this->hasMany(LmsMeetingContent::class, 'school_class_id');
    }

    public function SchoolPartner()
    {
        return $this->belongsTo(SchoolPartner::class, 'school_partner_id');
    }

    public function SchoolMajor()
    {
        return $this->belongsTo(SchoolMajor::class, 'major_id');
    }

    public function UserAccount()
    {
        return $this->belongsTo(UserAccount::class, 'wali_kelas_id');
    }

    public function StudentSchoolClass()
    {
        return $this->hasOne(StudentSchoolClass::class, 'school_class_id');
    }

    public function Fase()
    {
        return $this->belongsTo(Fase::class, 'fase_id');
    }

    public function Kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function SchoolAssessment()
    {
        return $this->hasMany(SchoolAssessment::class, 'school_class_id');
    }
}