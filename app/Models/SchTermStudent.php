<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchTermStudent extends Model
{
    use HasFactory;

    protected $fillable = [
        'office_id',
        'term_id',
        'student_id',
        'status',
    ];

    public function OfficeAccount()
    {
        return $this->belongsTo(UserAccount::class, 'office_id');
    }

    public function SchContractTerm()
    {
        return $this->belongsTo(SchContractTerm::class, 'term_id');
    }

    public function StudentAccount()
    {
        return $this->belongsTo(UserAccount::class, 'student_id');
    }
}