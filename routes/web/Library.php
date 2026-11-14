<?php

use App\Http\Controllers\LibraryController;
use Illuminate\Support\Facades\Route;

// =========================================================================
// 1. ADMINISTRATOR LIBRARY ROUTES
// =========================================================================
Route::prefix('administrator')->group(function () {

    // Main & Basic Management
    Route::get('/library', [LibraryController::class, 'administrator'])->name('library.administrator');
    Route::post('/library/store', [LibraryController::class, 'store'])->name('library.store');
    Route::post('/library/update/{id}', [LibraryController::class, 'update'])->name('library.update');
    Route::put('/library/update/{id}', [LibraryController::class, 'update']); // Fallback method
    Route::get('/library/row/{id}', [LibraryController::class, 'row'])->name('library.row');

    // PPT Management
    Route::post('/library/ppt/store', [LibraryController::class, 'storePpt'])->name('ppt.store');
    Route::put('/library/ppt/update/{id}', [LibraryController::class, 'updatePpt'])->name('ppt.update');
    Route::delete('/library/ppt/delete/{id}', [LibraryController::class, 'deletePpt'])->name('ppt.delete');

    // Chapter & Topik Management
    Route::post('/library/chapter/store', [LibraryController::class, 'storeChapter'])->name('library.chapter.store');
    Route::post('/library/topik/store', [LibraryController::class, 'storeTopik'])->name('library.topik.store');
    Route::put('/library/topik/update/{id}', [LibraryController::class, 'updateTopik'])->name('library.topik.update');
    Route::get('/library/get-topik', [LibraryController::class, 'getTopikByMapel']);
    Route::get('/library/get-series/{topikId}', [LibraryController::class, 'getSeries']);

    Route::get('/topik-management', [LibraryController::class, 'topikManagement'])->name('topik.management');
    Route::delete('/library/topik/delete/{id}', [LibraryController::class, 'deleteTopik'])->name('library.topik.delete');

    // ================= VIDEO CHUNK UPLOAD =================
    // Membuat session upload
    Route::post('/library/video/init', [LibraryController::class, 'initVideoUpload'])->name('library.video.init');

    // Upload setiap chunk
    Route::post('/library/video/chunk', [LibraryController::class, 'uploadVideoChunk'])->name('library.video.chunk');

    // Merge semua chunk menjadi satu file
    Route::post('/library/video/finish', [LibraryController::class, 'finishVideoUpload'])->name('library.video.finish');

    // (Opsional) Cek progress upload
    Route::get('/library/video/status/{upload_id}', [LibraryController::class, 'uploadStatus'])->name('library.video.status');

});

// Global delete (di luar prefix administrator karena URL aslinya tidak pakai /administrator)
Route::delete('/library/delete/{id}', [LibraryController::class, 'delete'])->name('library.delete');

// =========================================================
// STUDENT LIBRARY
// =========================================================
Route::get('/lms/student/library', [LibraryController::class, 'studentLibrary'])->name('student.library');

Route::get('/lms/student/library/ppt', [LibraryController::class, 'pptLibrary'])->name('student.library.ppt');

Route::prefix('student/library/lks')->group(function () {

    Route::get('/', [LibraryController::class, 'lksLibrary'])->name('student.library.lks');

    Route::get('/mapel', [LibraryController::class, 'getMapelByKelas'])->name('student.library.lks.mapel');

    Route::get('/{id}', [LibraryController::class, 'lksDetail'])->name('student.library.lks.detail');
});

Route::get('/lms/student/library/video', [LibraryController::class, 'videoLibrary'])->name('student.library.video');

Route::get('/lms/student/library/read/{id}', [LibraryController::class, 'readBook'])->name('student.library.read');

Route::post('/lms/student/library/submit', [LibraryController::class, 'submitTask'])->name('student.library.submit');

Route::get('/lms/student/library/mapel/{mapel}', [LibraryController::class, 'mapelDetail'])->where('mapel', '[0-9]+')->name('student.library.mapel');

Route::get('/get-bab/{mapel_id}', [LibraryController::class, 'getBab']);

// =========================================================
// TEACHER LIBRARY (AKSES SAMA)
// =========================================================
Route::get('/lms/teacher/library', [LibraryController::class, 'teacherLibrary'])->name('teacher.library');

Route::get('/lms/teacher/library/ppt', [LibraryController::class, 'pptLibrary'])->name('teacher.library.ppt');

Route::get('/lms/teacher/library/lks', [LibraryController::class, 'lksLibrary'])->name('teacher.library.lks');

Route::get('/lms/teacher/library/lks/{id}', [LibraryController::class, 'lksDetail'])->name('teacher.library.lks.detail');

Route::get('/lms/teacher/library/video', [LibraryController::class, 'videoLibrary'])->name('teacher.library.video');

Route::get('/lms/teacher/library/read/{id}', [LibraryController::class, 'readBook'])->name('teacher.library.read');

Route::get('/lms/teacher/library/mapel/{mapel}', [LibraryController::class, 'mapelDetail'])->where('mapel', '[0-9]+')->name('teacher.library.mapel');
