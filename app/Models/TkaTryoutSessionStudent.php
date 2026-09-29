<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TkaTryoutSessionStudent extends Model
{
    use HasFactory;

    protected $fillable = [
        'tka_tryout_session_id',
        'student_id',
        'status',
    ];

    public function TkaTryoutSession()
    {
        return $this->belongsTo(TkaTryoutSession::class, 'tka_tryout_session_id');
    }

    public function UserAccount()
    {
        return $this->belongsTo(UserAccount::class, 'student_id');
    }
}
