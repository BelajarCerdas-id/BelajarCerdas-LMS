<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TopikMateri extends Model
{
    use HasFactory;

    protected $table = 'library_topik_materi';

    protected $fillable = [
        'kelas_id',
        'mapel_id',
        'nama_topik',
        'deskripsi'
    ];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function mapel()
    {
        return $this->belongsTo(Mapel::class, 'mapel_id');
    }

    public function books()
    {
        return $this->hasMany(
            LibraryBook::class,
            'topik_materi_id'
        );
    }
}