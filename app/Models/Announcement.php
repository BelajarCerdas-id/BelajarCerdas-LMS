<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_partner_id',
        'target_class_id', // 👈 TAMBAHKAN INI
        'author_id',
        'author_role',
        'target',
        'title',
        'type',
        'content',
    ];

    /**
     * Relasi ke User (Penulis Pengumuman)
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Relasi ke riwayat pembaca pengumuman
     */
    public function views()
    {
        return $this->hasMany(AnnouncementView::class, 'announcement_id');
    }
    
    /**
     * Cek apakah pengumuman ini sudah dibaca oleh user tertentu
     */
    public function isReadBy($userId)
    {
        return $this->views()->where('user_id', $userId)->exists();
    }
}