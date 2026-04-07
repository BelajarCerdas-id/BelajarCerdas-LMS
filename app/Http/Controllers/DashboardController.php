<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // 1. Jika yang masuk adalah Siswa
        if ($user->role === 'Siswa') {
            // Berdasarkan web.php kamu, rute ini memang tidak butuh parameter
            return redirect()->route('lms.student.dashboard');
        }

        // 2. Jika yang masuk adalah Guru (Perhatikan 'G' besar)
        if ($user->role === 'Guru') {
            // 1. Ambil profil guru dari tabel school_staff
            $staffProfile = \App\Models\SchoolStaffProfile::where('user_id', $user->id)->first();
            
            // Atau kalau belum ada modelnya, pakai DB facade:
            // $staffProfile = \DB::table('school_staff')->where('user_id', $user->id)->first();

            if ($staffProfile) {
                $schoolId = $staffProfile->school_partner_id;
                
                // (Opsional) Ambil nama sekolah biar URL-nya cakep, ngga cuma tulisan 'sekolah'
                $school = \DB::table('school_partners')->where('id', $schoolId)->first();
                $schoolName = $school->nama_sekolah;
            } else {
                // Jaga-jaga kalau ada guru yang belum punya profil staff
                abort(403, 'Profil staff Anda belum lengkap. Silakan hubungi admin.');
            }

            // 2. Lempar ke rute Guru dengan parameter yang sudah valid dari database
            return redirect()->route('lms.teacher.view', [
                'schoolName' => $schoolName,
                'schoolId'   => $schoolId
            ]);
        }
        
        // 3. Jika yang masuk adalah SuperAdmin / Lainnya
        return view('beranda');
    }
}