<?php

use App\Http\Controllers\Parent\TkaTryout\ParentTkaTryoutController;
use App\Http\Controllers\ParentController;
use Illuminate\Support\Facades\Route;

// ROUTES ORANG TUA
// =========================================================
Route::get('/lms/{role}/{schoolName}/{schoolId}/parent/dashboard/student/{studentId?}', [ParentController::class, 'index'])->name('Lms.Parent.Dashboard.View');
Route::post('/lms/parent/polling/{id}/vote', [ParentController::class, 'submitPoll'])->name('Lms.Parent.Poll.Vote');
Route::get('/ortu/laporan-nilai/student/{studentId?}', [ParentController::class, 'laporanNilai'])->name('Ortu.LaporanNilai');
Route::get('/ortu/kehadiran/student/{studentId?}', [ParentController::class, 'kehadiran'])->name('Ortu.Kehadiran');
Route::get('/ortu/jadwal-pelajaran/student/{studentId?}', [ParentController::class, 'jadwalPelajaran'])->name('Ortu.JadwalPelajaran');
Route::get('/ortu/kalender-akademik/student/{studentId?}', [ParentController::class, 'kalenderAkademik'])->name('Ortu.KalenderAkademik');

// parent tka tryout monitoring (result & schedule)
// views
Route::get('/lms/{role}/{schoolName}/{schoolId}/parent/tka-tryout-monitoring/student/{studentId?}', [ParentTkaTryoutController::class, 'index'])->name('Lms.Parent.TkaTryoutMonitoring.View');

// paginate
Route::get('/lms/{role}/{schoolName}/{schoolId}/parent/tka-tryout-monitoring/student/{studentId?}/load-student-information', [ParentTkaTryoutController::class, 'loadStudentInformation'])->name('Lms.Parent.TkaTryoutStudentInformation.Load');
Route::get('/lms/{role}/{schoolName}/{schoolId}/parent/tka-tryout-monitoring/student/{studentId?}/load-schedule', [ParentTkaTryoutController::class, 'loadTkaTryoutSchedule'])->name('Lms.Parent.TkaTryoutStudentSchedule.Load');
Route::get('/lms/{role}/{schoolName}/{schoolId}/parent/tka-tryout-monitoring/student/{studentId?}/load-result', [ParentTkaTryoutController::class, 'loadTkaTryoutResult'])->name('Lms.Parent.TkaTryoutStudentResult.Load');
Route::get('/lms/{role}/{schoolName}/{schoolId}/parent/tka-tryout-monitoring/student/{studentId?}/load-insight', [ParentTkaTryoutController::class, 'loadTkaTryoutInsight'])->name('Lms.Parent.TkaTryoutStudentInsight.Load');
