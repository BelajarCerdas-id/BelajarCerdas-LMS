<?php

namespace App\Http\Controllers\Lms\Academic;

use App\Events\LmsManagementStudentInClass;
use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\SchoolPartner;
use App\Models\StudentSchoolClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StudentSchoolClassController extends Controller
{
    // HELPER NAMING CLASS
    private function extractClassLevel(string $className): int
    {
        $className = trim(strtoupper($className));

        // 1. Coba angka di depan (7, 10, 12, dst)
        if (preg_match('/^\d+/', $className, $match)) {
            return (int) $match[0];
        }

        // 2. Coba romawi di depan (I, II, III, IV, V, VI, VII, VIII, IX, X, XI, XII)
        if (preg_match('/^(XII|XI|X|IX|VIII|VII|VI|V|IV|III|II|I)\b/', $className, $match)) {
            return $this->romanToInt($match[0]);
        }

        return 0; // fallback aman
    }

    private function romanToInt(string $roman): int
    {
        $map = [
            'I' => 1,
            'II' => 2,
            'III' => 3,
            'IV' => 4,
            'V' => 5,
            'VI' => 6,
            'VII' => 7,
            'VIII' => 8,
            'IX' => 9,
            'X' => 10,
            'XI' => 11,
            'XII' => 12,
        ];

        return $map[$roman] ?? 0;
    }
    
    // function lms management students view
    public function lmsManagementStudentsView($schoolName, $schoolId, $role, $classId, $majorId = null)
    {
        return view('Features.lms.administrator.lms-school-subscription-management-students', compact('schoolName', 'schoolId', 'role', 'classId', 'majorId'));
    }

    // function paginate lms management users
    public function paginateLmsSchoolSubscriptionUsers($schoolName, $schoolId, $role, $classId, $majorId = null)
    {
        $getUsersQuery = StudentSchoolClass::with(['UserAccount.StudentProfile', 'SchoolClass', 
        'SchoolClass.UserAccount.SchoolStaffProfile']);

        if ($majorId) {
            $getUsersQuery->with(['SchoolClass.SchoolMajor']);
        }

        $getUsers = $getUsersQuery->whereHas('SchoolClass', function ($query) use ($schoolId) {
            $query->where('school_partner_id', $schoolId);
        })->where('school_class_id', $classId)->get();

        $getSchool = SchoolPartner::with('UserAccount.SchoolStaffProfile')->where('id', $schoolId)->first();

        $academicActionCheck = $getUsers->map(function ($item) {
            $item->has_academic_action = !empty($item->academic_action);
            return $item;
        });;

        return response()->json([
            'data' => $getUsers,
            'schoolIdentity' => $getSchool,
            'academicActionCheck' => $academicActionCheck,
        ]);
    }

    // function activate student in class
    public function lmsActivateStudentInClass(Request $request, $id)
    {
        $studentSchoolClass = StudentSchoolClass::findOrFail($id);

        $studentSchoolClass->update([
            'student_class_status' => $request->student_class_status,
        ]);

        broadcast(new LmsManagementStudentInClass('StudentSchoolClass', 'activate', $studentSchoolClass))->toOthers();

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil mengubah status siswa di kelas',
        ]);
    }

    // function promote class lms management users
    public function promotionClassOptions(Request $request, $schoolId, $majorId = null)
    {
        $currentClassId = $request->class_id;

        $currentClass = SchoolClass::findOrFail($currentClassId);

        // ambil tingkat kelas (7 dari 7.1)
        $currentLevel = $this->extractClassLevel($currentClass->class_name);
        $currentYear  = $currentClass->tahun_ajaran;

        $targetLevel = $currentLevel + 1;

        $classesQuery = SchoolClass::where('school_partner_id', $schoolId)->orderBy('tahun_ajaran');

        if ($majorId) {
            $classesQuery->where('major_id', $majorId);
        }

        // ambil semua kelas sekolah
        $classes = $classesQuery->get()->filter(function ($cls) use ($currentYear, $currentLevel, $targetLevel) {
            // tahun ajaran lebih besar
            if ($cls->tahun_ajaran <= $currentYear) {
                return false;
            }

            $level = $this->extractClassLevel($cls->class_name);

            // hanya memunculkan options 1 tingkat kelas dari kelas sebelumnya
            return $level === $targetLevel;
        })->values(); // reset index

        return response()->json($classes);
    }

    // function repeat class lms management users
    public function repeatClassOptions(Request $request, $schoolId, $majorId = null)
    {
        $currentClassId = $request->class_id;

        $currentClass = SchoolClass::findOrFail($currentClassId);

        // ambil tingkat kelas (7 dari 7.1)
        $currentLevel = $this->extractClassLevel($currentClass->class_name);
        $currentYear  = $currentClass->tahun_ajaran;

        $classesQuery = SchoolClass::where('school_partner_id', $schoolId)->orderBy('tahun_ajaran');

        if ($majorId) {
            $classesQuery->where('major_id', $majorId);
        }

        // ambil semua kelas sekolah
        $classes = $classesQuery->get()->filter(function ($cls) use ($currentYear, $currentLevel) {
            // tahun ajaran lebih besar
            if ($cls->tahun_ajaran <= $currentYear) {
                return false;
            }

            $level = $this->extractClassLevel($cls->class_name);

            // hanya memunculkan options 1 tingkat kelas dari kelas sebelumnya
            return $level === $currentLevel;
        })->values(); // reset index

        return response()->json($classes);
    }

    // function move class lms management users
    public function moveClassOptions(Request $request, $schoolId, $majorId = null)
    {
        $currentClassId = $request->class_id;

        $currentClass = SchoolClass::findOrFail($currentClassId);

        // ambil tingkat kelas (7 dari 7.1)
        $currentLevel = $this->extractClassLevel($currentClass->class_name);
        $currentYear  = $currentClass->tahun_ajaran;

        $classesQuery = SchoolClass::where('school_partner_id', $schoolId)->where('tahun_ajaran', $currentYear)
        ->where('id', '!=', $currentClassId)->orderBy('tahun_ajaran');

        if ($majorId) {
            $classesQuery->where('major_id', $majorId);
        }

        // ambil semua kelas sekolah
        $classes = $classesQuery->get()->filter(function ($cls) use ($currentYear, $currentLevel) {
            $level = $level = $this->extractClassLevel($cls->class_name);;

            // hanya memunculkan options 1 tingkat kelas dari kelas sebelumnya
            return $level === $currentLevel;
        })->values(); // reset index

        return response()->json($classes);
    }

    // function move major lms management users
    public function moveMajorOptions(Request $request, $schoolId, $majorId = null)
    {
        $currentClass = SchoolClass::findOrFail($request->class_id);
        $currentLevel = $this->extractClassLevel($currentClass->class_name);

        $classes = SchoolClass::with(['SchoolMajor'])->where('school_partner_id', $schoolId)
            ->where('tahun_ajaran', $currentClass->tahun_ajaran)
            ->where('id', '!=', $currentClass->id)
            ->when($majorId, fn ($q) => $q->where('major_id', '!=', $majorId))
            ->get()
            ->filter(fn ($cls) =>
                $this->extractClassLevel($cls->class_name) === $currentLevel
            )
            ->values();

        return response()->json($classes);
    }

    // function update lms management promote class
    public function lmsManagementPromoteClass(Request $request, $schoolName, $schoolId, $role, $classId)
    {
        $validator = Validator::make($request->all(), [
            'tahun_ajaran' => 'required',
            'school_class_id' => 'required',
        ], [
            'tahun_ajaran.required' => 'Tahun ajaran harus diisi.',
            'school_class_id.required' => 'Kelas harus diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $explodeStudentIds = explode(',', $request->student_id);

        $studentSchoolClass = StudentSchoolClass::whereIn('student_id', $explodeStudentIds)->where('school_class_id', $classId)->whereNotNull('academic_action')->where('academic_action', '!=', '')
        ->exists();

        if ($studentSchoolClass) {
            return response()->json([
                'status' => 'error',
                'studentSchoolClassCheck' => true,
                'message' => 'tidak dapat menggunakan aksi akademik kembali pada siswa yang telah memiliki keterangan.',
            ], 422);
        } else {
            foreach ($explodeStudentIds as $studentId) {
                StudentSchoolClass::where('student_id', $studentId) ->where('school_class_id', $classId)->update([
                    'student_class_status' => 'inactive',
                    'academic_action' => 'PROMOTED_CLASS',
                ]);
    
                StudentSchoolClass::create([
                    'student_id' => $studentId,
                    'school_class_id' => $request->school_class_id,
                    'student_class_status' => 'active',
                ]);
            }
        }

        broadcast(new LmsManagementStudentInClass('StudentSchoolClass', 'promote-class', $studentSchoolClass))->toOthers();

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil menaikkan kelas',
        ]);
    }

    // function update lms management repeat class
    public function lmsManagementRepeatClass(Request $request, $schoolName, $schoolId, $role, $classId)
    {
        $validator = Validator::make($request->all(), [
            'tahun_ajaran' => 'required',
            'school_class_id' => 'required',
        ], [
            'tahun_ajaran.required' => 'Tahun ajaran harus diisi.',
            'school_class_id.required' => 'Kelas harus diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $explodeStudentIds = explode(',', $request->student_id);

        $studentSchoolClass = StudentSchoolClass::whereIn('student_id', $explodeStudentIds)->where('school_class_id', $classId)->whereNotNull('academic_action')->where('academic_action', '!=', '')
        ->exists();

        if ($studentSchoolClass) {
            return response()->json([
                'status' => 'error',
                'studentSchoolClassCheck' => true,
                'message' => 'tidak dapat menggunakan aksi akademik kembali pada siswa yang telah memiliki keterangan.',
            ], 422);
        } else {
            foreach ($explodeStudentIds as $studentId) {
                StudentSchoolClass::where('student_id', $studentId)->update([
                    'student_class_status' => 'inactive',
                    'academic_action' => 'REPEATED_CLASS',
                ]);
    
                StudentSchoolClass::create([
                    'student_id' => $studentId,
                    'school_class_id' => $request->school_class_id,
                    'student_class_status' => 'active',
                ]);
            }
        }

        broadcast(new LmsManagementStudentInClass('StudentSchoolClass', 'repeat-class', $studentSchoolClass))->toOthers();

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil mengulang kelas',
        ]);
    }

    // function update lms management move class
    public function lmsManagementMoveClass(Request $request, $schoolName, $schoolId, $role, $classId)
    {
        $validator = Validator::make($request->all(), [
            'tahun_ajaran' => 'required',
            'school_class_id' => 'required',
        ], [
            'tahun_ajaran.required' => 'Tahun ajaran harus diisi.',
            'school_class_id.required' => 'Kelas harus diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $explodeStudentIds = explode(',', $request->student_id);

        $studentSchoolClass = StudentSchoolClass::whereIn('student_id', $explodeStudentIds)->where('school_class_id', $classId)->whereNotNull('academic_action')->where('academic_action', '!=', '')
        ->exists();

        if ($studentSchoolClass) {
            return response()->json([
                'status' => 'error',
                'studentSchoolClassCheck' => true,
                'message' => 'tidak dapat menggunakan aksi akademik kembali pada siswa yang telah memiliki keterangan.',
            ], 422);
        } else {
            foreach ($explodeStudentIds as $studentId) {
                StudentSchoolClass::where('student_id', $studentId)->update([
                    'student_class_status' => 'inactive',
                    'academic_action' => 'TRANSFERRED_CLASS',
                ]);
    
                StudentSchoolClass::create([
                    'student_id' => $studentId,
                    'school_class_id' => $request->school_class_id,
                    'student_class_status' => 'active',
                ]);
            }
        }

        broadcast(new LmsManagementStudentInClass('StudentSchoolClass', 'move-class', $studentSchoolClass))->toOthers();

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil memindahkan kelas',
        ]);
    }

    // function update lms management move major
    public function lmsManagementMoveMajor(Request $request, $schoolName, $schoolId, $role, $classId)
    {
        $validator = Validator::make($request->all(), [
            'tahun_ajaran' => 'required',
            'major_id' => 'required',
            'school_class_id' => 'required',
        ], [
            'tahun_ajaran.required' => 'Tahun ajaran harus diisi.',
            'major_id.required' => 'Jurusan harus diisi.',
            'school_class_id.required' => 'Kelas harus diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $explodeStudentIds = explode(',', $request->student_id);
        $majorId = $request->major_id;

        $studentSchoolClass = StudentSchoolClass::whereIn('student_id', $explodeStudentIds)->where('school_class_id', $classId)->whereNotNull('academic_action')->where('academic_action', '!=', '')
        ->exists();

        if ($studentSchoolClass) {
            return response()->json([
                'status' => 'error',
                'studentSchoolClassCheck' => true,
                'message' => 'tidak dapat menggunakan aksi akademik kembali pada siswa yang telah memiliki keterangan.',
            ], 422);
        } else {
            foreach ($explodeStudentIds as $studentId) {
                StudentSchoolClass::where('student_id', $studentId)->update([
                    'student_class_status' => 'inactive',
                    'academic_action' => 'TRANSFERRED_MAJOR',
                ]);
    
                StudentSchoolClass::create([
                    'student_id' => $studentId,
                    'school_class_id' => $request->school_class_id,
                    'major_id' => $majorId,
                    'student_class_status' => 'active',
                ]);
            }
        }

        broadcast(new LmsManagementStudentInClass('StudentSchoolClass', 'move-major', $studentSchoolClass))->toOthers();

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil memindahkan jurusan',
        ]);
    }
}