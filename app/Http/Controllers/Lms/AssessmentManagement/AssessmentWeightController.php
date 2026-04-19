<?php

namespace App\Http\Controllers\Lms\AssessmentManagement;

use App\Events\LmsAssessmentWeightManagement;
use App\Http\Controllers\Controller;
use App\Models\SchoolAssessmentType;
use App\Models\SchoolAssessmentTypeWeight;
use App\Models\SchoolClass;
use App\Models\SchoolPartner;
use App\Models\UserAccount;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AssessmentWeightController extends Controller
{
    // function assessment weight management
    public function assessmentWeight($schoolName, $schoolId)
    {
        $assessmentType = SchoolAssessmentType::where('is_active', 1)->where('school_partner_id', $schoolId)->get();

        $tahunAjaran = SchoolClass::where('school_partner_id', $schoolId)->pluck('tahun_ajaran')->unique()->sortDesc()->values();

        return view('features.lms.administrator.assessment-weight-management.lms-assessment-weight-management', compact('schoolName', 'schoolId', 'assessmentType', 'tahunAjaran'));
    }

    // function paginate assessment weight
    public function paginateAssessmentWeight(Request $request, $schoolName, $schoolId)
    {
        $users = UserAccount::with(['StudentProfile', 'SchoolStaffProfile'])->where(function ($query) use ($schoolId) {
            $query->whereHas('StudentProfile', function ($q) use ($schoolId) {
                $q->where('school_partner_id', $schoolId);
            })->orWhereHas('SchoolStaffProfile', function ($q) use ($schoolId) {
                $q->where('school_partner_id', $schoolId);
            });
        })->get();

        $getSchool = SchoolPartner::with('UserAccount.SchoolStaffProfile')->where('id', $schoolId)->first();

        $countUsers = $users->count();

        // dropdown data
        $tahunAjaran = SchoolClass::where('school_partner_id', $schoolId)->pluck('tahun_ajaran')->unique()->sortDesc()->values();

        $searchYear = $request->filled('search_year') ? $request->search_year : ($tahunAjaran->first() ?? null);

        // base query
        $query = SchoolAssessmentTypeWeight::with(['UserAccount', 'UserAccount.OfficeProfile', 'UserAccount.SchoolStaffProfile', 'SchoolAssessmentType'])
        ->where('school_partner_id', $schoolId)->where('school_year', $searchYear);

        $assessmentTypes = $query->orderBy('created_at', 'desc')->get();

        // manual pagination karena sudah menjadi collection
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 20;

        $assessmentTypes = new LengthAwarePaginator(
            $assessmentTypes->forPage($currentPage, $perPage)->values(),
            $assessmentTypes->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url()]
        );

        return response()->json([
            'data' => $assessmentTypes->items(),
            'links' => (string) $assessmentTypes->links(),
            'tahunAjaran'   => $tahunAjaran,
            'selectedYear'  => $searchYear,
            'schoolIdentity' => $getSchool,
            'countUsers' => $countUsers,
        ]);
    }

    // function assessment weight store
    public function assessmentWeightStore(Request $request, $schoolName, $schoolId)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'assessment_type_id' => 'required',
            'school_year' => 'required',
            'weight' => 'required|integer|min:1|max:100',
        ], [
            'assessment_type_id' => 'Harap pilih tipe asesmen.',
            'school_year' => 'Harap pilih tahun ajaran.',
            'weight.required' => 'Bobot asesmen tidak boleh kosong.',
            'weight.min' => 'Bobot asesmen harus lebih dari 0.',
            'weight.max' => 'Bobot asesmen tidak boleh lebih dari 100.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $exists = SchoolAssessmentTypeWeight::where('school_partner_id', $schoolId)->where('school_year', $request->school_year)->where('assessment_type_id', $request->assessment_type_id)->exists();

        if ($exists) {
            return response()->json([
                'status' => 'error',
                'errors' => [
                    'assessment_type_id' => ['Bobot pada asesmen di tahun ajaran ini telah terdaftar.']
                ]
            ], 422);
        }

        $totalWeight = SchoolAssessmentTypeWeight::where('school_partner_id', $schoolId)->where('school_year', $request->school_year)->sum('weight');

        $newTotal = $totalWeight + $request->weight;

        if ($newTotal > 100) {
            return response()->json([
                'status' => 'error',
                'error_type' => 'weight_limit_exceeded',
                'message' => 'Total bobot semua jenis asesmen tidak boleh melebihi 100%. Silakan sesuaikan bobot yang ada.',
            ], 422);
        }

        $assessmentTypeWeight = SchoolAssessmentTypeWeight::create([
            'user_id' => $user->id,
            'school_partner_id' => $schoolId,
            'assessment_type_id' => $request->assessment_type_id,
            'weight' => $request->weight,
            'school_year' => $request->school_year,
        ]);

        broadcast(new LmsAssessmentWeightManagement('SchoolAssessmentTypeWeight', 'create', $assessmentTypeWeight))->toOthers();

        return response()->json([
            'status'  => 'success',
            'message' => 'Bobot berhasil ditambahkan.',
            'data'    => $assessmentTypeWeight,
        ]);
    }

    // function assessment weight edit
    public function assessmentWeightEdit(Request $request, $schoolName, $schoolId, $assessmentWeightId)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'assessment_type_id' => 'required',
            'school_year' => 'required',
            'weight' => 'required|integer|min:1|max:100',
        ], [
            'assessment_type_id' => 'Harap pilih tipe asesmen.',
            'school_year' => 'Harap pilih tahun ajaran.',
            'weight.required' => 'Bobot asesmen tidak boleh kosong.',
            'weight.min' => 'Bobot asesmen harus lebih dari 0.',
            'weight.max' => 'Bobot asesmen tidak boleh lebih dari 100.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $exists = SchoolAssessmentTypeWeight::where('school_partner_id', $schoolId)->where('school_year', $request->school_year)->where('assessment_type_id', $request->assessment_type_id)
        ->where('id', '!=', $assessmentWeightId)->exists();

        if ($exists) {
            return response()->json([
                'status' => 'error',
                'errors' => [
                    'assessment_type_id' => ['Bobot pada asesmen di tahun ajaran ini telah terdaftar.']
                ]
            ], 422);
        }

        $assessmentTypeWeight = SchoolAssessmentTypeWeight::findOrFail($assessmentWeightId);

        $totalWeight = SchoolAssessmentTypeWeight::where('school_partner_id', $schoolId)->sum('weight');

        $oldWeight = $assessmentTypeWeight->weight;

        $newTotal = $totalWeight - $oldWeight + $request->weight;

        if ($newTotal > 100) {
            return response()->json([
                'status' => 'error',
                'error_type' => 'weight_limit_exceeded',
                'message' => 'Total bobot semua jenis asesmen tidak boleh melebihi 100%. Silakan sesuaikan bobot yang ada.',
            ], 422);
        }

        $assessmentTypeWeight->update([
            'user_id' => $user->id,
            'school_partner_id' => $schoolId,
            'assessment_type_id' => $request->assessment_type_id,
            'weight' => $request->weight,
            'school_year' => $request->school_year,
        ]);

        broadcast(new LmsAssessmentWeightManagement('SchoolAssessmentTypeWeight', 'update', $assessmentTypeWeight))->toOthers();

        return response()->json([
            'status'  => 'success',
            'message' => 'data berhasil diubah.',
            'data'    => $assessmentTypeWeight,
        ]);
    }
}