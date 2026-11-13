<?php

namespace App\Http\Controllers\Lms\TeacherSubject;

use App\Http\Controllers\Controller;
use App\Services\ClassName\ClassNameService;
use App\Models\Kurikulum;
use App\Models\SchoolClass;
use App\Models\SchoolPartner;
use App\Models\SchoolStaffProfile;
use App\Models\TeacherMapel;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TeacherSubjectController extends Controller
{
    // HELPER NAMING CLASS



    // TEACHER SUBJECT MANAGEMENT
    // function teacher suject management view
    public function lmsTeacherSubjectManagement($role, $schoolName, $schoolId)
    {
        $getCurriculum = Kurikulum::all();

        return view('features.lms.administrator.subject-teacher-management.lms-subject-teacher-management', compact('role', 'schoolName', 'schoolId', 'getCurriculum'));
    }

    // function paginate teacher subject management
    public function paginateLmsTeacherSubjectManagement(Request $request, $schoolName, $schoolId)
    {
        $getSchool = SchoolPartner::with('UserAccount.SchoolStaffProfile')
            ->where('id', $schoolId)
            ->first();

        $startLevelMap = [
            'SD'  => 1,
            'MI'  => 1,
            'SMP' => 7,
            'MTS' => 7,
            'SMA' => 10,
            'SMK' => 10,
            'MA'  => 10,
            'MAK' => 10
        ];

        $defaultLevel = $startLevelMap[$getSchool->jenjang_sekolah] ?? 1;

        $selectedClass = $request->filled('search_class') ? app(ClassNameService::class)->resolveClassLevel($request->search_class) : $defaultLevel;

        // dropdown data
        $tahunAjaran = SchoolClass::where('school_partner_id', $schoolId)->pluck('tahun_ajaran')->unique()->sortDesc()->values();

        $className = SchoolClass::where('school_partner_id', $schoolId)->pluck('class_name')->map(function ($className) {
            return app(ClassNameService::class)->extractClassLevel($className);
        })->unique()->sort()->values();

        $searchYear = $request->filled('search_year') ? $request->search_year : ($tahunAjaran->first() ?? null);

        $searchTeacher = $request->search_teacher;

        // base query
        $query = TeacherMapel::with(['UserAccount.SchoolStaffProfile', 'Mapel', 'SchoolClass'])
        ->whereHas('SchoolClass', function ($q) use ($schoolId, $searchYear) {
            $q->where('school_partner_id', $schoolId);

            if ($searchYear) {
                $q->where('tahun_ajaran', $searchYear);
            }
        });

        if ($searchTeacher) {
            $query->whereHas('UserAccount.SchoolStaffProfile', function ($q) use ($searchTeacher) {
                $q->where('nama_lengkap', 'like', '%' . $searchTeacher . '%');
            });
        }

        $teacherSubjectCollection = $query->orderBy('created_at', 'desc')->get();

        // Filter berdasarkan level kelas (PHP side filtering)
        if ($selectedClass) {
            $teacherSubjectCollection = $teacherSubjectCollection->filter(function ($item) use ($selectedClass) {

                if (!$item->SchoolClass || !$item->SchoolClass->class_name) {
                    return false;
                }

                return app(ClassNameService::class)->extractClassLevel($item->SchoolClass->class_name) == $selectedClass;
            });
        }

        // manual pagination karena sudah menjadi collection
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 20;

        $teacherSubject = new LengthAwarePaginator(
            $teacherSubjectCollection->forPage($currentPage, $perPage)->values(),
            $teacherSubjectCollection->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url()]
        );

        return response()->json([
            'data'          => $teacherSubject->items(),
            'links'         => (string) $teacherSubject->links(),
            'current_page'  => $teacherSubject->currentPage(),
            'per_page'      => $teacherSubject->perPage(),
            'tahunAjaran'   => $tahunAjaran,
            'selectedYear'  => $searchYear,
            'selectedClass' => $selectedClass,
            'className'     => $className
        ]);
    }

    // function teacher subject management store
    public function lmsTeacherSubjectManagementStore(Request $request, $schoolName, $schoolId)
    {
        $validator = Validator::make($request->all(), [
            'kurikulum_id' => 'required',
            'kelas_id' => 'required',
            'mapel_id' => 'required',
            'school_class_id' => 'required',
            'teacher' => [
                'required',
                'email',
                'regex:/^[A-Za-z0-9._-]+@(' . implode('|', array_map('preg_quote', config('email-domains.allowed_domains'))) . ')$/',
                Rule::unique('teacher_mapels', 'user_id')->where('school_class_id', $request->school_class_id),
            ],
        ], [
            'kurikulum_id.required' => 'Harap pilih kurikulum.',
            'kelas_id.required' => 'Harap pilih kelas.',
            'mapel_id.required' => 'Harap pilih mapel.',
            'school_class_id.required' => 'Harap pilih rombel kelas.',
            'teacher.required' => 'Harap isi nama guru.',
            'teacher.email'    => 'Format email tidak valid.',
            'teacher.regex'    => 'Domain email akun tidak diperbolehkan.',
            'teacher.unique'    => 'Guru telah terdaftar pada rombel kelas di tahun ini.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $getTeacher = SchoolStaffProfile::whereHas('UserAccount', function ($query) use ($request) {
            $query->where('email', $request->teacher);
        })->where('school_partner_id', $schoolId)->first();

        if (!$getTeacher) {
            return response()->json([
                'status' => 'error',
                'errors' => [
                    'teacher' => ['Akun guru tidak terdaftar.']
                ]
            ], 422);
        }

        $exists = TeacherMapel::where('user_id', $getTeacher->user_id)
            ->where('mapel_id', $request->mapel_id)
            ->where('school_class_id', $request->school_class_id)
            ->exists();

        if ($exists) {
            return response()->json([
                'status' => 'error',
                'errors' => [
                    'teacher' => ['Guru telah terdaftar pada mapel dan rombel kelas ini.']
                ]
            ], 422);
        }

        $teacherSubject = TeacherMapel::create([
            'user_id' => $getTeacher->user_id,
            'mapel_id' => $request->mapel_id,
            'school_class_id' => $request->school_class_id,
        ]);

        return response()->json([
            'data' => $teacherSubject,
            'message' => 'Data berhasil disimpan.',
        ]);
    }

    // function teacher subject management update
    public function lmsTeacherSubjectManagementEdit(Request $request, $schoolName, $schoolId, $teacherSubjectId)
    {
        $validator = Validator::make($request->all(), [
            'teacher' => [
                'required',
                'email',
                'regex:/^[A-Za-z0-9._-]+@(' . implode('|', array_map('preg_quote', config('email-domains.allowed_domains'))) . ')$/',
            ],
        ], [
            'teacher.required' => 'Harap isi nama guru.',
            'teacher.email'    => 'Format email tidak valid.',
            'teacher.regex'    => 'Domain email akun tidak diperbolehkan.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $getTeacher = SchoolStaffProfile::whereHas('UserAccount', function ($query) use ($request) {
            $query->where('email', $request->teacher);
        })->where('school_partner_id', $schoolId)->first();

        if (!$getTeacher) {
            return response()->json([
                'status' => 'error',
                'errors' => [
                    'teacher' => ['Akun guru tidak terdaftar.']
                ]
            ], 422);
        }

        $exists = TeacherMapel::where('user_id', $getTeacher->user_id)
            ->where('mapel_id', $request->mapel_id)
            ->where('school_class_id', $request->school_class_id)
            ->exists();

        if ($exists) {
            return response()->json([
                'status' => 'error',
                'errors' => [
                    'teacher' => ['Guru telah terdaftar pada mapel dan rombel kelas ini.']
                ]
            ], 422);
        }
        
        $teacherSubject = TeacherMapel::findOrFail($teacherSubjectId);

        $teacherSubject->update([
            'user_id' => $getTeacher->user_id,
        ]);

        return response()->json([
            'data' => $teacherSubject,
            'message' => 'Data berhasil disimpan.',
        ]);
    }

    // function teacher subject management activate
    public function lmsTeacherSubjectManagementActivate(Request $request, $schoolName, $schoolId, $teacherSubjectId)
    {
        $teacherSubject = TeacherMapel::findOrFail($teacherSubjectId);

        $teacherSubject->update([
            'is_active' => $request->is_active,
        ]);

        return response()->json([
            'data' => $teacherSubject,
            'message' => 'Status berhasil diubah.',
        ]);
    }
}
