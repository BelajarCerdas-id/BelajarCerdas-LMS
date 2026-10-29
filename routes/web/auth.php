<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MasterAcademicController;
use App\Http\Controllers\ServiceRuleController;
use App\Http\Middleware\RedirectIfAuthenticated;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// ROUTE FALLBACK
Route::fallback(function () {

    if (Auth::check()) {

        $role = Auth::user()->role;

        // jika role nya untuk office, maka gunakan route ini
        if (in_array($role, ['Administrator', 'Finance'])) {
            return redirect()->route('lms.office.dashboard.view', [
                'role' => $role,
            ]);
        } else {
            return redirect()->route('beranda', [
                'role' => $role,
            ]);
        }
    }

    return redirect()->route('login');
});

Route::get('/', fn () => redirect('/login'));

// middleware redirect if authenticated
Route::middleware([RedirectIfAuthenticated::class])->group(function () {
    Route::get('/login', [AuthController::class, 'loginView'])->name('login');
});

// routes auth login & logout
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ROUTES DROPDOWN KURIKULUM, KELAS, MAPEL, BAB, SUB BAB
Route::get('/kelas/{id}', [MasterAcademicController::class, 'getKelas']);

// service for school partner & non school partner
Route::get('/kurikulum/{curriculumId}/service', [MasterAcademicController::class, 'getServiceByKurikulum']);
Route::get('/kurikulum/{curriculumId}/{schoolId}/service', [MasterAcademicController::class, 'getServiceByKurikulum']);

// kelas for school partner & non school partner
Route::get('/kurikulum/{curriculumId}/kelas', [MasterAcademicController::class, 'getKelasByKurikulum']);
Route::get('/kurikulum/{curriculumId}/{schoolId}/kelas', [MasterAcademicController::class, 'getKelasByKurikulum']);

// route dependent dropdown mapel by kelas non school partner & school partner
Route::get('/kelas/{kelasId}/mapel', [MasterAcademicController::class, 'getMapelByKelas']);
Route::get('/kelas/{kelasId}/{schoolId}/mapel', [MasterAcademicController::class, 'getMapelByKelas']);

// route dependent dropdown rombel kelas by kelas
Route::get('/kelas/{kelasId}/rombel-kelas/{schoolId}', [MasterAcademicController::class, 'getRombelByKelas']);

Route::get('/mapel/{mapelId}/bab', [MasterAcademicController::class, 'getBabByMapel']);
Route::get('/bab/{babId}/sub-bab', [MasterAcademicController::class, 'getSubBabByBab']);

Route::get('/service/{service}/rules', [ServiceRuleController::class, 'index']);
