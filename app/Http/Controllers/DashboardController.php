<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

            // 1. Ambil profil guru dari tabel school_staff
            $staffProfile = \App\Models\SchoolStaffProfile::where('user_id', $user->id)->first();
            
            // Atau kalau belum ada modelnya, pakai DB facade:
            // $staffProfile = \DB::table('school_staff')->where('user_id', $user->id)->first();

        if ($staffProfile) {
            $schoolId = $staffProfile->school_partner_id;
                
            // (Opsional) Ambil nama sekolah biar URL-nya cakep, ngga cuma tulisan 'sekolah'
            $school = DB::table('school_partners')->where('id', $schoolId)->first();
            $schoolName = $school->nama_sekolah;
        }

        // 2. Jika yang masuk adalah Guru (Perhatikan 'G' besar)
        if ($user->role === 'Guru') {

            // 2. Lempar ke rute Guru dengan parameter yang sudah valid dari database
            return redirect()->route('lms.teacher.view', [
                'role'       => $user->role,
                'schoolName' => $schoolName,
                'schoolId'   => $schoolId
            ]);
        }

        // 3. Jika yang masuk adalah Admin Sekolah
        if ($user->role === 'Admin Sekolah') {

            // Berdasarkan web.php kamu, rute ini memang tidak butuh parameter
            return redirect()->route('lms.schoolAdmin.dashboard.view', [
                'role'       => $user->role,
                'schoolName' => $schoolName,
                'schoolId'   => $schoolId
            ]);
        }
        
        // 3. Jika yang masuk adalah Lainnya
        return view('beranda');
    }
}