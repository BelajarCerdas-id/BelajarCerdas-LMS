<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\ParentProfile; 
use App\Models\SchoolStaffProfile;
use App\Models\StudentProfile;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // 1. Jika yang masuk adalah Siswa
        if ($user->role === 'Siswa') {
            $studentProfile = StudentProfile::where('user_id', $user->id)->first();

            if ($studentProfile) {
                $schoolId = $studentProfile->school_partner_id;
                $school = DB::table('school_partners')->where('id', $schoolId)->first();
                $schoolName = $school ? $school->nama_sekolah : 'sekolah';
                
                return redirect()->route('lms.student.dashboard', [
                    'role'       => $user->role,
                    'schoolName' => $schoolName,
                    'schoolId'   => $schoolId
                ]);
            }
        }

        // 2. Jika yang masuk adalah Guru
        if ($user->role === 'Guru') {
            $staffProfile = SchoolStaffProfile::where('user_id', $user->id)->first();
            
            if ($staffProfile) {
                $schoolId = $staffProfile->school_partner_id;
                $school = DB::table('school_partners')->where('id', $schoolId)->first();
                $schoolName = $school ? Str::slug($school->nama_sekolah) : 'sekolah';
                
                return redirect()->route('lms.teacher.view', [
                    'role'       => $user->role,
                    'schoolName' => $schoolName,
                    'schoolId'   => $schoolId
                ]);
            } else {
                abort(403, 'Profil staff Anda belum lengkap. Silakan hubungi admin.');
            }
        }
        
        // 3. Jika yang masuk adalah Kepala Sekolah
        if (in_array($user->role, ['Kepala Sekolah', 'Wakil Kepala Sekolah'])) {
            $profilKepsek = SchoolStaffProfile::where('user_id', $user->id)->first();
            
            if (!$profilKepsek) {
                abort(403, 'Profil Kepala Sekolah belum lengkap. Silakan hubungi admin.');
            }

            $schoolId = $profilKepsek->school_partner_id;
            $school = DB::table('school_partners')->where('id', $schoolId)->first();
            $schoolName = $school ? $school->nama_sekolah : 'sekolah';

            // Redirect ke rute Kepsek membawa parameter lengkap
            return redirect()->route('lms.headmaster.dashboard.view', [
                'role'       => $user->role,
                'schoolName' => $schoolName,
                'schoolId'   => $schoolId
            ]);
        }

        // 4. Jika yang masuk adalah Wakil Kesiswaan
        if ($user->role === 'Wakil Kesiswaan') {
            $staffProfile = SchoolStaffProfile::where('user_id', $user->id)->first();
            
            if ($staffProfile) {
                $schoolId = $staffProfile->school_partner_id;
                $school = DB::table('school_partners')->where('id', $schoolId)->first();
                $schoolName = $school ? $school->nama_sekolah : 'sekolah';
                
                return redirect()->route('lms.student-vice-principal.dashboard.view', [
                    'role'       => $user->role,
                    'schoolName' => $schoolName,
                    'schoolId'   => $schoolId
                ]);
            } else {
                abort(403, 'Profil staff Anda belum lengkap. Silakan hubungi admin.');
            }
        }

        // 5. Jika yang masuk adalah Orang Tua
        if ($user->role === 'Orang Tua') {
            $profilOrangTua = ParentProfile::where('user_id', $user->id)->first();
            
            if (!$profilOrangTua) {
                abort(403, 'Profil Orang Tua Anda belum terdaftar di sistem.');
            }

            $schoolId = $profilOrangTua->school_partner_id;
            $school = DB::table('school_partners')->where('id', $schoolId)->first();
            $schoolName = $school ? $school->nama_sekolah : 'sekolah';

            // Redirect otomatis ke rute Orang Tua
            return redirect()->route('lms.parent.dashboard', [
                'role'       => 'Orang Tua', 
                'schoolName' => $schoolName,
                'schoolId'   => $schoolId
            ]);
        }
        
        if ($user->role === 'Admin Sekolah') {

            $staffProfile = SchoolStaffProfile::where('user_id', $user->id)->first();
            
            if ($staffProfile) {
                $schoolId = $staffProfile->school_partner_id;
                $school = DB::table('school_partners')->where('id', $schoolId)->first();
                $schoolName = $school ? $school->nama_sekolah : 'sekolah';
                
                return redirect()->route('lms.schoolAdmin.dashboard.view', [
                    'role'       => $user->role,
                    'schoolName' => $schoolName,
                    'schoolId'   => $schoolId
                ]);
            } else {
                abort(403, 'Profil staff Anda belum lengkap. Silakan hubungi admin.');
            }
        }

        // 6. Jika yang masuk adalah Administrator atau Role Lainnya
        if ($user->role === 'Administrator') {
            return redirect()->route('lms.administrator.dashboard.view', [
                'role' => $user->role,
            ]);
        }

        // Default Fallback jika role tidak dikenali
        return view('beranda');
    }
}