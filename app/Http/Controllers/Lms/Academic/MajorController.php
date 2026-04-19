<?php
namespace App\Http\Controllers\Lms\Academic;

use App\Events\LmsManagementMajors;
use App\Http\Controllers\Controller;
use App\Models\SchoolMajor;
use App\Models\SchoolPartner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class MajorController extends Controller
{
    // function lms management majors view
    public function lmsManagementMajorsView($schoolName, $schoolId, $role)
    {
        return view('Features.lms.administrator.lms-school-subscription-management-majors', compact('schoolName', 'schoolId', 'role'));
    }

    // function paginate lms management majors
    public function paginateLmsSchoolSubscriptionMajors(Request $request, $schoolName, $schoolId, $role)
    {
        $majors = SchoolMajor::withCount([
            'schoolClass as school_class_count' => function ($q) {
                $q->where('status_major', 'active');
            }
        ])->where('school_partner_id', $schoolId)->get();

        $getSchool = SchoolPartner::with('UserAccount.SchoolStaffProfile')->where('id', $schoolId)->first();

        return response()->json([
            'data' => $majors,
            'schoolIdentity' => $getSchool,
            'lmsManagementClass' => '/lms/school-subscription/:schoolName/:schoolId/management-role-account/:role/management-majors/:majorId/management-class',
        ]);
    }

    // function lms management create majors
    public function lmsManagementCreateMajor(Request $request, $schoolName, $schoolId, $role)
    {
        $validator = Validator::make($request->all(), [
            'major_name' => [
                'required',
                Rule::unique('school_majors', 'major_name')->where('school_partner_id', $schoolId),
            ],
            'major_code' => [
                'required',
                Rule::unique('school_majors', 'major_code')->where('school_partner_id', $schoolId),
            ]
        ], [
            'major_name.required' => 'Nama jurusan harus diisi.',
            'major_name.unique' => 'Nama jurusan telah terdaftar pada sekolah ini.',
            'major_code.required' => 'Kode jurusan harus diisi.',
            'major_code.unique' => 'Kode jurusan telah terdaftar pada sekolah ini.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $major = SchoolMajor::create([
            'school_partner_id' => $schoolId,
            'major_name' => request()->major_name,
            'major_code' => request()->major_code,
        ]);

        broadcast(new LmsManagementMajors('SchoolMajor', 'create', $major))->toOthers();

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil menambahkan jurusan',
        ]);
    }

    // function lms management edit major
    public function lmsManagementEditMajor(Request $request, $schoolName, $schoolId, $role, $majorId)
    {
        $validator = Validator::make($request->all(), [
            'major_name' => [
                'required',
                Rule::unique('school_majors', 'major_name')->where('school_partner_id', $schoolId)->ignore($majorId),
            ],
            'major_code' => [
                'required',
            ]
        ], [
            'major_name.required' => 'Nama jurusan harus diisi.',
            'major_name.unique' => 'Nama jurusan telah terdaftar pada sekolah ini.',
            'major_code.required' => 'Kode jurusan harus diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }
        $major = SchoolMajor::findOrFail($majorId);

        $major->update([
            'school_partner_id' => $schoolId,
            'major_name' => $request->major_name,
            'major_code' => $request->major_code,
        ]);

        broadcast(new LmsManagementMajors('SchoolMajor', 'edit', $major));

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil edit jurusan',
        ]);
    }

    // function lms activate major
    public function lmsActivateMajor(Request $request, $id)
    {
        $major = SchoolMajor::findOrFail($id);

        $major->update([
            'status_major' => $request->status_major,
        ]);

        broadcast(new LmsManagementMajors('SchoolMajor', 'activate', $major))->toOthers();

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil mengubah status jurusan',
        ]);
    }
}