<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UploadSession extends Model
{
    use HasFactory;

    protected $table = 'upload_sessions';

    protected $fillable = [
        'upload_id',
        'file_name',
        'path',
        'total_chunks',
        'uploaded_chunks',
        'status',
    ];

    protected $casts = [
        'total_chunks' => 'integer',
        'uploaded_chunks' => 'integer',
    ];

    // ================= STATUS HELPERS =================

    public function isUploading()
    {
        return $this->status === 'uploading';
    }

    public function isDone()
    {
        return $this->status === 'done';
    }

    public function isFailed()
    {
        return $this->status === 'failed';
    }

    // ================= PROGRESS =================

    public function getProgressAttribute()
    {
        if ($this->total_chunks == 0) return 0;

        return round(($this->uploaded_chunks / $this->total_chunks) * 100);
    }
}