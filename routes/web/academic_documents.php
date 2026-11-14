<?php

use App\Http\Controllers\AcademicDocumentController;
use App\Http\Controllers\AcademicDocumentDriveController;
use App\Http\Controllers\AcademicDocumentWordController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| LMS - REGISTRASI GURU
|--------------------------------------------------------------------------
|
| PREFIX:
| lms/{role}/{schoolName}/{schoolId}/registrasi-guru
|
| NAME:
| lms.schoolAdmin.registrasiGuru.*
|
|--------------------------------------------------------------------------
*/

Route::prefix(
    'lms/{role}/{schoolName}/{schoolId}/registrasi-guru'
)->name(
    'lms.schoolAdmin.registrasiGuru.'
)->group(function () {

    /*
    |--------------------------------------------------------------------------
    | ======================================================================
    | PROTA / PROSEM
    | ======================================================================
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | MENU PROTA / PROSEM
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/prota-prosem',
        [
            AcademicDocumentController::class,
            'protaProsem',
        ]
    )->name('ProtaProsem');

    /*
    |--------------------------------------------------------------------------
    | TAMPILAN EXCEL PROTA / PROSEM
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/prota-prosem/tampilan',
        [
            AcademicDocumentController::class,
            'tampilanProtaProsem',
        ]
    )->name('ProtaProsem.Tampilan');

    /*
    |--------------------------------------------------------------------------
    | UPLOAD PROTA / PROSEM
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/prota-prosem/upload',
        [
            AcademicDocumentController::class,
            'protaProsemUpload',
        ]
    )->name('ProtaProsem.Upload');

    /*
    |--------------------------------------------------------------------------
    | UPDATE CELL PROTA / PROSEM
    |--------------------------------------------------------------------------
    */

    Route::put(
        '/prota-prosem/cells/{cell}',
        [
            AcademicDocumentController::class,
            'updateCell',
        ]
    )
        ->whereNumber('cell')
        ->name('ProtaProsem.Cells.Update');

    /*
    |--------------------------------------------------------------------------
    | AUTOSAVE CELL PROTA / PROSEM
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/prota-prosem/cells/{cell}/autosave',
        [
            AcademicDocumentController::class,
            'updateCell',
        ]
    )
        ->whereNumber('cell')
        ->name('ProtaProsem.Cells.Autosave');

    /*
    |--------------------------------------------------------------------------
    | STORE COMMENT PROTA / PROSEM
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/prota-prosem/cells/{cell}/comments',
        [
            AcademicDocumentController::class,
            'storeProtaProsemComment',
        ]
    )
        ->whereNumber('cell')
        ->name('ProtaProsem.Comments.Store');

    /*
    |--------------------------------------------------------------------------
    | GET COMMENTS PROTA / PROSEM
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/prota-prosem/cells/{cell}/comments',
        [
            AcademicDocumentController::class,
            'protaProsemComments',
        ]
    )
        ->whereNumber('cell')
        ->name('ProtaProsem.Comments');

    /*
    |--------------------------------------------------------------------------
    | RESOLVE REVISION PROTA / PROSEM
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/prota-prosem/comments/{comment}/resolve',
        [
            AcademicDocumentController::class,
            'resolveRevision',
        ]
    )
        ->whereNumber('comment')
        ->name('ProtaProsem.Comments.Resolve');

    /*
    |--------------------------------------------------------------------------
    | DOWNLOAD PROTA / PROSEM
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/prota-prosem/{document}/download',
        [
            AcademicDocumentController::class,
            'protaProsemDownload',
        ]
    )
        ->whereNumber('document')
        ->name('ProtaProsem.Download');

    /*
    |--------------------------------------------------------------------------
    | DELETE PROTA / PROSEM
    |--------------------------------------------------------------------------
    */

    Route::delete(
        '/prota-prosem/{document}',
        [
            AcademicDocumentController::class,
            'protaProsemDestroy',
        ]
    )
        ->whereNumber('document')
        ->name('ProtaProsem.Delete');

    /*
    |--------------------------------------------------------------------------
    | SHOW PROTA / PROSEM
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/prota-prosem/{document}',
        [
            AcademicDocumentController::class,
            'showProtaProsem',
        ]
    )
        ->whereNumber('document')
        ->name('ProtaProsem.Show');

    /*
    |--------------------------------------------------------------------------
    | ======================================================================
    | BAGAN ANALISIS / ANALISIS CP HINGGA ATP
    | ======================================================================
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | MENU BAGAN ANALISIS
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/bagan-analisis',
        [
            AcademicDocumentController::class,
            'baganAnalisis',
        ]
    )->name('BaganAnalisis');

    /*
    |--------------------------------------------------------------------------
    | TAMPILAN BAGAN ANALISIS
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/bagan-analisis/tampilan',
        [
            AcademicDocumentController::class,
            'tampilanBaganAnalisis',
        ]
    )->name('BaganAnalisis.Tampilan');

    /*
    |--------------------------------------------------------------------------
    | UPLOAD BAGAN ANALISIS
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/bagan-analisis/upload',
        [
            AcademicDocumentController::class,
            'baganAnalisisUpload',
        ]
    )->name('BaganAnalisis.Upload');

    /*
    |--------------------------------------------------------------------------
    | DOWNLOAD BAGAN ANALISIS
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/bagan-analisis/{document}/download',
        [
            AcademicDocumentController::class,
            'baganAnalisisDownload',
        ]
    )
        ->whereNumber('document')
        ->name('BaganAnalisis.Download');

    /*
    |--------------------------------------------------------------------------
    | UPDATE CELL BAGAN ANALISIS
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/bagan-analisis/cells/{cell}/autosave',
        [
            AcademicDocumentController::class,
            'updateBaganAnalisisCell',
        ]
    )
        ->whereNumber('cell')
        ->name('BaganAnalisis.Cells.Autosave');

    /*
    |--------------------------------------------------------------------------
    | LEGACY PUT ENDPOINT
    |--------------------------------------------------------------------------
    */

    Route::put(
        '/bagan-analisis/cells/{cell}',
        [
            AcademicDocumentController::class,
            'updateBaganAnalisisCell',
        ]
    )
        ->whereNumber('cell')
        ->name('BaganAnalisis.Cells.Update');

    /*
    |--------------------------------------------------------------------------
    | STORE COMMENT BAGAN ANALISIS
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/bagan-analisis/cells/{cell}/comments',
        [
            AcademicDocumentController::class,
            'storeBaganAnalisisComment',
        ]
    )
        ->whereNumber('cell')
        ->name('BaganAnalisis.Comments.Store');

    /*
    |--------------------------------------------------------------------------
    | GET COMMENTS BAGAN ANALISIS
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/bagan-analisis/cells/{cell}/comments',
        [
            AcademicDocumentController::class,
            'baganAnalisisComments',
        ]
    )
        ->whereNumber('cell')
        ->name('BaganAnalisis.Comments');

    /*
    |--------------------------------------------------------------------------
    | RESOLVE COMMENT / REVISI
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/bagan-analisis/comments/{comment}/resolve',
        [
            AcademicDocumentController::class,
            'resolveBaganAnalisisRevision',
        ]
    )
        ->whereNumber('comment')
        ->name('BaganAnalisis.Comments.Resolve');

    /*
    |--------------------------------------------------------------------------
    | DELETE BAGAN ANALISIS
    |--------------------------------------------------------------------------
    */

    Route::delete(
        '/bagan-analisis/{document}',
        [
            AcademicDocumentController::class,
            'baganAnalisisDestroy',
        ]
    )
        ->whereNumber('document')
        ->name('BaganAnalisis.Delete');

    /*
    |--------------------------------------------------------------------------
    | SHOW BAGAN ANALISIS
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/bagan-analisis/{document}',
        [
            AcademicDocumentController::class,
            'showBaganAnalisis',
        ]
    )
        ->whereNumber('document')
        ->name('BaganAnalisis.Show');

    /*
    |--------------------------------------------------------------------------
    | ======================================================================
    | ANALISIS CP - TP - ATP
    | ======================================================================
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | MENU ANALISIS
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/analisis',
        [
            AcademicDocumentController::class,
            'analisis',
        ]
    )->name('Analisis');

    /*
    |--------------------------------------------------------------------------
    | TAMPILAN ANALISIS
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/analisis/tampilan',
        [
            AcademicDocumentController::class,
            'tampilanAnalisis',
        ]
    )->name('Analisis.Tampilan');

    /*
    |--------------------------------------------------------------------------
    | UPLOAD ANALISIS
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/analisis/upload',
        [
            AcademicDocumentController::class,
            'analisisUpload',
        ]
    )->name('Analisis.Upload');

    /*
    |--------------------------------------------------------------------------
    | DOWNLOAD ANALISIS
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/analisis/{document}/download',
        [
            AcademicDocumentController::class,
            'analisisDownload',
        ]
    )
        ->whereNumber('document')
        ->name('Analisis.Download');

    /*
    |--------------------------------------------------------------------------
    | UPDATE CELL ANALISIS
    |--------------------------------------------------------------------------
    */

    Route::put(
        '/analisis/cells/{cell}',
        [
            AcademicDocumentController::class,
            'updateAnalisisCell',
        ]
    )
        ->whereNumber('cell')
        ->name('Analisis.Cells.Update');

    /*
    |--------------------------------------------------------------------------
    | STORE COMMENT ANALISIS
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/analisis/cells/{cell}/comments',
        [
            AcademicDocumentController::class,
            'storeAnalisisComment',
        ]
    )
        ->whereNumber('cell')
        ->name('Analisis.Comments.Store');

    /*
    |--------------------------------------------------------------------------
    | GET COMMENTS ANALISIS
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/analisis/cells/{cell}/comments',
        [
            AcademicDocumentController::class,
            'analisisComments',
        ]
    )
        ->whereNumber('cell')
        ->name('Analisis.Comments');

    /*
    |--------------------------------------------------------------------------
    | RESOLVE REVISION ANALISIS
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/analisis/comments/{comment}/resolve',
        [
            AcademicDocumentController::class,
            'resolveAnalisisRevision',
        ]
    )
        ->whereNumber('comment')
        ->name('Analisis.Comments.Resolve');

    /*
    |--------------------------------------------------------------------------
    | DELETE ANALISIS
    |--------------------------------------------------------------------------
    */

    Route::delete(
        '/analisis/{document}',
        [
            AcademicDocumentController::class,
            'analisisDestroy',
        ]
    )
        ->whereNumber('document')
        ->name('Analisis.Delete');

    /*
    |--------------------------------------------------------------------------
    | SHOW ANALISIS
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/analisis/{document}',
        [
            AcademicDocumentController::class,
            'showAnalisis',
        ]
    )
        ->whereNumber('document')
        ->name('Analisis.Show');

    /*
    |--------------------------------------------------------------------------
    | ======================================================================
    | RPPM
    | ======================================================================
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | MENU RPPM
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/rppm',
        function (
            string $role,
            string $schoolName,
            int $schoolId
        ) {
            return view(
                'features.lms.school-admin.registrasi-guru.RPPM',
                [
                    'role' => $role,
                    'schoolName' => $schoolName,
                    'schoolId' => $schoolId,
                ]
            );
        }
    )->name('Rppm');

    /*
    |--------------------------------------------------------------------------
    | TAMPILAN RPPM
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/rppm/tampilan',
        [
            AcademicDocumentWordController::class,
            'rppm',
        ]
    )->name('Rppm.Tampilan');

    /*
    |--------------------------------------------------------------------------
    | UPLOAD RPPM WORD
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/rppm-word/upload',
        [
            AcademicDocumentWordController::class,
            'upload',
        ]
    )->name('Rppm.Word.Upload');

    /*
    |--------------------------------------------------------------------------
    | FILE RPPM WORD
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/rppm-word/{documentId}/file',
        [
            AcademicDocumentWordController::class,
            'file',
        ]
    )
        ->whereNumber('documentId')
        ->name('Rppm.Word.File');

    /*
    |--------------------------------------------------------------------------
    | SAVE RPPM WORD
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/rppm-word/{documentId}/save',
        [
            AcademicDocumentWordController::class,
            'save',
        ]
    )
        ->whereNumber('documentId')
        ->name('Rppm.Word.Save');

    /*
    |--------------------------------------------------------------------------
    | DOWNLOAD RPPM WORD
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/rppm-word/{documentId}/download',
        [
            AcademicDocumentWordController::class,
            'download',
        ]
    )
        ->whereNumber('documentId')
        ->name('Rppm.Word.Download');

    /*
    |--------------------------------------------------------------------------
    | STORE COMMENT RPPM
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/rppm-word/{documentId}/comments',
        [
            AcademicDocumentWordController::class,
            'storeComment',
        ]
    )
        ->whereNumber('documentId')
        ->name('Rppm.Word.Comments.Store');

    /*
    |--------------------------------------------------------------------------
    | REPLY RPPM
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/rppm-word/comments/{commentId}/reply',
        [
            AcademicDocumentWordController::class,
            'replyComment',
        ]
    )
        ->whereNumber('commentId')
        ->name('Rppm.Word.Comments.Reply');

    /*
    |--------------------------------------------------------------------------
    | RESOLVE RPPM
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/rppm-word/comments/{commentId}/resolve',
        [
            AcademicDocumentWordController::class,
            'resolveComment',
        ]
    )
        ->whereNumber('commentId')
        ->name('Rppm.Word.Comments.Resolve');

    /*
    |--------------------------------------------------------------------------
    | DELETE COMMENT RPPM
    |--------------------------------------------------------------------------
    */

    Route::delete(
        '/rppm-word/comments/{commentId}',
        [
            AcademicDocumentWordController::class,
            'destroyComment',
        ]
    )
        ->whereNumber('commentId')
        ->name('Rppm.Word.Comments.Destroy');

    /*
    |--------------------------------------------------------------------------
    | DELETE RPPM
    |--------------------------------------------------------------------------
    */

    Route::delete(
        '/rppm-word/{documentId}',
        [
            AcademicDocumentWordController::class,
            'destroy',
        ]
    )
        ->whereNumber('documentId')
        ->name('Rppm.Word.Destroy');

    /*
    |--------------------------------------------------------------------------
    | EDIT RPPM
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/rppm-word/{documentId}',
        [
            AcademicDocumentWordController::class,
            'edit',
        ]
    )
        ->whereNumber('documentId')
        ->name('Rppm.Word.Edit');

    /*
    |--------------------------------------------------------------------------
    | ======================================================================
    | ANALISIS RPPM
    | ======================================================================
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | MENU ANALISIS RPPM
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/analisis-rppm',
        function (
            string $role,
            string $schoolName,
            int $schoolId
        ) {
            return view(
                'features.lms.school-admin.registrasi-guru.analisis-rppm',
                [
                    'role' => $role,
                    'schoolName' => $schoolName,
                    'schoolId' => $schoolId,
                ]
            );
        }
    )->name('AnalisisRppm');

    /*
    |--------------------------------------------------------------------------
    | TAMPILAN ANALISIS RPPM
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/analisis-rppm/tampilan',
        [
            AcademicDocumentWordController::class,
            'analisisRppm',
        ]
    )->name('AnalisisRppm.Tampilan');

    /*
    |--------------------------------------------------------------------------
    | UPLOAD ANALISIS RPPM
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/analisis-rppm-word/upload',
        [
            AcademicDocumentWordController::class,
            'uploadAnalisisRppm',
        ]
    )->name('AnalisisRppm.Word.Upload');

    /*
    |--------------------------------------------------------------------------
    | FILE ANALISIS RPPM
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/analisis-rppm-word/{documentId}/file',
        [
            AcademicDocumentWordController::class,
            'fileAnalisisRppm',
        ]
    )
        ->whereNumber('documentId')
        ->name('AnalisisRppm.Word.File');

    /*
    |--------------------------------------------------------------------------
    | SAVE ANALISIS RPPM
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/analisis-rppm-word/{documentId}/save',
        [
            AcademicDocumentWordController::class,
            'saveAnalisisRppm',
        ]
    )
        ->whereNumber('documentId')
        ->name('AnalisisRppm.Word.Save');

    /*
    |--------------------------------------------------------------------------
    | DOWNLOAD ANALISIS RPPM
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/analisis-rppm-word/{documentId}/download',
        [
            AcademicDocumentWordController::class,
            'downloadAnalisisRppm',
        ]
    )
        ->whereNumber('documentId')
        ->name('AnalisisRppm.Word.Download');

    /*
    |--------------------------------------------------------------------------
    | STORE COMMENT ANALISIS RPPM
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/analisis-rppm-word/{documentId}/comments',
        [
            AcademicDocumentWordController::class,
            'storeAnalisisRppmComment',
        ]
    )
        ->whereNumber('documentId')
        ->name('AnalisisRppm.Word.Comments.Store');

    /*
    |--------------------------------------------------------------------------
    | REPLY ANALISIS RPPM
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/analisis-rppm-word/comments/{commentId}/reply',
        [
            AcademicDocumentWordController::class,
            'replyAnalisisRppmComment',
        ]
    )
        ->whereNumber('commentId')
        ->name('AnalisisRppm.Word.Comments.Reply');

    /*
    |--------------------------------------------------------------------------
    | RESOLVE ANALISIS RPPM
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/analisis-rppm-word/comments/{commentId}/resolve',
        [
            AcademicDocumentWordController::class,
            'resolveAnalisisRppmComment',
        ]
    )
        ->whereNumber('commentId')
        ->name('AnalisisRppm.Word.Comments.Resolve');

    /*
    |--------------------------------------------------------------------------
    | DELETE COMMENT ANALISIS RPPM
    |--------------------------------------------------------------------------
    */

    Route::delete(
        '/analisis-rppm-word/comments/{commentId}',
        [
            AcademicDocumentWordController::class,
            'destroyAnalisisRppmComment',
        ]
    )
        ->whereNumber('commentId')
        ->name('AnalisisRppm.Word.Comments.Destroy');

    /*
    |--------------------------------------------------------------------------
    | DELETE ANALISIS RPPM
    |--------------------------------------------------------------------------
    */

    Route::delete(
        '/analisis-rppm-word/{documentId}',
        [
            AcademicDocumentWordController::class,
            'destroyAnalisisRppm',
        ]
    )
        ->whereNumber('documentId')
        ->name('AnalisisRppm.Word.Destroy');

    /*
    |--------------------------------------------------------------------------
    | EDIT ANALISIS RPPM
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/analisis-rppm-word/{documentId}',
        [
            AcademicDocumentWordController::class,
            'editAnalisisRppm',
        ]
    )
        ->whereNumber('documentId')
        ->name('AnalisisRppm.Word.Edit');

    /*
    |--------------------------------------------------------------------------
    | ======================================================================
    | REFLEKSI GURU
    | ======================================================================
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | MENU REFLEKSI GURU
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/refleksi-guru',
        function (
            string $role,
            string $schoolName,
            int $schoolId
        ) {
            return view(
                'features.lms.school-admin.registrasi-guru.refleksi-guru',
                [
                    'role' => $role,
                    'schoolName' => $schoolName,
                    'schoolId' => $schoolId,
                ]
            );
        }
    )->name('RefleksiGuru');

    /*
    |--------------------------------------------------------------------------
    | TAMPILAN REFLEKSI GURU
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/refleksi-guru/tampilan',
        [
            AcademicDocumentWordController::class,
            'refleksiGuru',
        ]
    )->name('RefleksiGuru.Tampilan');

    /*
    |--------------------------------------------------------------------------
    | UPLOAD REFLEKSI
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/refleksi-guru/upload',
        [
            AcademicDocumentWordController::class,
            'uploadRefleksi',
        ]
    )->name('RefleksiGuru.Upload');

    /*
    |--------------------------------------------------------------------------
    | FILE REFLEKSI
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/refleksi-guru/{documentId}/file',
        [
            AcademicDocumentWordController::class,
            'fileRefleksi',
        ]
    )
        ->whereNumber('documentId')
        ->name('RefleksiGuru.File');

    /*
    |--------------------------------------------------------------------------
    | SAVE REFLEKSI
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/refleksi-guru/{documentId}/save',
        [
            AcademicDocumentWordController::class,
            'saveRefleksi',
        ]
    )
        ->whereNumber('documentId')
        ->name('RefleksiGuru.Save');

    /*
    |--------------------------------------------------------------------------
    | DOWNLOAD REFLEKSI
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/refleksi-guru/{documentId}/download',
        [
            AcademicDocumentWordController::class,
            'downloadRefleksi',
        ]
    )
        ->whereNumber('documentId')
        ->name('RefleksiGuru.Download');

    /*
    |--------------------------------------------------------------------------
    | STORE COMMENT REFLEKSI
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/refleksi-guru/{documentId}/comments',
        [
            AcademicDocumentWordController::class,
            'storeRefleksiComment',
        ]
    )
        ->whereNumber('documentId')
        ->name('RefleksiGuru.Comments.Store');

    /*
    |--------------------------------------------------------------------------
    | REPLY REFLEKSI
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/refleksi-guru/comments/{commentId}/reply',
        [
            AcademicDocumentWordController::class,
            'replyRefleksiComment',
        ]
    )
        ->whereNumber('commentId')
        ->name('RefleksiGuru.Comments.Reply');

    /*
    |--------------------------------------------------------------------------
    | RESOLVE REFLEKSI
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/refleksi-guru/comments/{commentId}/resolve',
        [
            AcademicDocumentWordController::class,
            'resolveRefleksiComment',
        ]
    )
        ->whereNumber('commentId')
        ->name('RefleksiGuru.Comments.Resolve');

    /*
    |--------------------------------------------------------------------------
    | DELETE COMMENT REFLEKSI
    |--------------------------------------------------------------------------
    */

    Route::delete(
        '/refleksi-guru/comments/{commentId}',
        [
            AcademicDocumentWordController::class,
            'destroyRefleksiComment',
        ]
    )
        ->whereNumber('commentId')
        ->name('RefleksiGuru.Comments.Destroy');

    /*
    |--------------------------------------------------------------------------
    | DELETE REFLEKSI
    |--------------------------------------------------------------------------
    */

    Route::delete(
        '/refleksi-guru/{documentId}',
        [
            AcademicDocumentWordController::class,
            'destroyRefleksi',
        ]
    )
        ->whereNumber('documentId')
        ->name('RefleksiGuru.Destroy');

    /*
    |--------------------------------------------------------------------------
    | EDIT REFLEKSI
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/refleksi-guru/{documentId}',
        [
            AcademicDocumentWordController::class,
            'editRefleksi',
        ]
    )
        ->whereNumber('documentId')
        ->name('RefleksiGuru.Edit');

    /*
    |--------------------------------------------------------------------------
    | ======================================================================
    | ACADEMIC DOCUMENT SHARING
    | ======================================================================
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | SAVE ARCHIVE - EXCEL
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/academic-document/{type}/{document}/save-archive',
        [
            AcademicDocumentController::class,
            'saveArchive',
        ]
    )
        ->whereIn('type', [
            'analisis',
            'prota',
            'prota-prosem',
        ])
        ->whereNumber('document')
        ->name('AcademicDocument.SaveArchive');

    /*
    |--------------------------------------------------------------------------
    | BROWSE ACADEMIC DOCUMENT - EXCEL
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/academic-drive/browse/{type}',
        [
            AcademicDocumentController::class,
            'browseAcademicDocuments',
        ]
    )
        ->whereIn('type', [
            'analisis',
            'prota',
            'prota-prosem',
        ])
        ->name('AcademicDrive.Browse');

    /*
    |--------------------------------------------------------------------------
    | VIEW SHARED ACADEMIC DOCUMENT - EXCEL
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/academic-document/{type}/{document}/view',
        [
            AcademicDocumentController::class,
            'viewSharedAcademicDocument',
        ]
    )
        ->whereIn('type', [
            'analisis',
            'prota',
            'prota-prosem',
        ])
        ->whereNumber('document')
        ->name('AcademicDocument.SharedView');

    /*
    |--------------------------------------------------------------------------
    | SAVE ARCHIVE - WORD
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/academic-word/{type}/{documentId}/save-archive',
        [
            AcademicDocumentWordController::class,
            'saveArchive',
        ]
    )
        ->whereIn('type', [
            'rppm',
            'refleksi-guru',
        ])
        ->whereNumber('documentId')
        ->name('AcademicWord.SaveArchive');

    /*
    |--------------------------------------------------------------------------
    | BROWSE ACADEMIC DOCUMENT - WORD
    |--------------------------------------------------------------------------
    | HANYA SATU ROUTE.
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/academic-word/{type}/browse',
        [
            AcademicDocumentWordController::class,
            'browseAcademicDocuments',
        ]
    )
        ->whereIn('type', [
            'rppm',
            'refleksi-guru',
        ])
        ->name('AcademicWord.Browse');

    /*
    |--------------------------------------------------------------------------
    | VIEW SHARED DOCUMENT - WORD
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/academic-word/{type}/{documentId}/view',
        [
            AcademicDocumentWordController::class,
            'viewSharedDocument',
        ]
    )
        ->whereIn('type', [
            'rppm',
            'refleksi-guru',
        ])
        ->whereNumber('documentId')
        ->name('AcademicWord.SharedView');

});

/*
|--------------------------------------------------------------------------
| ==========================================================================
| ACADEMIC DRIVE
| ==========================================================================
|
| URL:
|
| /lms/{role}/{schoolName}/{schoolId}/registrasi-guru/academic-drive
|
|--------------------------------------------------------------------------
*/

Route::prefix(
    'lms/{role}/{schoolName}/{schoolId}/registrasi-guru/academic-drive'
)
    ->middleware('auth')
    ->name('Lms.SchoolAdmin.RegistrasiGuru.AcademicDrive.')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | DASHBOARD / ROOT ACADEMIC DRIVE
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/',
            [
                AcademicDocumentDriveController::class,
                'index',
            ]
        )->name('Index');

        /*
        |--------------------------------------------------------------------------
        | FILES
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/files/{subjectId}/{teacherId}/{folder}',
            [
                AcademicDocumentDriveController::class,
                'files',
            ]
        )
            ->whereNumber('subjectId')
            ->whereNumber('teacherId')
            ->name('Files');

        /*
        |--------------------------------------------------------------------------
        | DOWNLOAD SINGLE FILE
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/download/{documentId}',
            [
                AcademicDocumentDriveController::class,
                'download',
            ]
        )
            ->whereNumber('documentId')
            ->name('Download');

        /*
        |--------------------------------------------------------------------------
        | DOWNLOAD SELECTED FILES
        |--------------------------------------------------------------------------
        */

        Route::post(
            '/download-selected',
            [
                AcademicDocumentDriveController::class,
                'downloadSelected',
            ]
        )->name('DownloadSelected');

        /*
        |--------------------------------------------------------------------------
        | DELETE SELECTED FILES
        |--------------------------------------------------------------------------
        */

        Route::post(
            '/delete-selected',
            [
                AcademicDocumentDriveController::class,
                'destroySelected',
            ]
        )->name('DeleteSelected');
    });
