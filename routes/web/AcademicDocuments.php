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
    'lms/{role}/{school-name}/{school-id}/registrasi-guru'
)->name('lms.school_admin.registrasi_guru.')->group(function () {

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
    )->name('prota_prosem');

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
    )->name('prota_prosem.tampilan');

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
    )->name('prota_prosem.upload');

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
        ->name('prota_prosem.cells.update');

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
        ->name('prota_prosem.cells.autosave');

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
        ->name('prota_prosem.comments.store');

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
        ->name('prota_prosem.comments');

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
        ->name('prota_prosem.comments.resolve');

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
        ->name('prota_prosem.download');

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
        ->name('prota_prosem.delete');

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
        ->name('prota_prosem.show');

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
    )->name('bagan_analisis');

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
    )->name('bagan_analisis.tampilan');

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
    )->name('bagan_analisis.upload');

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
        ->name('bagan_analisis.download');

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
        ->name('bagan_analisis.cells.autosave');

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
        ->name('bagan_analisis.cells.update');

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
        ->name('bagan_analisis.comments.store');

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
        ->name('bagan_analisis.comments');

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
        ->name('bagan_analisis.comments.resolve');

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
        ->name('bagan_analisis.delete');

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
        ->name('bagan_analisis.show');

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
    )->name('analisis_rppm');

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
    )->name('analisis_rppm.tampilan');

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
    )->name('analisis_rppm.word.upload');

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
        ->name('analisis_rppm.word.file');

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
        ->name('analisis_rppm.word.save');

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
        ->name('analisis_rppm.word.download');

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
        ->name('analisis_rppm.word.comments.store');

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
        ->name('analisis_rppm.word.comments.reply');

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
        ->name('analisis_rppm.word.comments.resolve');

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
        ->name('analisis_rppm.word.comments.destroy');

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
        ->name('analisis_rppm.word.destroy');

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
        ->name('analisis_rppm.word.edit');

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
    )->name('refleksi_guru');

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
    )->name('refleksi_guru.tampilan');

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
    )->name('refleksi_guru.upload');

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
        ->name('refleksi_guru.file');

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
        ->name('refleksi_guru.save');

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
        ->name('refleksi_guru.download');

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
        ->name('refleksi_guru.comments.store');

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
        ->name('refleksi_guru.comments.reply');

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
        ->name('refleksi_guru.comments.resolve');

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
        ->name('refleksi_guru.comments.destroy');

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
        ->name('refleksi_guru.destroy');

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
        ->name('refleksi_guru.edit');

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
        ->name('academic_document.save_archive');

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
        ->name('academic_drive.browse');

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
        ->name('academic_document.shared_view');

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
        ->name('academic_word.save_archive');

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
        ->name('academic_word.browse');

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
        ->name('academic_word.shared_view');

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
    'lms/{role}/{school-name}/{school-id}/registrasi-guru/academic-drive'
)
    ->middleware('auth')
    ->name('lms.school_admin.registrasi_guru.academic_drive.')
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
        )->name('download_selected');

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
        )->name('delete_selected');
    });
