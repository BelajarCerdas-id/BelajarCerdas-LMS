<?php

namespace App\Http\Controllers;

use App\Imports\SchoolPartnerSheetImport;
use App\Imports\SchoolPartnerUsersSheetImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;

class SchoolPartnerController extends Controller
{
    // function untuk bulk upload school partner
    public function bulkUploadSchoolPartner(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bulkUpload-school-partner' => 'required|file|mimes:xlsx,xls,csv|max:100000',
        ], [
            'bulkUpload-school-partner.required' => 'File tidak boleh kosong.',
            'bulkUpload-school-partner.mimes' => 'Format file harus .xlsx.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => [
                    'form_errors' => $validator->errors(),
                    'excel_validation_errors' => [],
                ]
            ], 422);
        }

        try {
            $userId = Auth::id();
            Excel::import(new SchoolPartnerSheetImport($userId, $request->file('bulkUpload-school-partner')), $request->file('bulkUpload-school-partner'));

            return response()->json([
                'status' => 'success',
                'message' => 'Import school partner berhasil.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'errors' => [
                    'form_errors' => [],
                    'excel_validation_errors' => $e->errors()['import'] ?? [],
                ]
            ], 422);
        }
    }

    // function untuk bulk upload school partner
    public function bulkUploadAddUsers(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bulkUpload-add-users' => 'required|file|mimes:xlsx,xls,csv|max:100000',
        ], [
            'bulkUpload-add-users.required' => 'File tidak boleh kosong.',
            'bulkUpload-add-users.mimes' => 'Format file harus .xlsx.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => [
                    'form_errors' => $validator->errors(),
                    'excel_validation_errors' => [],
                ]
            ], 422);
        }

        try {
            $userId = Auth::id();
            Excel::import(new SchoolPartnerUsersSheetImport($userId, $request->file('bulkUpload-add-users')), $request->file('bulkUpload-add-users'));

            return response()->json([
                'status' => 'success',
                'message' => 'Import users berhasil.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'errors' => [
                    'form_errors' => [],
                    'excel_validation_errors' => $e->errors()['import'] ?? [],
                ]
            ], 422);
        }
    }
}
