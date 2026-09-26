<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserActivity extends Model
{
    use HasFactory;

    protected $table = 'user_activities';

    protected $fillable = [
        'user_id',
        'school_partner_id',
        'role',
        'module',
        'sub_module',
        'action',
        'url',
        'route_name',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    public $timestamps = true;

    /**
     * Relasi ke akun pengguna (UserAccount)
     */
    public function UserAccount()
    {
        return $this->belongsTo(UserAccount::class, 'user_id');
    }

    /**
     * Relasi ke sekolah partner (SchoolPartner)
     */
    public function SchoolPartner()
    {
        return $this->belongsTo(SchoolPartner::class, 'school_partner_id');
    }

    /**
     * Scope untuk filter berdasarkan role
     */
    public function scopeForRole($query, $role)
    {
        if (!empty($role) && $role !== 'all') {
            return $query->where('role', $role);
        }
        return $query;
    }

    /**
     * Scope untuk filter berdasarkan sekolah
     */
    public function scopeForSchool($query, $schoolId)
    {
        if (!empty($schoolId) && $schoolId !== 'all') {
            return $query->where('school_partner_id', $schoolId);
        }
        return $query;
    }

    /**
     * Scope untuk filter berdasarkan modul
     */
    public function scopeForModule($query, $module)
    {
        if (!empty($module) && $module !== 'all') {
            return $query->where('module', $module);
        }
        return $query;
    }

    /**
     * Scope untuk hari ini
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', now()->toDateString());
    }
}
