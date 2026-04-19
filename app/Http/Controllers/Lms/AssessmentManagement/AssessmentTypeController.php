<?php

namespace App\Http\Controllers\Lms\AssessmentManagement;

use App\Events\LmsAssessmentTypeManagement;
use App\Http\Controllers\Controller;
use App\Models\AssessmentMode;
use App\Models\SchoolAssessmentType;
use App\Models\SchoolPartner;
use App\Models\UserAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AssessmentTypeController extends Controller
{
    // LMS ASSESSMENT TYPE MANAGEMENT
    // function lms assessment type management view
    public function lmsAssessmentTypeManagementView($schoolName, $schoolId)
    {
        $getAssessmentMode = AssessmentMode::all();

        return view('features.lms.administrator.assessment-type-management.lms-assessment-type-management', compact('schoolName', 'schoolId', 'getAssessmentMode'));
    }

    // function paginate lms assessment type management
    public function paginateLmsAssessmentTypeManagement($schoolName, $schoolId) 
    {
        $assessmentTypes = SchoolAssessmentType::with(['UserAccount', 'UserAccount.OfficeProfile', 'UserAccount.SchoolStaffProfile', 'AssessmentMode'])
        ->where('school_partner_id', $schoolId)->paginate(10);

        $users = UserAccount::with(['StudentProfile', 'SchoolStaffProfile'])->where(function ($query) use ($schoolId) {
            $query->whereHas('StudentProfile', function ($q) use ($schoolId) {
                $q->where('school_partner_id', $schoolId);
            })->orWhereHas('SchoolStaffProfile', function ($q) use ($schoolId) {
                $q->where('school_partner_id', $schoolId);
            });
        })->get();

        $getSchool = SchoolPartner::with('UserAccount.SchoolStaffProfile')->where('id', $schoolId)->first();

        $countUsers = $users->count();

        return response()->json([
            'data' => $assessmentTypes->items(),
            'links' => (string) $assessmentTypes->links(),
            'schoolIdentity' => $getSchool,
            'countUsers' => $countUsers,
        ]);
    }

    // function lms assessment type management store
    public function lmsAssessmentTypeManagementStore(Request $request, $schoolName, $schoolId)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                Rule::unique('school_assessment_types', 'name')->where('school_partner_id', $schoolId),
            ],
            'assessment_mode_id' => 'required',
            'is_remedial_allowed' => 'required',
            'max_remedial_attempt' => 'required_if:is_remedial_allowed,1|integer|min:1',
        ], [
            'name.required' => 'Nama asesmen tidak boleh kosong.',
            'name.unique'   => 'Nama asesmen telah terdaftar pada sekolah ini.',
            'assessment_mode_id.required' => 'Mode asesmen tidak boleh kosong.',
            'is_remedial_allowed.required' => 'Kebijakan remedial tidak boleh kosong.',
            'max_remedial_attempt.required_if' => 'Jumlah remedial tidak boleh kosong.',
            'max_remedial_attempt.min' => 'Jumlah remedial harus lebih dari 0.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $isRemedialAllowed = (int) $request->is_remedial_allowed;

        $maxRemedialAttempt = null;

        if ($isRemedialAllowed === 1) {
            $maxRemedialAttempt = (int) $request->max_remedial_attempt;
        }

        $assessmentType = SchoolAssessmentType::create([
            'user_id' => $user->id,
            'school_partner_id' => $schoolId,
            'name' => $request->name,
            'assessment_mode_id' => $request->assessment_mode_id,
            'is_remedial_allowed' => $request->is_remedial_allowed ?? null,
            'max_remedial_attempt' => $maxRemedialAttempt,
        ]);

        broadcast(new LmsAssessmentTypeManagement('SchoolAssessmentType', 'create', $assessmentType))->toOthers();

        return response()->json([
            'status'  => 'success',
            'message' => 'Nama asesmen berhasil ditambahkan.',
            'data'    => $assessmentType,
        ]);
    }

    // function lms assessment type management edit
    public function lmsAssessmentTypeManagementEdit(Request $request, $schoolName, $schoolId, $assessmentTypeId)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                Rule::unique('school_assessment_types', 'name')->where('school_partner_id', $schoolId)->ignore($assessmentTypeId),
            ],
            'assessment_mode_id' => 'required',
            'is_remedial_allowed' => 'required',
            'max_remedial_attempt' => 'required_if:is_remedial_allowed,1|integer|min:1',
        ], [
            'name.required' => 'Nama asesmen tidak boleh kosong.',
            'name.unique'   => 'Nama asesmen telah terdaftar pada sekolah ini.',
            'assessment_mode_id.required' => 'Mode asesmen tidak boleh kosong.',
            'is_remedial_allowed.required' => 'Kebijakan remedial tidak boleh kosong.',
            'max_remedial_attempt.required_if' => 'Jumlah remedial tidak boleh kosong.',
            'max_remedial_attempt.min' => 'Jumlah remedial harus lebih dari 0.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $assessmentType = SchoolAssessmentType::findOrFail($assessmentTypeId);

        $isRemedialAllowed = (int) $request->is_remedial_allowed;

        $maxRemedialAttempt = null;

        if ($isRemedialAllowed === 1) {
            $maxRemedialAttempt = (int) $request->max_remedial_attempt;
        }

        $assessmentType->update([
            'user_id' => $user->id,
            'name' => $request->name,
            'assessment_mode_id' => $request->assessment_mode_id,
            'is_remedial_allowed' => $request->is_remedial_allowed ?? null,
            'max_remedial_attempt' => $maxRemedialAttempt,
        ]);

        broadcast(new LmsAssessmentTypeManagement('SchoolAssessmentType', 'edit', $assessmentType))->toOthers();

        return response()->json([
            'data' => $assessmentType,
            'message' => 'Data berhasil diperbarui.',
        ]);
    }

    // function lms assessment type management activate
    public function lmsAssessmentTypeManagementActivate(Request $request, $schoolName, $schoolId, $assessmentTypeId)
    {
        $assessmentType = SchoolAssessmentType::findOrFail($assessmentTypeId);

        $assessmentType->update([
            'is_active' => $request->is_active,
        ]);

        broadcast(new LmsAssessmentTypeManagement('SchoolAssessmentType', 'activate', $assessmentType))->toOthers();

        return response()->json([
            'data' => $assessmentType,
            'message' => 'Status asesmen berhasil diperbarui.',
        ]);
    }
}