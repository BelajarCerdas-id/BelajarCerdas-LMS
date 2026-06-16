<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Bab;
use App\Models\Mapel;
use App\Models\Kelas;
use App\Models\TopikMateri;

class LibraryBook extends Model
{
    protected $fillable = [
    'title',
    'description',
    'cover',
    'file',
    'kelas_id',
    'mapel_id',
    'bab_id',
    'topik_materi_id',
    'series_no',
    'tipe'
];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function mapel()
    {
        return $this->belongsTo(Mapel::class, 'mapel_id');
    }

    public function bab()
    {
        return $this->belongsTo(Bab::class, 'bab_id');
    }
    public function topik()
    {
        return $this->belongsTo(
            TopikMateri::class,
            'topik_materi_id'
        );
    }
}