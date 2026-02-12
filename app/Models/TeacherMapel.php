<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherMapel extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'mapel_id',
        'school_class_id',
        'is_active',
    ];

    public function UserAccount()
    {
        return $this->belongsTo(UserAccount::class, 'user_id');
    }

    public function Mapel()
    {
        return $this->belongsTo(Mapel::class, 'mapel_id');
    }

    public function SchoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'school_class_id');
    }
}