<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnnouncementView extends Model
{
    use HasFactory;

    protected $fillable = [
        'announcement_id',
        'user_id',
    ];

    /**
     * Relasi kembali ke Pengumuman
     */
    public function announcement()
    {
        return $this->belongsTo(Announcement::class, 'announcement_id');
    }

    /**
     * Relasi ke User yang membaca
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}