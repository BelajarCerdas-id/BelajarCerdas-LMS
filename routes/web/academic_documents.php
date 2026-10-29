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
    )->name('prota-prosem');

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
    )->name('prota-prosem.tampilan');

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
    )->name('prota-prosem.upload');

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
        ->name('prota-prosem.cells.update');

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
        ->name('prota-prosem.cells.autosave');

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
        ->name('prota-prosem.comments.store');

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
        ->name('prota-prosem.comments');

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
        ->name('prota-prosem.comments.resolve');

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
        ->name('prota-prosem.download');

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
        ->name('prota-prosem.delete');

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
        ->name('prota-prosem.show');

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
    )->name('baganAnalisis');

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
    )->name('baganAnalisis.tampilan');

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
    )->name('baganAnalisis.upload');

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
        ->name('baganAnalisis.download');

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
        ->name('baganAnalisis.cells.autosave');

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
        ->name('baganAnalisis.cells.update');

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
        ->name('baganAnalisis.comments.store');

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
        ->name('baganAnalisis.comments');

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
        ->name('baganAnalisis.comments.resolve');

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
        ->name('baganAnalisis.delete');

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
        ->name('baganAnalisis.show');

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
    )->name('analisis');

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
    )->name('analisis.tampilan');

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
    )->name('analisis.upload');

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
        ->name('analisis.download');

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
        ->name('analisis.cells.update');

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
        ->name('analisis.comments.store');

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
        ->name('analisis.comments');

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
        ->name('analisis.comments.resolve');

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
        ->name('analisis.delete');

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
        ->name('analisis.show');

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
    )->name('rppm');

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
    )->name('rppm.tampilan');

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
    )->name('rppm.word.upload');

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
        ->name('rppm.word.file');

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
        ->name('rppm.word.save');

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
        ->name('rppm.word.download');

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
        ->name('rppm.word.comments.store');

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
        ->name('rppm.word.comments.reply');

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
        ->name('rppm.word.comments.resolve');

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
        ->name('rppm.word.comments.destroy');

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
        ->name('rppm.word.destroy');

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
        ->name('rppm.word.edit');

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
    )->name('analisis-rppm');

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
    )->name('analisis-rppm.tampilan');

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
    )->name('analisis-rppm.word.upload');

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
        ->name('analisis-rppm.word.file');

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
        ->name('analisis-rppm.word.save');

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
        ->name('analisis-rppm.word.download');

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
        ->name('analisis-rppm.word.comments.store');

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
        ->name('analisis-rppm.word.comments.reply');

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
        ->name('analisis-rppm.word.comments.resolve');

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
        ->name('analisis-rppm.word.comments.destroy');

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
        ->name('analisis-rppm.word.destroy');

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
        ->name('analisis-rppm.word.edit');

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
    )->name('refleksiGuru');

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
    )->name('refleksi-guru.tampilan');

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
    )->name('refleksi-guru.upload');

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
        ->name('refleksi-guru.file');

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
        ->name('refleksi-guru.save');

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
        ->name('refleksi-guru.download');

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
        ->name('refleksi-guru.comments.store');

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
        ->name('refleksi-guru.comments.reply');

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
        ->name('refleksi-guru.comments.resolve');

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
        ->name('refleksi-guru.comments.destroy');

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
        ->name('refleksi-guru.destroy');

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
        ->name('refleksi-guru.edit');

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
        ->name('academicDocument.saveArchive');

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
        ->name('academicDrive.browse');

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
        ->name('academicDocument.sharedView');

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
        ->name('academicWord.saveArchive');

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
        ->name('academicWord.browse');

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
        ->name('academicWord.sharedView');

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
    ->name('lms.schoolAdmin.registrasiGuru.academic-drive.')
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
        )->name('index');

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
            ->name('files');

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
            ->name('download');

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
        )->name('download-selected');

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
        )->name('delete-selected');
    });
