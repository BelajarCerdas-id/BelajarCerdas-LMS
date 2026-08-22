<?php

namespace App\Http\Controllers;

use App\Models\Extracurricular;
use App\Models\ExtracurricularKelengkapan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KelengkapanEkskulController extends Controller
{
    /**
     * Halaman Kelengkapan Ekstrakurikuler
     */
    public function index(
        $role,
        $schoolName,
        $schoolId
    ) {
        /*
        |--------------------------------------------------------------------------
        | Ambil ekstrakurikuler aktif sekolah
        |--------------------------------------------------------------------------
        */

        $extracurriculars = Extracurricular::where(
            'school_partner_id',
            $schoolId
        )
            ->where('status', 'active')
            ->with('kelengkapan')
            ->orderBy('name')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Pastikan setiap ekstrakurikuler memiliki data kelengkapan
        |--------------------------------------------------------------------------
        */

        foreach ($extracurriculars as $extracurricular) {

            if (!$extracurricular->kelengkapan) {

                ExtracurricularKelengkapan::create([
                    'extracurricular_id' => $extracurricular->id,
                    'silabus' => false,
                    'prota' => false,
                    'prosem' => false,
                    'rpp' => false,
                    'comment' => null,
                ]);

                // Refresh relationship
                $extracurricular->load('kelengkapan');
            }
        }


        /*
        |--------------------------------------------------------------------------
        | KPI
        |--------------------------------------------------------------------------
        */

        $totalExtracurricular = $extracurriculars->count();

        $completeExtracurriculars = $extracurriculars->filter(
            function ($item) {

                return $item->kelengkapan
                    && $item->kelengkapan->is_complete;
            }
        );

        $incompleteExtracurriculars = $extracurriculars->filter(
            function ($item) {

                return !$item->kelengkapan
                    || !$item->kelengkapan->is_complete;
            }
        );

        $totalComplete = $completeExtracurriculars->count();

        $totalIncomplete = $incompleteExtracurriculars->count();


        /*
        |--------------------------------------------------------------------------
        | Return View
        |--------------------------------------------------------------------------
        */

        return view(
            'features.lms.student-vice-principal.extracurricular.kelengkapan.kelengkapan-extraculikuler',
            [
                'extracurriculars' => $extracurriculars,

                'role' => $role,
                'schoolName' => $schoolName,
                'schoolId' => $schoolId,

                'totalExtracurricular' => $totalExtracurricular,
                'totalComplete' => $totalComplete,
                'totalIncomplete' => $totalIncomplete,
            ]
        );
    }


    /**
     * Simpan komentar Wakil Kesiswaan
     */
    public function saveComment(
        Request $request,
        $role,
        $schoolName,
        $schoolId,
        $extracurricularId
    ) {

        if (
            !Auth::check() ||
            Auth::user()->role !== 'Wakil Kesiswaan'
        ) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki izin untuk mengubah komentar.',
            ], 403);
        }


        $extracurricular = Extracurricular::where(
            'school_partner_id',
            $schoolId
        )
            ->where('status', 'active')
            ->findOrFail($extracurricularId);


        $request->validate([
            'comment' => 'nullable|string|max:2000',
        ]);


        $kelengkapan =
            ExtracurricularKelengkapan::firstOrCreate(
                [
                    'extracurricular_id' => $extracurricular->id,
                ],
                [
                    'silabus' => false,
                    'prota' => false,
                    'prosem' => false,
                    'rpp' => false,
                    'comment' => null,
                ]
            );


        $kelengkapan->update([
            'comment' => $request->input('comment'),
        ]);


        return response()->json([
            'status' => 'success',
            'message' => 'Komentar berhasil disimpan.',
        ]);
    }


    /**
     * Update status dokumen
     */
    public function updateDocument(
        Request $request,
        $role,
        $schoolName,
        $schoolId,
        $extracurricularId
    ) {

        $extracurricular = Extracurricular::where(
            'school_partner_id',
            $schoolId
        )
            ->where('status', 'active')
            ->findOrFail($extracurricularId);


        $request->validate([
            'document' => 'required|in:silabus,prota,prosem,rpp',
            'value' => 'required|boolean',
        ]);


        $kelengkapan =
            ExtracurricularKelengkapan::firstOrCreate(
                [
                    'extracurricular_id' => $extracurricular->id,
                ],
                [
                    'silabus' => false,
                    'prota' => false,
                    'prosem' => false,
                    'rpp' => false,
                    'comment' => null,
                ]
            );


        $kelengkapan->update([
            $request->input('document') =>
                $request->boolean('value'),
        ]);


        // Refresh supaya total_document dan is_complete
        // mengambil nilai terbaru dari database.
        $kelengkapan->refresh();


        return response()->json([
            'status' => 'success',
            'message' => 'Status dokumen berhasil diperbarui.',
            'data' => [
                'total_document' =>
                    $kelengkapan->total_document,

                'is_complete' =>
                    $kelengkapan->is_complete,
            ],
        ]);
    }


    /**
     * Data KPI untuk modal
     */
    public function kpi(
        $role,
        $schoolName,
        $schoolId
    ) {

        $extracurriculars = Extracurricular::where(
            'school_partner_id',
            $schoolId
        )
            ->where('status', 'active')
            ->with('kelengkapan')
            ->orderBy('name')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Pastikan data kelengkapan tersedia
        |--------------------------------------------------------------------------
        */

        foreach ($extracurriculars as $extracurricular) {

            if (!$extracurricular->kelengkapan) {

                ExtracurricularKelengkapan::create([
                    'extracurricular_id' =>
                        $extracurricular->id,

                    'silabus' => false,
                    'prota' => false,
                    'prosem' => false,
                    'rpp' => false,
                    'comment' => null,
                ]);

                $extracurricular->load('kelengkapan');
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Pisahkan lengkap dan belum lengkap
        |--------------------------------------------------------------------------
        */

        $complete =
            $extracurriculars
                ->filter(function ($item) {

                    return $item->kelengkapan
                        && $item->kelengkapan->is_complete;
                })
                ->values();


        $incomplete =
            $extracurriculars
                ->filter(function ($item) {

                    return !$item->kelengkapan
                        || !$item->kelengkapan->is_complete;
                })
                ->values();


        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return response()->json([

            'total' =>
                $extracurriculars->count(),

            'complete' =>
                $complete
                    ->map(function ($item) {

                        return [
                            'id' => $item->id,
                            'name' => $item->name,
                            'description' =>
                                $item->description,
                        ];
                    })
                    ->values(),

            'incomplete' =>
                $incomplete
                    ->map(function ($item) {

                        return [
                            'id' => $item->id,
                            'name' => $item->name,
                            'description' =>
                                $item->description,
                        ];
                    })
                    ->values(),
        ]);
    }

    /**
 * Detail Kelengkapan Ekstrakurikuler
 */
public function detail(
    $role,
    $schoolName,
    $schoolId,
    $extracurricularId
) {
    $extracurricular = Extracurricular::where(
        'school_partner_id',
        $schoolId
    )
        ->where('status', 'active')
        ->with('kelengkapan')
        ->findOrFail($extracurricularId);

    if (!$extracurricular->kelengkapan) {
        ExtracurricularKelengkapan::create([
            'extracurricular_id' => $extracurricular->id,

            'silabus' => false,
            'silabus_file' => null,

            'prota' => false,
            'prota_file' => null,

            'prosem' => false,
            'prosem_file' => null,

            'rpp' => false,
            'rpp_file' => null,

            'comment' => null,
        ]);

        $extracurricular->load('kelengkapan');
    }

    $kelengkapan = $extracurricular->kelengkapan;

    return view(
    'features.lms.student-vice-principal.extracurricular.kelengkapan.kelengkapan-extrakulikuler-detail',
    [
        'extracurricular' => $extracurricular,
        'kelengkapan' => $kelengkapan,

        'role' => $role,
        'schoolName' => $schoolName,
        'schoolId' => $schoolId,
        'extracurricularId' => $extracurricularId,

        'totalDocument' => $kelengkapan->total_document,
        'isComplete' => $kelengkapan->is_complete,
    ]
);
}

/**
 * Upload / Ganti dokumen kelengkapan
 */
public function uploadDocument(
    Request $request,
    $role,
    $schoolName,
    $schoolId,
    $extracurricularId
) {
    $extracurricular = Extracurricular::where(
        'school_partner_id',
        $schoolId
    )
        ->where('status', 'active')
        ->findOrFail($extracurricularId);

    $request->validate([
        'document' => 'required|in:silabus,prota,prosem,rpp',
        'file' => 'required|file|mimes:pdf|max:10240',
    ]);

    $kelengkapan = ExtracurricularKelengkapan::firstOrCreate(
        [
            'extracurricular_id' => $extracurricular->id,
        ],
        [
            'silabus' => false,
            'silabus_file' => null,

            'prota' => false,
            'prota_file' => null,

            'prosem' => false,
            'prosem_file' => null,

            'rpp' => false,
            'rpp_file' => null,

            'comment' => null,
        ]
    );

    $document = $request->input('document');

    $fileColumn = $document . '_file';

    /*
     * Hapus file lama jika sedang melakukan Ganti.
     */
    $oldFile = $kelengkapan->{$fileColumn};

    if ($oldFile && Storage::disk('public')->exists($oldFile)) {
        Storage::disk('public')->delete($oldFile);
    }

    /*
     * Simpan file baru.
     */
    $path = $request->file('file')->store(
        'extracurricular/' . $extracurricular->id . '/kelengkapan',
        'public'
    );

    /*
     * Status dokumen otomatis menjadi TRUE.
     */
    $kelengkapan->update([
        $document => true,
        $fileColumn => $path,
    ]);

    $kelengkapan->refresh();

    return response()->json([
        'status' => 'success',

        'message' => ucfirst($document)
            . ' berhasil diunggah.',

            'data' => [
            'document' => $document,

            'file' => $kelengkapan->{$fileColumn},

            'file_url' => $kelengkapan->{$fileColumn}
            ? asset('storage/' . ltrim($kelengkapan->{$fileColumn}, '/'))
            : null,

            'total_document' => $kelengkapan->total_document,

            'is_complete' => $kelengkapan->is_complete, 
        ],
    ]);
}

/**
 * Lihat dokumen kelengkapan
 */
public function viewDocument(
    $role,
    $schoolName,
    $schoolId,
    $extracurricularId,
    $document
) {
    abort_unless(
        in_array($document, [
            'silabus',
            'prota',
            'prosem',
            'rpp',
        ]),
        404
    );

    $extracurricular = Extracurricular::where(
        'school_partner_id',
        $schoolId
    )
        ->where('status', 'active')
        ->with('kelengkapan')
        ->findOrFail($extracurricularId);

    $kelengkapan = $extracurricular->kelengkapan;

    if (!$kelengkapan) {
        abort(404, 'Data kelengkapan tidak ditemukan.');
    }

    // Ambil path dari kolom *_file
    $fileColumn = $document . '_file';

    $file = $kelengkapan->{$fileColumn};

    if (!$file) {
        abort(404, 'Dokumen belum tersedia.');
    }

    if (!Storage::disk('public')->exists($file)) {
        abort(404, 'File tidak ditemukan.');
    }

    return response()->file(
        Storage::disk('public')->path($file)
    );
}

public function uploadKelengkapan(
    Request $request,
    $role,
    $schoolName,
    $schoolId,
    $extracurricularId
) {
    $request->validate([
        'document' => 'required|in:silabus,prota,prosem,rpp',
        'file' => 'required|file|mimes:pdf|max:10240',
    ]);

    $extracurricular = Extracurricular::where(
        'school_partner_id',
        $schoolId
    )
        ->where('status', 'active')
        ->findOrFail($extracurricularId);

    /*
    |--------------------------------------------------------------------------
    | Pastikan data kelengkapan tersedia
    |--------------------------------------------------------------------------
    */

    $kelengkapan = $extracurricular->kelengkapan;

    if (!$kelengkapan) {
        $kelengkapan = $extracurricular->kelengkapan()->create([
            'silabus' => false,
            'silabus_file' => null,

            'prota' => false,
            'prota_file' => null,

            'prosem' => false,
            'prosem_file' => null,

            'rpp' => false,
            'rpp_file' => null,

            'comment' => null,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Tentukan dokumen
    |--------------------------------------------------------------------------
    */

    $document = $request->input('document');

    $file = $request->file('file');

    /*
    |--------------------------------------------------------------------------
    | Hapus file lama jika ada
    |--------------------------------------------------------------------------
    */

    $fileColumn = $document . '_file';

    $oldFile = $kelengkapan->{$fileColumn};

    if ($oldFile && Storage::disk('public')->exists($oldFile)) {
        Storage::disk('public')->delete($oldFile);
    }

    /*
    |--------------------------------------------------------------------------
    | Nama file
    |--------------------------------------------------------------------------
    */

    $fileName =
        $document . '_' .
        $extracurricularId . '_' .
        time() . '.' .
        $file->getClientOriginalExtension();

    /*
    |--------------------------------------------------------------------------
    | Folder
    |--------------------------------------------------------------------------
    */

    $folder = 'extracurricular/kelengkapan/' . $extracurricularId;

    /*
    |--------------------------------------------------------------------------
    | Upload
    |--------------------------------------------------------------------------
    */

    $path = $file->storeAs(
        $folder,
        $fileName,
        'public'
    );

    /*
    |--------------------------------------------------------------------------
    | Simpan STATUS dan PATH
    |--------------------------------------------------------------------------
    */

    $kelengkapan->update([
        $document => true,
        $fileColumn => $path,
    ]);

    $kelengkapan->refresh();

    /*
    |--------------------------------------------------------------------------
    | Response
    |--------------------------------------------------------------------------
    */

    return response()->json([
        'success' => true,
        'status' => 'success',

        'message' =>
            strtoupper($document) .
            ' berhasil diunggah.',

        'document' => $document,

        'path' => $path,

        'file' => $path,

        'file_url' => asset(
            'storage/' . ltrim($path, '/')
        ),

        'total_document' =>
            $kelengkapan->total_document,

        'is_complete' =>
            $kelengkapan->is_complete,
    ]);
}
}