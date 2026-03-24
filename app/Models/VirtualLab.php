<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VirtualLab extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'school_partner_id',
        'kurikulum_id',
        'kelas_id',
        'mapel_id',
        'bab_id',
        'sub_bab_id',
        'title',
        'description',
        'thumbnail_path',
        'video_path',
        'original_video_name',
        'video_extension',
        'video_mime',
        'video_size',
        'duration_seconds',
        'preview_duration',
        'subject',
        'experiment_type',
        'class_level',
        'materials_needed',
        'learning_objectives',
        'tags',
        'safety_notes',
        'requires_supervision',
        'status',
        'is_active',
    ];

    protected $casts = [
        'materials_needed' => 'array',
        'learning_objectives' => 'array',
        'tags' => 'array',
        'requires_supervision' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function UserAccount()
    {
        return $this->belongsTo(UserAccount::class, 'user_id');
    }

    public function SchoolPartner()
    {
        return $this->belongsTo(SchoolPartner::class, 'school_partner_id');
    }

    public function Kurikulum()
    {
        return $this->belongsTo(Kurikulum::class, 'kurikulum_id');
    }

    public function Kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function Mapel()
    {
        return $this->belongsTo(Mapel::class, 'mapel_id');
    }

    public function Bab()
    {
        return $this->belongsTo(Bab::class, 'bab_id');
    }

    public function SubBab()
    {
        return $this->belongsTo(SubBab::class, 'sub_bab_id');
    }

    public function Views()
    {
        return $this->hasMany(VirtualLabView::class, 'virtual_lab_id');
    }

    public function Reviews()
    {
        return $this->hasMany(VirtualLabReview::class, 'virtual_lab_id');
    }

    public function getVideoUrlAttribute()
    {
        return asset('storage/' . $this->video_path);
    }

    public function getThumbnailUrlAttribute()
    {
        if ($this->thumbnail_path) {
            return asset('storage/' . $this->thumbnail_path);
        }
        return asset('assets/images/default-video.png');
    }

    public function getFormattedDurationAttribute()
    {
        if (!$this->duration_seconds) {
            return '00:00';
        }
        
        $minutes = floor($this->duration_seconds / 60);
        $seconds = $this->duration_seconds % 60;
        
        return sprintf('%02d:%02d', $minutes, $seconds);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')->where('is_active', true);
    }

    public function scopeBySubject($query, $subject)
    {
        return $query->where('subject', $subject);
    }

    public function averageRating()
    {
        return $this->Reviews()->avg('rating');
    }
}
