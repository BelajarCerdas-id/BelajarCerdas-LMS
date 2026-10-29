<?php

use App\Http\Controllers\Parent\TkaTryout\ParentTkaTryoutController;
use App\Http\Controllers\ParentController;
use Illuminate\Support\Facades\Route;

// ROUTES ORANG TUA
// =========================================================
Route::get('/lms/{role}/{schoolName}/{schoolId}/parent/dashboard/student/{studentId?}', [ParentController::class, 'index'])->name('lms.parent.dashboard.view');
Route::post('/lms/parent/polling/{id}/vote', [ParentController::class, 'submitPoll'])->name('lms.parent.poll.vote');
Route::get('/ortu/laporan-nilai/student/{studentId?}', [ParentController::class, 'laporanNilai'])->name('ortu.laporan-nilai');
Route::get('/ortu/kehadiran/student/{studentId?}', [ParentController::class, 'kehadiran'])->name('ortu.kehadiran');
Route::get('/ortu/jadwal-pelajaran/student/{studentId?}', [ParentController::class, 'jadwalPelajaran'])->name('ortu.jadwal-pelajaran');
Route::get('/ortu/kalender-akademik/student/{studentId?}', [ParentController::class, 'kalenderAkademik'])->name('ortu.kalender-akademik');

// parent tka tryout monitoring (result & schedule)
// views
Route::get('/lms/{role}/{schoolName}/{schoolId}/parent/tka-tryout-monitoring/student/{studentId?}', [ParentTkaTryoutController::class, 'index'])->name('lms.parent.tka-tryout-monitoring.view');

// paginate
Route::get('/lms/{role}/{schoolName}/{schoolId}/parent/tka-tryout-monitoring/student/{studentId?}/load-student-information', [ParentTkaTryoutController::class, 'loadStudentInformation'])->name('lms.parent.tka-tryout-student-information.load');
Route::get('/lms/{role}/{schoolName}/{schoolId}/parent/tka-tryout-monitoring/student/{studentId?}/load-schedule', [ParentTkaTryoutController::class, 'loadTkaTryoutSchedule'])->name('lms.parent.tka-tryout-student-schedule.load');
Route::get('/lms/{role}/{schoolName}/{schoolId}/parent/tka-tryout-monitoring/student/{studentId?}/load-result', [ParentTkaTryoutController::class, 'loadTkaTryoutResult'])->name('lms.parent.tka-tryout-student-result.load');
Route::get('/lms/{role}/{schoolName}/{schoolId}/parent/tka-tryout-monitoring/student/{studentId?}/load-insight', [ParentTkaTryoutController::class, 'loadTkaTryoutInsight'])->name('lms.parent.tka-tryout-student-insight.load');
