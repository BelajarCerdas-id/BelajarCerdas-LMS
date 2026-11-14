<?php

use App\Http\Controllers\LibraryController;
use Illuminate\Support\Facades\Route;

// =========================================================================
// 1. ADMINISTRATOR LIBRARY ROUTES
// =========================================================================
Route::prefix('administrator')->group(function () {

    // Main & Basic Management
    Route::get('/library', [LibraryController::class, 'administrator'])->name('Library.Administrator');
    Route::post('/library/store', [LibraryController::class, 'store'])->name('Library.Store');
    Route::post('/library/update/{id}', [LibraryController::class, 'update'])->name('Library.Update');
    Route::put('/library/update/{id}', [LibraryController::class, 'update']); // Fallback method
    Route::get('/library/row/{id}', [LibraryController::class, 'row'])->name('Library.Row');

    // PPT Management
    Route::post('/library/ppt/store', [LibraryController::class, 'storePpt'])->name('Ppt.Store');
    Route::put('/library/ppt/update/{id}', [LibraryController::class, 'updatePpt'])->name('Ppt.Update');
    Route::delete('/library/ppt/delete/{id}', [LibraryController::class, 'deletePpt'])->name('Ppt.Delete');

    // Chapter & Topik Management
    Route::post('/library/chapter/store', [LibraryController::class, 'storeChapter'])->name('Library.Chapter.Store');
    Route::post('/library/topik/store', [LibraryController::class, 'storeTopik'])->name('Library.Topik.Store');
    Route::put('/library/topik/update/{id}', [LibraryController::class, 'updateTopik'])->name('Library.Topik.Update');
    Route::get('/library/get-topik', [LibraryController::class, 'getTopikByMapel']);
    Route::get('/library/get-series/{topikId}', [LibraryController::class, 'getSeries']);

    Route::get('/topik-management', [LibraryController::class, 'topikManagement'])->name('Topik.Management');
    Route::delete('/library/topik/delete/{id}', [LibraryController::class, 'deleteTopik'])->name('Library.Topik.Delete');

    // ================= VIDEO CHUNK UPLOAD =================
    // Membuat session upload
    Route::post('/library/video/init', [LibraryController::class, 'initVideoUpload'])->name('Library.Video.Init');

    // Upload setiap chunk
    Route::post('/library/video/chunk', [LibraryController::class, 'uploadVideoChunk'])->name('Library.Video.Chunk');

    // Merge semua chunk menjadi satu file
    Route::post('/library/video/finish', [LibraryController::class, 'finishVideoUpload'])->name('Library.Video.Finish');

    // (Opsional) Cek progress upload
    Route::get('/library/video/status/{upload_id}', [LibraryController::class, 'uploadStatus'])->name('Library.Video.Status');

});

// Global delete (di luar prefix administrator karena URL aslinya tidak pakai /administrator)
Route::delete('/library/delete/{id}', [LibraryController::class, 'delete'])->name('Library.Delete');

// =========================================================
// STUDENT LIBRARY
// =========================================================
Route::get('/lms/student/library', [LibraryController::class, 'studentLibrary'])->name('Student.Library');

Route::get('/lms/student/library/ppt', [LibraryController::class, 'pptLibrary'])->name('Student.Library.Ppt');

Route::prefix('student/library/lks')->group(function () {

    Route::get('/', [LibraryController::class, 'lksLibrary'])->name('Student.Library.Lks');

    Route::get('/mapel', [LibraryController::class, 'getMapelByKelas'])->name('Student.Library.Lks.Mapel');

    Route::get('/{id}', [LibraryController::class, 'lksDetail'])->name('Student.Library.Lks.Detail');
});

Route::get('/lms/student/library/video', [LibraryController::class, 'videoLibrary'])->name('Student.Library.Video');

Route::get('/lms/student/library/read/{id}', [LibraryController::class, 'readBook'])->name('Student.Library.Read');

Route::post('/lms/student/library/submit', [LibraryController::class, 'submitTask'])->name('Student.Library.Submit');

Route::get('/lms/student/library/mapel/{mapel}', [LibraryController::class, 'mapelDetail'])->where('mapel', '[0-9]+')->name('Student.Library.Mapel');

Route::get('/get-bab/{mapel_id}', [LibraryController::class, 'getBab']);

// =========================================================
// TEACHER LIBRARY (AKSES SAMA)
// =========================================================
Route::get('/lms/teacher/library', [LibraryController::class, 'teacherLibrary'])->name('Teacher.Library');

Route::get('/lms/teacher/library/ppt', [LibraryController::class, 'pptLibrary'])->name('Teacher.Library.Ppt');

Route::get('/lms/teacher/library/lks', [LibraryController::class, 'lksLibrary'])->name('Teacher.Library.Lks');

Route::get('/lms/teacher/library/lks/{id}', [LibraryController::class, 'lksDetail'])->name('Teacher.Library.Lks.Detail');

Route::get('/lms/teacher/library/video', [LibraryController::class, 'videoLibrary'])->name('Teacher.Library.Video');

Route::get('/lms/teacher/library/read/{id}', [LibraryController::class, 'readBook'])->name('Teacher.Library.Read');

Route::get('/lms/teacher/library/mapel/{mapel}', [LibraryController::class, 'mapelDetail'])->where('mapel', '[0-9]+')->name('Teacher.Library.Mapel');
