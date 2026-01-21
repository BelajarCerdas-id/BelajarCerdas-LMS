<?php

namespace App\Services\LMS;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BankSoalWordImportService
{
    public function bankSoalImportService(Request $request)
    {
        // Validasi input form dari frontend (wajib diisi)
        $validator = Validator::make($request->all(), [
            // File wajib ada, format .docx, max 10 MB
            'bulkUpload-lms' => 'required|file|mimes:docx|max:100000',
            'kurikulum_id' => 'required',
            'kelas_id' => 'required',
            'mapel_id' => 'required',
            'bab_id' => 'required',
            'sub_bab_id' => 'required',
        ], [
            // Pesan error custom
            'bulkUpload-lms.required' => 'Harap upload soal.',
            'bulkUpload-lms.max' => 'Ukuran file melebihi kapasitas yang ditentukan.',
            'kurikulum_id.required' => 'Harap pilih kurikulum.',
            'kelas_id.required' => 'Harap pilih kelas.',
            'mapel_id.required' => 'Harap pilih mapel.',
            'bab_id.required' => 'Harap pilih bab.',
            'sub_bab_id.required' => 'Harap pilih sub bab.',
        ]);

        // Simpan error validasi form (tidak langsung return, biar bisa digabung dengan error validasi isi file Word)
        $formErrors = $validator->fails() ? $validator->errors()->toArray() : [];

        // Return respon error validasi
        if (!empty($formErrors)) {
            return response()->json([
                'status' => 'validation-error',
                'errors' => [
                    'form_errors' => $formErrors,
                ],
            ], 422);
        }
    }
}