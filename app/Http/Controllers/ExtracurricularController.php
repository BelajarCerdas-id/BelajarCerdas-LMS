<?php

namespace App\Http\Controllers;

use App\Exports\AttendanceTemplateExport;
use App\Exports\ExtracurricularTemplateExport;
use App\Imports\AttendanceImport;
use App\Imports\ExtracurricularImport;
use App\Imports\ExtracurricularMemberImport;
use App\Models\Extracurricular;
use App\Models\ExtracurricularAttendance;
use App\Models\ExtracurricularMeeting;
use App\Models\ExtracurricularPeriod;
use App\Models\ExtracurricularSemester;
use App\Models\ExtracurricularStudent;
use App\Models\ExtracurricularValue;
use App\Models\SchoolPartner;
use App\Models\StudentProfile;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExtracurricularController extends Controller
{
    /**
     * Daftar ekstrakurikuler sekolah.
     */
public function index($role, $schoolName, $schoolId)
{
    $extracurriculars = Extracurricular::where(
        'school_partner_id',
        $schoolId
    )
    ->withCount('students')
    ->orderByRaw("CASE WHEN type = 'wajib' THEN 0 ELSE 1 END")
    ->orderBy('name')
    ->get();

    /*
    |--------------------------------------------------------------------------
    | TOTAL ANGGOTA
    |--------------------------------------------------------------------------
    | Satu siswa dihitung satu kali walaupun ikut beberapa ekskul.
    */
    $totalMember = ExtracurricularStudent::whereHas(
        'extracurricular',
        function ($q) use ($schoolId) {
            $q->where('school_partner_id', $schoolId);
        }
    )
    ->whereNotNull('student_profile_id')
    ->distinct('student_profile_id')
    ->count('student_profile_id');

    /*
    |--------------------------------------------------------------------------
    | TOTAL PERTEMUAN
    |--------------------------------------------------------------------------
    */
    $totalMeeting = ExtracurricularMeeting::whereHas(
        'extracurricular',
        function ($q) use ($schoolId) {
            $q->where('school_partner_id', $schoolId);
        }
    )->count();

    /*
    |--------------------------------------------------------------------------
    | SESI / PERIODE
    |--------------------------------------------------------------------------
    */
    $semesters = ExtracurricularPeriod::query()
        ->orderByDesc('is_active')
        ->orderByDesc('id')
        ->get();

    $activePeriod = $semesters->firstWhere(
        'is_active',
        true
    );

    $selectedPeriodId = request('period_id')
        ?: $activePeriod?->id;

    return view(
        'features.lms.student-vice-principal.extracurricular.manage-extracurricular',
        compact(
            'extracurriculars',
            'totalMember',
            'totalMeeting',
            'semesters',
            'activePeriod',
            'selectedPeriodId',
            'role',
            'schoolName',
            'schoolId'
        )
    );
}

public function memberKpi(
    Request $request,
    $role,
    $schoolName,
    $schoolId
) {
    try {

        /* =========================================================
           FILTER
        ========================================================= */

        $mode = $request->get('mode', 'all');

        $extracurricularId =
            $request->get('extracurricular_id');

        /*
         * Filter sekarang menggunakan TIPE KELAS.
         */
        $tipeKelasFilter =
            trim((string) $request->get('tipe_kelas', ''));


        /* =========================================================
           QUERY MEMBER
        ========================================================= */

        $memberQuery = ExtracurricularStudent::query()
            ->where('school_partner_id', $schoolId)
            ->where('status', 'active');


        /* =========================================================
           FILTER EKSTRAKURIKULER
        ========================================================= */

        if (
            $mode === 'extracurricular' &&
            !empty($extracurricularId)
        ) {

            $memberQuery->where(
                'extracurricular_id',
                $extracurricularId
            );

        }


        /* =========================================================
           FILTER TIPE KELAS
        ========================================================= */

        if ($tipeKelasFilter !== '') {

            $memberQuery->where(
                'tipe_kelas',
                $tipeKelasFilter
            );

        }


        /* =========================================================
           AMBIL MEMBER
        ========================================================= */

        $members = $memberQuery->get();


        /* =========================================================
           TOTAL SISWA YANG IKUT EKSKUL
        ========================================================= */

        $joinedStudentIds = $members
            ->pluck('student_profile_id')
            ->filter()
            ->unique()
            ->values();

        $joined = $joinedStudentIds->count();


        /*
         * Jika student_profile_id kosong,
         * gunakan jumlah record member.
         */
        if ($joined === 0 && $members->count() > 0) {
            $joined = $members->count();
        }


        /* =========================================================
           TOTAL SISWA SEKOLAH
        ========================================================= */

        $totalQuery = StudentProfile::query()
            ->where('school_partner_id', $schoolId);


        /*
         * Jika filter tipe kelas aktif,
         *
         * total siswa juga harus mengikuti
         * tipe kelas tersebut.
         *
         * Kita TIDAK menggunakan:
         *
         * StudentSchoolClass->SchoolClass
         *
         * karena relasinya collection.
         *
         * Untuk KPI ini kita gunakan member
         * yang memang sudah mempunyai tipe_kelas.
         */


        if ($tipeKelasFilter !== '') {

            /*
             * Ambil seluruh student_profile_id
             * yang mempunyai tipe kelas tersebut
             * berdasarkan data extracurricular_students.
             *
             * Ini mencegah error relationship collection.
             */

            $studentIdsForType = ExtracurricularStudent::query()
                ->where('school_partner_id', $schoolId)
                ->where('status', 'active')
                ->where(
                    'tipe_kelas',
                    $tipeKelasFilter
                )
                ->pluck('student_profile_id')
                ->filter()
                ->unique()
                ->values();


            if ($studentIdsForType->isEmpty()) {

                $total = 0;

            } else {

                $total = StudentProfile::query()
                    ->where('school_partner_id', $schoolId)
                    ->whereIn(
                        'id',
                        $studentIdsForType
                    )
                    ->count();

            }

        } else {

            $total = $totalQuery->count();

        }


        /* =========================================================
           PERSENTASE
        ========================================================= */

        $percentage = $total > 0
            ? round(
                ($joined / $total) * 100,
                2
            )
            : 0;


        /* =========================================================
           DISTRIBUSI BERDASARKAN TIPE KELAS
        ========================================================= */

        /*
         * INI BAGIAN UTAMA.
         *
         * Jangan lagi:
         *
         * groupBy('kelas')
         *
         * Tetapi:
         *
         * groupBy('tipe_kelas')
         */

        $classGroups = $members
            ->groupBy(function ($member) {

                $tipeKelas =
                    trim(
                        (string) $member->tipe_kelas
                    );

                return $tipeKelas !== ''
                    ? $tipeKelas
                    : 'Tidak Ada Tipe Kelas';

            });


        /* =========================================================
           DATA TIPE KELAS
        ========================================================= */

        $classes = $classGroups
            ->map(function (
                $classMembers,
                $tipeKelas
            ) {

                /*
                 * Hindari duplicate siswa
                 * dalam satu tipe kelas.
                 */

                $joinedIds = $classMembers
                    ->pluck('student_profile_id')
                    ->filter()
                    ->unique();


                $joinedCount =
                    $joinedIds->count();


                /*
                 * Fallback apabila
                 * student_profile_id kosong.
                 */

                if ($joinedCount === 0) {

                    $joinedCount =
                        $classMembers->count();

                }


                return [

                    /*
                     * FIELD UTAMA UNTUK JS
                     */
                    'tipe_kelas' =>
                        $tipeKelas,

                    /*
                     * Alias supaya kompatibel
                     * dengan kode lama.
                     */
                    'kelas' =>
                        $tipeKelas,

                    'joined' =>
                        $joinedCount,

                    'total' =>
                        $joinedCount,

                ];

            })
            ->sortBy('tipe_kelas')
            ->values();


        /* =========================================================
           DAFTAR EKSTRAKURIKULER
        ========================================================= */

        $extracurriculars =
            Extracurricular::query()
                ->where(
                    'school_partner_id',
                    $schoolId
                )
                ->where(
                    'status',
                    'active'
                )
                ->orderBy('name')
                ->get();


        /* =========================================================
           DAFTAR TIPE KELAS
        ========================================================= */

        $tipeKelasList = $members
            ->pluck('tipe_kelas')
            ->filter(function ($value) {

                return trim(
                    (string) $value
                ) !== '';

            })
            ->map(function ($value) {

                return trim(
                    (string) $value
                );

            })
            ->unique()
            ->sort()
            ->values();


        /* =========================================================
           RESPONSE
        ========================================================= */

        return response()->json([

            'status' =>
                'success',

            'mode' =>
                $mode,

            'joined' =>
                $joined,

            'total' =>
                $total,

            'percentage' =>
                $percentage,

            /*
             * DATA UTAMA CHART
             */
            'classes' =>
                $classes,

            /*
             * Data tipe kelas mentah
             */
            'tipe_kelas' =>
                $tipeKelasList,

            /*
             * Ekstrakurikuler
             */
            'extracurriculars' =>
                $extracurriculars
                    ->map(function (
                        $extracurricular
                    ) {

                        return [

                            'id' =>
                                $extracurricular->id,

                            'name' =>
                                $extracurricular->name,

                        ];

                    })
                    ->values(),

        ]);

    } catch (\Throwable $e) {

        Log::error(
            'MEMBER KPI ERROR',
            [

                'message' =>
                    $e->getMessage(),

                'file' =>
                    $e->getFile(),

                'line' =>
                    $e->getLine(),

                'school_id' =>
                    $schoolId,

                'mode' =>
                    $request->get('mode'),

                'extracurricular_id' =>
                    $request->get(
                        'extracurricular_id'
                    ),

                'tipe_kelas' =>
                    $request->get(
                        'tipe_kelas'
                    ),

            ]
        );


        return response()->json(
            [

                'status' =>
                    'error',

                'message' =>
                    $e->getMessage(),

            ],
            500
        );

    }
}
public function downloadMemberTemplate(
    $role,
    $schoolName,
    $schoolId,
    $extracurricularId
){
    return response()->download(
        public_path(
            'assets/template/ekskul/make_absen/Upload member.xlsx'
        ),
        'Upload Member.xlsx'
    );
}

public function importMember(
    Request $request,
    $role,
    $schoolName,
    $schoolId,
    $extracurricularId
){
    $request->validate([
        'excel_file' => 'required|mimes:xlsx,xls'
    ]);

    Excel::import(
        new ExtracurricularMemberImport(
            $schoolId,
            $extracurricularId
        ),
        $request->file('excel_file')
    );

    return response()->json([
        'status' => 'success',
        'message' => 'Import peserta berhasil.'
    ]);
}

    /**
     * Simpan ekstrakurikuler.
     */
   public function store(Request $request)
{
    $validator = Validator::make($request->all(), [

        'name' => 'required|string|max:255',
        'description' => 'nullable|string|max:500',
        'type' => 'required|in:wajib,pilihan',
        'coach' => 'nullable|string|max:255',

    ], [

        'name.required' => 'Nama ekstrakurikuler wajib diisi.',
        'type.required' => 'Tipe ekstrakurikuler wajib dipilih.',
        'type.in' => 'Tipe ekstrakurikuler tidak valid.',

    ]);

    if ($validator->fails()) {

        return response()->json([
            'status' => 'error',
            'errors' => $validator->errors()
        ], 422);

    }

    $school = SchoolPartner::findOrFail(
    request()->route('schoolId')
);

    $data = Extracurricular::create([

    'school_partner_id' => request()->route('schoolId'),

    'name' => $request->name,

    'description' => $request->description,

    'type' => $request->type,

    'coach' => $request->coach,

    'status' => 'active'

]);

    return response()->json([

    'status' => 'success',

    'message' => 'Ekstrakurikuler berhasil ditambahkan.',

    'data' => $data,

    'row' => view(
        'features.lms.student-vice-principal.extracurricular.components.row',
        [
            'data' => $data
        ]
    )->render()

]);
}

public function downloadTemplate()
{
    return response()->download(
        public_path('assets/template/ekskul/make_ekskul/Template_Ekstrakurikuler upload.xlsx'),
        'Template_Ekstrakurikuler.xlsx'
    );
}

public function importExcel(Request $request)
{

    $request->validate([
    'excel_file' => 'required|mimes:xlsx,xls'
    ]);

    Excel::import(
    new ExtracurricularImport($request->route('schoolId')),
    $request->file('excel_file')
);

    return response()->json([

        'status'=>'success',

        'message'=>'Import berhasil.'

    ]);

}   

    /**
     * Detail ekstrakurikuler.
     */
    public function show($id)
    {
        $extracurricular = Extracurricular::with([
            'students.studentProfile',
            'meetings'
        ])->findOrFail($id);

        $jumlahSiswa = $extracurricular->students()->count();

        $jumlahPertemuan = $extracurricular->meetings()->count();

        return view(
            'features.lms.administrator.extracurricular.detail',
            compact(
                'extracurricular',
                'jumlahSiswa',
                'jumlahPertemuan'
            )
        );
    }

    /**
     * Update ekstrakurikuler.
     */
    public function update(Request $request,$id)
    {
        $validator = Validator::make($request->all(),[
            'name'=>'required|string|max:255',
            'coach'=>'nullable|string|max:255',
            'status'=>'required|in:active,inactive'
        ]);

        if($validator->fails()){
            return response()->json([
                'status'=>'error',
                'errors'=>$validator->errors()
            ],422);
        }

        $data = Extracurricular::findOrFail($id);

        $data->update([
            'name'=>$request->name,
            'coach'=>$request->coach,
            'status'=>$request->status
        ]);

        return response()->json([
            'status'=>'success',
            'message'=>'Data berhasil diperbarui.'
        ]);
    }

    /**
     * Hapus ekstrakurikuler.
     */
    public function destroy(
    $role,
    $schoolName,
    $schoolId,
    $id
)
{
    $data = Extracurricular::findOrFail($id);

    $data->delete();

    return response()->json([
        'status' => 'success',
        'message' => 'Data berhasil dihapus.'
    ]);
}

    /**
     * Halaman detail anggota.
     */
    public function members($id)
    {
        $extracurricular = Extracurricular::findOrFail($id);

        $members = ExtracurricularStudent::with('studentProfile')
            ->where('extracurricular_id',$id)
            ->get();

        return view(
            'features.lms.administrator.extracurricular.members',
            compact(
                'extracurricular',
                'members'
            )
        );
    }

    /**
     * Halaman absensi.
     */
   public function attendance($id)
{
    $extracurricular = Extracurricular::findOrFail($id);

    $members = ExtracurricularStudent::where(
        'extracurricular_id',
        $id
    )
    ->with('attendances')
    ->orderBy('student_name')
    ->get();

    $meetings = ExtracurricularMeeting::where(
        'extracurricular_id',
        $id
    )
    ->with('attendances')
    ->orderBy('meeting_number')
    ->get();

    return view(
        'features.lms.administrator.extracurricular.attendance',
        compact(
            'extracurricular',
            'members',
            'meetings'
        )
    );
}

public function saveAttendance(Request $request, $role, $schoolName, $schoolId, $extracurricularId)
{
    $request->validate([
        'student_profile_id' => 'required',
        'meeting_id' => 'required',
        'status' => 'required|in:present,absent',
    ]);

    $attendance = ExtracurricularAttendance::updateOrCreate(
        [
            'student_profile_id' => $request->student_profile_id,
            'meeting_id' => $request->meeting_id,
        ],
        [
            'status' => $request->status,
        ]
    );

    return response()->json([
        'success' => true,
        'message' => 'Absensi berhasil disimpan.',
        'data' => $attendance,
    ]);
}

public function detail(
    $role,
    $schoolName,
    $schoolId,
    $extracurricularId
) {
    /*
    |--------------------------------------------------------------------------
    | 1. EKSTRAKURIKULER
    |--------------------------------------------------------------------------
    | Pastikan ekstrakurikuler memang milik sekolah yang sedang dibuka.
    */

    $extracurricular = Extracurricular::where(
        'school_partner_id',
        $schoolId
    )->findOrFail($extracurricularId);


    /*
    |--------------------------------------------------------------------------
    | 2. MEMBER AKTIF
    |--------------------------------------------------------------------------
    | Hanya anggota yang saat ini masih terdaftar.
    */

    $members = ExtracurricularStudent::where(
        'extracurricular_id',
        $extracurricularId
    )
    ->orderBy('student_name')
    ->get();


    /*
    |--------------------------------------------------------------------------
    | 3. PERTEMUAN AKTIF
    |--------------------------------------------------------------------------
    */

    $meetings = ExtracurricularMeeting::where(
        'extracurricular_id',
        $extracurricularId
    )
    ->with('attendances')
    ->orderBy('meeting_date')
    ->get();


    /*
    |--------------------------------------------------------------------------
    | 4. SEMUA SISWA
    |--------------------------------------------------------------------------
    */

    $students = StudentProfile::where(
        'school_partner_id',
        $schoolId
    )
    ->orderBy('nama_lengkap')
    ->get();


    /*
    |--------------------------------------------------------------------------
    | 5. SEMUA PERIODE
    |--------------------------------------------------------------------------
    */

    $semesters = ExtracurricularPeriod::where(
        'extracurricular_id',
        $extracurricularId
    )
    ->orderByDesc('sequence')
    ->get();


    /*
    |--------------------------------------------------------------------------
    | 6. PERIODE AKTIF
    |--------------------------------------------------------------------------
    */

    $activePeriod = ExtracurricularPeriod::where(
        'extracurricular_id',
        $extracurricularId
    )
    ->where(
        'is_active',
        true
    )
    ->orderByDesc('sequence')
    ->first();


    /*
    |--------------------------------------------------------------------------
    | 7. PERIODE YANG DIPILIH
    |--------------------------------------------------------------------------
    |
    | Jika URL:
    |
    | ?period_id=1
    |
    | maka periode tersebut yang digunakan.
    |
    | Jika tidak ada:
    | gunakan periode aktif.
    |
    */

    $selectedPeriodId = request()->query(
        'period_id',
        $activePeriod?->id
    );


    /*
    |--------------------------------------------------------------------------
    | 8. AMBIL PERIODE TERPILIH
    |--------------------------------------------------------------------------
    */

    $selectedPeriod = null;

    if ($selectedPeriodId) {

        $selectedPeriod = ExtracurricularPeriod::where(
            'extracurricular_id',
            $extracurricularId
        )
        ->where(
            'id',
            $selectedPeriodId
        )
        ->first();
    }


    /*
    |--------------------------------------------------------------------------
    | 9. FALLBACK PERIODE
    |--------------------------------------------------------------------------
    */

    if (!$selectedPeriod) {

        $selectedPeriod = $activePeriod;

        $selectedPeriodId =
            $activePeriod?->id;
    }


    /*
    |--------------------------------------------------------------------------
    | 10. NILAI
    |--------------------------------------------------------------------------
    |
    | Nilai arsip dicari berdasarkan period_id.
    |
    | Jangan membatasi berdasarkan member aktif karena member lama
    | memang dapat sudah dihapus setelah nilai berhasil diupload.
    |
    */

    $nilai = collect();

    if ($selectedPeriodId) {

        $nilai = ExtracurricularValue::where(
            'period_id',
            $selectedPeriodId
        )
        ->orderBy(
            'student_name'
        )
        ->get();
    }


    /*
    |--------------------------------------------------------------------------
    | 11. STATUS LOCK NILAI
    |--------------------------------------------------------------------------
    |
    | Jika nilai sudah pernah diupload pada periode tersebut,
    | maka siklus nilai dianggap terkunci.
    |
    */

    $nilaiCycleLocked = false;

    if ($selectedPeriod) {

        $nilaiCycleLocked =
            !is_null(
                $selectedPeriod->nilai_uploaded_at
            );
    }


    /*
    |--------------------------------------------------------------------------
    | 12. TOTAL MEMBER
    |--------------------------------------------------------------------------
    |
    | Hanya anggota yang saat ini masih terdaftar.
    |
    */

    $totalMember = $members->count();


    /*
    |--------------------------------------------------------------------------
    | 13. TOTAL PERTEMUAN
    |--------------------------------------------------------------------------
    |
    | Hanya meeting milik ekstrakurikuler ini.
    |
    */

    $totalMeeting = $meetings->count();


    /*
    |--------------------------------------------------------------------------
    | 14. ID SISWA AKTIF
    |--------------------------------------------------------------------------
    |
    | Ambil student_profile_id dari anggota yang MASIH terdaftar.
    |
    | Ini penting agar attendance siswa yang sudah dihapus tidak
    | ikut dihitung lagi.
    |
    */

    $studentProfileIds = $members
        ->pluck('student_profile_id')
        ->filter()
        ->unique()
        ->values();


    /*
    |--------------------------------------------------------------------------
    | 15. ID MEETING
    |--------------------------------------------------------------------------
    */

    $meetingIds = $meetings
        ->pluck('id')
        ->values();


    /*
    |--------------------------------------------------------------------------
    | 16. TOTAL SLOT ABSENSI
    |--------------------------------------------------------------------------
    |
    | Rumus:
    |
    | jumlah peserta × jumlah pertemuan
    |
    | Contoh:
    |
    | 3 peserta × 9 pertemuan = 27 slot absensi
    |
    | Walaupun ada attendance record yang belum dibuat,
    | slot tersebut tetap dianggap tidak hadir.
    |
    */

    $totalAttendance =
        $totalMember * $totalMeeting;


    /*
    |--------------------------------------------------------------------------
    | 17. TOTAL HADIR
    |--------------------------------------------------------------------------
    |
    | Hanya menghitung:
    |
    | 1. Siswa yang masih menjadi anggota
    | 2. Meeting milik ekstrakurikuler ini
    | 3. Status = present
    |
    | Jadi attendance siswa yang sudah dihapus tidak akan dihitung.
    |
    */

    $totalPresent = 0;

    if (
        $studentProfileIds->isNotEmpty()
        && $meetingIds->isNotEmpty()
    ) {

        $totalPresent =
            ExtracurricularAttendance::whereIn(
                'student_profile_id',
                $studentProfileIds
            )
            ->whereIn(
                'meeting_id',
                $meetingIds
            )
            ->where(
                'status',
                'present'
            )
            ->count();
    }


    /*
    |--------------------------------------------------------------------------
    | 18. AMANKAN TOTAL HADIR
    |--------------------------------------------------------------------------
    |
    | Secara normal totalPresent tidak mungkin lebih besar dari
    | totalAttendance.
    |
    | Tetapi clamp ini menjadi pengaman tambahan jika ada duplicate
    | attendance record atau kondisi data yang tidak normal.
    |
    */

    $totalPresent = min(
        $totalPresent,
        $totalAttendance
    );


    /*
    |--------------------------------------------------------------------------
    | 19. PERSENTASE KEHADIRAN
    |--------------------------------------------------------------------------
    |
    | Rumus:
    |
    | total hadir
    | ----------------------------- × 100
    | peserta × pertemuan
    |
    | Contoh:
    |
    | 3 peserta
    | 9 pertemuan
    |
    | 3 × 9 = 27
    |
    | Jika semua hadir:
    |
    | 27 / 27 × 100 = 100%
    |
    */

    $attendancePercent =
        $totalAttendance > 0

            ? round(
                (
                    $totalPresent /
                    $totalAttendance
                ) * 100,
                2
            )

            : 0;


    /*
    |--------------------------------------------------------------------------
    | 20. RETURN VIEW
    |--------------------------------------------------------------------------
    */

    return view(
        'features.lms.student-vice-principal.extracurricular.manage-extracurricular-detail',
        compact(

            'role',

            'schoolName',

            'schoolId',

            'extracurricularId',

            'extracurricular',

            'members',

            'meetings',

            'students',

            'semesters',

            'activePeriod',

            'selectedPeriod',

            'selectedPeriodId',

            'nilai',

            'nilaiCycleLocked',

            'totalMember',

            'totalMeeting',

            'totalAttendance',

            'totalPresent',

            'attendancePercent'
        )
    );
}

public function kelengkapanView(
    $role,
    $schoolName,
    $schoolId
) {

    $extracurriculars = Extracurricular::where(
    'school_partner_id',
    $schoolId
)
->withCount('students')
->orderByRaw("type = 'wajib' DESC")
->orderBy('name')
->get();

    $totalMember = ExtracurricularStudent::whereHas(
        'extracurricular',
        function ($q) use ($schoolId) {
            $q->where('school_partner_id', $schoolId);
        }
    )->count();

    $totalMeeting = ExtracurricularMeeting::whereHas(
        'extracurricular',
        function ($q) use ($schoolId) {
            $q->where('school_partner_id', $schoolId);
        }
    )->count();

    return view(
        'features.lms.student-vice-principal.extracurricular.kelengkapan.kelengkapan-extraculikuler',
        compact(
            'extracurriculars',
            'totalMember',
            'totalMeeting',
            'role',
            'schoolName',
            'schoolId'
        )
    );
}

public function updateDetail(
    Request $request,
    $role,
    $schoolName,
    $schoolId,
    $extracurricularId
)
{
    $request->validate([
        'name' => 'required|max:255',
        'description' => 'nullable',
        'coach' => 'nullable|max:255',
        'type' => 'required|in:wajib,pilihan'
    ]);

    $extracurricular = Extracurricular::where(
        'school_partner_id',
        $schoolId
    )->findOrFail($extracurricularId);

    $extracurricular->update([

        'name' => $request->name,

        'description' => $request->description,

        'coach' => $request->coach,

        'type' => $request->type

    ]);

    return response()->json([

        'status'=>'success',

        'message'=>'Data berhasil diperbarui.'

    ]);
}

public function storeMeeting(
    Request $request,
    $role,
    $schoolName,
    $schoolId,
    $extracurricularId
)
{
    $request->validate([

        'title'=>'required|max:255',

        'meeting_date'=>'required|date',

        'description'=>'nullable'

    ]);

    $meeting = ExtracurricularMeeting::create([

        'extracurricular_id'=>$extracurricularId,

        'title'=>$request->title,

        'meeting_date'=>$request->meeting_date,

        'description'=>$request->description

    ]);

    return response()->json([

        'status'=>'success',

        'message'=>'Pertemuan berhasil ditambahkan.',

        'data'=>$meeting

    ]);
}

public function updateMeeting(
    Request $request,
    $role,
    $schoolName,
    $schoolId,
    $extracurricularId,
    $meetingId
)
{
    $meeting = ExtracurricularMeeting::where(
        'extracurricular_id',
        $extracurricularId
    )->findOrFail($meetingId);

    $meeting->update([

        'title'=>$request->title,

        'meeting_date'=>$request->meeting_date,

        'description'=>$request->description

    ]);

    return response()->json([

        'status'=>'success',

        'message'=>'Pertemuan berhasil diperbarui.'

    ]);
}

public function destroyMeeting(
    $role,
    $schoolName,
    $schoolId,
    $extracurricularId,
    $meetingId
)
{
    ExtracurricularMeeting::where(
        'extracurricular_id',
        $extracurricularId
    )
    ->findOrFail($meetingId)
    ->delete();

    return response()->json([

        'status'=>'success',

        'message'=>'Pertemuan berhasil dihapus.'

    ]);
}

public function storeMember(
    Request $request,
    $role,
    $schoolName,
    $schoolId,
    $extracurricularId
) {
    /*
    |--------------------------------------------------------------------------
    | Validasi siswa
    |--------------------------------------------------------------------------
    */

    $request->validate([
        'student_profile_id' => 'required|exists:student_profiles,id'
    ]);


    /*
    |--------------------------------------------------------------------------
    | Pastikan ekstrakurikuler milik sekolah yang sedang dibuka
    |--------------------------------------------------------------------------
    */

    $extracurricular = Extracurricular::where(
        'school_partner_id',
        $schoolId
    )->findOrFail($extracurricularId);


    /*
    |--------------------------------------------------------------------------
    | Ambil data siswa beserta relasi kelas
    |--------------------------------------------------------------------------
    */

    $student = StudentProfile::with([
        'UserAccount.StudentSchoolClass.SchoolClass.Kelas'
    ])->findOrFail(
        $request->student_profile_id
    );


    /*
    |--------------------------------------------------------------------------
    | Ambil StudentSchoolClass
    |--------------------------------------------------------------------------
    */

    $studentSchoolClass = $student->UserAccount
        ?->StudentSchoolClass
        ?->first();


    /*
    |--------------------------------------------------------------------------
    | Ambil SchoolClass
    |--------------------------------------------------------------------------
    */

    $schoolClass = $studentSchoolClass?->SchoolClass;


    /*
    |--------------------------------------------------------------------------
    | Default data
    |--------------------------------------------------------------------------
    */

    $kelas = '';
    $tipeKelas = '';


    /*
    |--------------------------------------------------------------------------
    | Ambil KELAS
    |--------------------------------------------------------------------------
    |
    | Struktur yang digunakan:
    |
    | StudentProfile
    |    -> UserAccount
    |       -> StudentSchoolClass
    |          -> SchoolClass
    |             -> Kelas
    |
    */

    if ($schoolClass) {

        /*
        | Jika SchoolClass mempunyai relasi Kelas
        */

        if ($schoolClass->Kelas) {

            $kelas = $schoolClass->Kelas->kelas
                ?? $schoolClass->Kelas->nama
                ?? $schoolClass->Kelas->name
                ?? '';

        }

        /*
        | Jika ternyata kelas langsung berada di SchoolClass
        */

        if ($kelas === '') {

            $kelas = $schoolClass->kelas
                ?? $schoolClass->class
                ?? $schoolClass->class_name
                ?? '';

        }
    }


    /*
    |--------------------------------------------------------------------------
    | Ambil TIPE KELAS
    |--------------------------------------------------------------------------
    */

    if ($schoolClass) {

        $tipeKelas = $schoolClass->class_name
            ?? $schoolClass->tipe_kelas
            ?? $schoolClass->type
            ?? '';

    }


    /*
    |--------------------------------------------------------------------------
    | Cek apakah siswa sudah menjadi anggota
    |--------------------------------------------------------------------------
    */

    $exists = ExtracurricularStudent::where(
        'extracurricular_id',
        $extracurricularId
    )
    ->where(
        'student_profile_id',
        $student->id
    )
    ->exists();


    if ($exists) {

        return response()->json([
            'status' => 'warning',
            'message' => 'Peserta sudah terdaftar pada ekstrakurikuler ini.'
        ], 409);

    }


    /*
    |--------------------------------------------------------------------------
    | Simpan anggota ekstrakurikuler
    |--------------------------------------------------------------------------
    */

    $member = ExtracurricularStudent::create([

        'extracurricular_id' => $extracurricularId,

        'student_profile_id' => $student->id,

        'school_partner_id' => $schoolId,

        'student_name' => $student->nama_lengkap,

        'kelas' => $kelas,

        'tipe_kelas' => $tipeKelas,

        'status' => 'active'

    ]);


    /*
    |--------------------------------------------------------------------------
    | Response
    |--------------------------------------------------------------------------
    */

    return response()->json([

        'status' => 'success',

        'message' => 'Anggota berhasil ditambahkan.',

        'data' => [

            'id' => $member->id,

            'student_profile_id' => $student->id,

            'nama' => $student->nama_lengkap,

            'nisn' => $student->nisn,

            'kelas' => $kelas,

            'tipe_kelas' => $tipeKelas

        ]

    ]);
}
    public function destroyMember(
        $role,
        $schoolName,
        $schoolId,
        $extracurricularId,
        $memberId
    )
    {
        $member = ExtracurricularStudent::where(
            'extracurricular_id',
            $extracurricularId
        )->findOrFail($memberId);

        $member->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Anggota berhasil dihapus.'
        ]);
    }

public function uploadMember(
    Request $request,
    $role,
    $schoolName,
    $schoolId,
    $extracurricularId
)
{
    $request->validate([
        'excel_file' => 'required|mimes:xlsx,xls'
    ]);

    Excel::import(

        new ExtracurricularMemberImport(
            $schoolId,
            $extracurricularId
        ),

        $request->file('excel_file')

    );

    return response()->json([

        'status'=>'success',

        'message'=>'Peserta berhasil diimport.'

    ]);
}

public function downloadAttendanceTemplate(
    $role,
    $schoolName,
    $schoolId,
    $extracurricularId
) {
    /*
    |--------------------------------------------------------------------------
    | Pastikan ekstrakurikuler milik sekolah yang sedang dibuka
    |--------------------------------------------------------------------------
    */

    $extracurricular = Extracurricular::where(
        'school_partner_id',
        $schoolId
    )->findOrFail($extracurricularId);


    /*
    |--------------------------------------------------------------------------
    | Lokasi template
    |--------------------------------------------------------------------------
    */

    $template = public_path(
        'assets/template/ekskul/list_absen/list absen.xlsx'
    );

    if (!file_exists($template)) {
        abort(404, 'Template tidak ditemukan.');
    }


    /*
    |--------------------------------------------------------------------------
    | Load template Excel
    |--------------------------------------------------------------------------
    */

    $spreadsheet = IOFactory::load($template);

    $sheet = $spreadsheet->getActiveSheet();


    /*
    |--------------------------------------------------------------------------
    | Ambil semua anggota ekstrakurikuler
    |--------------------------------------------------------------------------
    */

    $members = ExtracurricularStudent::with([
        'studentProfile'
    ])
    ->where('extracurricular_id', $extracurricularId)
    ->orderBy('student_name')
    ->get();


    /*
    |--------------------------------------------------------------------------
    | Ambil semua pertemuan
    |--------------------------------------------------------------------------
    */

    $meetings = ExtracurricularMeeting::where(
        'extracurricular_id',
        $extracurricularId
    )
    ->orderBy('meeting_date')
    ->get();


    /*
    |--------------------------------------------------------------------------
    | Ambil semua data absensi yang sudah tersimpan
    |--------------------------------------------------------------------------
    |
    | Key:
    | meeting_id + student_profile_id
    |
    */

    $attendances = ExtracurricularAttendance::whereIn(
        'meeting_id',
        $meetings->pluck('id')
    )
    ->get()
    ->keyBy(function ($attendance) {
        return $attendance->meeting_id
            . '_'
            . $attendance->student_profile_id;
    });


    /*
    |--------------------------------------------------------------------------
    | HEADER ABSENSI
    |
    | A = Nama
    | B = NISN
    | C = Kelas
    | D = Tipe Kelas
    | E dst = Tanggal pertemuan
    |--------------------------------------------------------------------------
    */

    $column = 5; // E

    foreach ($meetings as $meeting) {

        $letter = Coordinate::stringFromColumnIndex(
            $column
        );

        $tanggal = Carbon::parse(
            $meeting->meeting_date
        )->format('d/m/Y');

        $sheet->setCellValue(
            $letter . '2',
            $tanggal
        );

        $column++;
    }


    /*
    |--------------------------------------------------------------------------
    | ISI DATA MEMBER
    |--------------------------------------------------------------------------
    */

    $row = 3;

    foreach ($members as $member) {

        /*
        |--------------------------------------------------------------------------
        | NAMA
        |--------------------------------------------------------------------------
        */

        $nama = $member->studentProfile?->nama_lengkap
            ?? $member->student_name
            ?? '';


        /*
        |--------------------------------------------------------------------------
        | NISN
        |--------------------------------------------------------------------------
        */

        $nisn = $member->studentProfile?->nisn
            ?? '';


        /*
        |--------------------------------------------------------------------------
        | KELAS
        |--------------------------------------------------------------------------
        */

        $kelas = $member->kelas
            ?? '';


        /*
        |--------------------------------------------------------------------------
        | TIPE KELAS
        |--------------------------------------------------------------------------
        */

        $tipeKelas = $member->tipe_kelas
            ?? '';


        /*
        |--------------------------------------------------------------------------
        | MASUKKAN DATA MEMBER KE EXCEL
        |--------------------------------------------------------------------------
        */

        $sheet->setCellValue(
            "A{$row}",
            $nama
        );

        $sheet->setCellValue(
            "B{$row}",
            $nisn
        );

        $sheet->setCellValue(
            "C{$row}",
            $kelas
        );

        $sheet->setCellValue(
            "D{$row}",
            $tipeKelas
        );


        /*
        |--------------------------------------------------------------------------
        | ISI ABSENSI YANG SUDAH TERSIMPAN
        |--------------------------------------------------------------------------
        |
        | H     = Hadir
        | kosong = Tidak hadir
        |
        */

        $attendanceColumn = 5;

        foreach ($meetings as $meeting) {

            $letter = Coordinate::stringFromColumnIndex(
                $attendanceColumn
            );

            /*
            |--------------------------------------------------------------------------
            | Default kosong
            |--------------------------------------------------------------------------
            */

            $status = '';


            /*
            |--------------------------------------------------------------------------
            | Cari data absensi berdasarkan:
            |
            | meeting_id
            | student_profile_id
            |--------------------------------------------------------------------------
            */

            if ($member->studentProfile) {

                $attendance = $attendances->get(
                    $meeting->id
                    . '_'
                    . $member->studentProfile->id
                );


                /*
                |--------------------------------------------------------------------------
                | Jika status database = present
                | maka Excel diisi H
                |--------------------------------------------------------------------------
                */

                if (
                    $attendance
                    && $attendance->status === 'present'
                ) {
                    $status = 'H';
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Masukkan status ke Excel
            |--------------------------------------------------------------------------
            */

            $sheet->setCellValue(
                $letter . $row,
                $status
            );

            $attendanceColumn++;
        }

        $row++;
    }


    /*
    |--------------------------------------------------------------------------
    | Jika belum ada member
    |--------------------------------------------------------------------------
    */

    if ($members->count() === 0) {

        $sheet->setCellValue(
            'A3',
            'Belum ada peserta ekstrakurikuler.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Jika belum ada pertemuan
    |--------------------------------------------------------------------------
    */

    if ($meetings->count() === 0) {

        $sheet->setCellValue(
            'E2',
            'Belum ada pertemuan'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Simpan file sementara
    |--------------------------------------------------------------------------
    */

    $filename = 'Template Absensi.xlsx';

    $tempFile = storage_path(
        'app/' . uniqid() . '_' . $filename
    );


    /*
    |--------------------------------------------------------------------------
    | Write Excel
    |--------------------------------------------------------------------------
    */

    $writer = IOFactory::createWriter(
        $spreadsheet,
        'Xlsx'
    );

    $writer->save($tempFile);


    /*
    |--------------------------------------------------------------------------
    | Download
    |--------------------------------------------------------------------------
    */

    return response()
        ->download(
            $tempFile,
            $filename
        )
        ->deleteFileAfterSend(true);
}

public function importAttendance(
    Request $request,
    $role,
    $schoolName,
    $schoolId,
    $extracurricularId
)
{
    $request->validate([
        'file' => 'required|mimes:xlsx,xls'
    ]);

    $spreadsheet = IOFactory::load(
        $request->file('file')->getRealPath()
    );

    $sheet = $spreadsheet->getActiveSheet();

    $highestRow = $sheet->getHighestRow();

    $highestColumn = Coordinate::columnIndexFromString(
        $sheet->getHighestColumn()
    );

    /*
    |--------------------------------------------------------------------------
    | Ambil / Buat Meeting dari Header Excel
    |--------------------------------------------------------------------------
    */

    $meetings = [];

    for ($col = 5; $col <= $highestColumn; $col++) {

        $cell = $sheet->getCellByColumnAndRow($col, 2);

        if (trim((string) $cell->getValue()) == '') {
            continue;
        }

        try {

            if (is_numeric($cell->getValue())) {

                $tanggal = Carbon::instance(
                    \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject(
                        $cell->getValue()
                    )
                )->format('Y-m-d');

            } else {

                $tanggal = Carbon::createFromFormat(
                    'd/m/Y',
                    trim($cell->getFormattedValue())
                )->format('Y-m-d');

            }

        } catch (\Exception $e) {

            continue;

        }

        $meeting = ExtracurricularMeeting::where(
            'extracurricular_id',
            $extracurricularId
        )
        ->whereDate('meeting_date', $tanggal)
        ->first();

        if (!$meeting) {

            $meetingNumber = (
                ExtracurricularMeeting::where(
                    'extracurricular_id',
                    $extracurricularId
                )->max('meeting_number') ?? 0
            ) + 1;

            $meeting = ExtracurricularMeeting::create([
                'extracurricular_id' => $extracurricularId,
                'meeting_number'     => $meetingNumber,
                'meeting_date'       => $tanggal,
            ]);

        }

        $meetings[$col] = $meeting;
    }

    if (count($meetings) == 0) {

        return response()->json([
            'status' => 'error',
            'message' => 'Template tidak memiliki data tanggal pertemuan.'
        ], 422);

    }

    /*
    |--------------------------------------------------------------------------
    | Import Absensi
    |--------------------------------------------------------------------------
    */

    for ($row = 3; $row <= $highestRow; $row++) {

        $nisn = trim(
            (string) $sheet->getCell("B{$row}")->getValue()
        );

        if ($nisn == '') {
            continue;
        }

        $student = StudentProfile::where(
            'nisn',
            $nisn
        )->first();

        if (!$student) {
            continue;
        }

        foreach ($meetings as $column => $meeting) {

            $value = strtoupper(
                trim(
                    (string) $sheet
                        ->getCellByColumnAndRow($column, $row)
                        ->getValue()
                )
            );

            switch ($value) {

            case 'H':
                $status = 'present';
                break;

            case 'I':
                $status = 'permission';
                break;

            case 'S':
                $status = 'sick';
                break;

            default:
                $status = 'absent';
                break;
        }

            ExtracurricularAttendance::updateOrCreate(

                [
                    'meeting_id' => $meeting->id,
                    'student_profile_id' => $student->id,
                ],

                [
                    'status' => $status,
                ]

            );
        }
    }

    return response()->json([
        'status' => 'success',
        'message' => 'Absensi berhasil diimport.'
    ]);
}
public function kelengkapanDetail(
    $role,
    $schoolName,
    $schoolId,
    $extracurricularId
) {
    return view(
        'features.lms.student-vice-principal.extracurricular.kelengkapan.kelengkapan-extrakulikuler-detail',
        compact(
            'role',
            'schoolName',
            'schoolId',
            'extracurricularId'
        )
    );
}

public function paginate($role, $schoolName, $schoolId)
{
    $data = Extracurricular::where(
    'school_partner_id',
    $schoolId
)
->withCount('students')
->orderByRaw("CASE WHEN type = 'wajib' THEN 0 ELSE 1 END")
->orderBy('name')
->paginate(10);

    return response()->json([
        'data' => $data->items(),
        'links' => (string) $data->links(),
        'current_page' => $data->currentPage(),
        'detailRoute' => route(
            'lms.student-vice-principal.extracurricular-management.detail',
            [
                'role' => ':role',
                'schoolName' => ':schoolName',
                'schoolId' => ':schoolId',
                'extracurricularId' => ':extracurricularId'
            ]
        ),
        'kpi' => [
            'total_extracurricular' => Extracurricular::where('school_partner_id', $schoolId)->count(),
            'total_member' => ExtracurricularStudent::whereHas(
    'extracurricular',
    function ($q) use ($schoolId) {
        $q->where('school_partner_id', $schoolId);
    }
)
->distinct('student_profile_id')
->count('student_profile_id'),
            'total_meeting' => ExtracurricularMeeting::whereHas('extracurricular', function ($q) use ($schoolId) {
                $q->where('school_partner_id', $schoolId);
            })->count(),
        ]
    ]);
}


/**
 * KPI Detail Ekstrakurikuler
 */

public function kpi(
    $role,
    $schoolName,
    $schoolId,
    $extracurricularId
) {
    /*
    |--------------------------------------------------------------------------
    | Pastikan ekstrakurikuler milik sekolah yang sedang dibuka
    |--------------------------------------------------------------------------
    */

    $extracurricular = Extracurricular::where(
        'school_partner_id',
        $schoolId
    )->findOrFail($extracurricularId);


    /*
    |--------------------------------------------------------------------------
    | Total anggota yang masih aktif
    |--------------------------------------------------------------------------
    */

    $members = ExtracurricularStudent::where(
        'extracurricular_id',
        $extracurricularId
    )->get();

    $totalMember = $members->count();


    /*
    |--------------------------------------------------------------------------
    | Total pertemuan
    |--------------------------------------------------------------------------
    */

    $meetings = ExtracurricularMeeting::where(
        'extracurricular_id',
        $extracurricularId
    )->get();

    $totalMeeting = $meetings->count();


    /*
    |--------------------------------------------------------------------------
    | ID siswa yang masih menjadi anggota
    |--------------------------------------------------------------------------
    |
    | Attendance siswa yang sudah dihapus tidak boleh ikut dihitung.
    |
    */

    $studentProfileIds = $members
        ->pluck('student_profile_id')
        ->filter()
        ->unique()
        ->values();


    /*
    |--------------------------------------------------------------------------
    | ID meeting
    |--------------------------------------------------------------------------
    */

    $meetingIds = $meetings
        ->pluck('id')
        ->values();


    /*
    |--------------------------------------------------------------------------
    | TOTAL ABSENSI / TOTAL SLOT
    |--------------------------------------------------------------------------
    |
    | Bukan jumlah record pada tabel attendance.
    |
    | Rumus:
    |
    | jumlah peserta × jumlah pertemuan
    |
    | Contoh:
    |
    | 3 peserta × 9 pertemuan = 27
    |
    */

    $totalAttendance =
        $totalMember * $totalMeeting;


    /*
    |--------------------------------------------------------------------------
    | TOTAL HADIR
    |--------------------------------------------------------------------------
    |
    | Hanya menghitung siswa yang:
    |
    | - masih menjadi anggota ekstrakurikuler
    | - berada pada meeting ekstrakurikuler ini
    | - status = present
    |
    */

    $totalPresent = 0;

    if (
        $studentProfileIds->isNotEmpty()
        && $meetingIds->isNotEmpty()
    ) {

        $totalPresent = ExtracurricularAttendance::whereIn(
            'student_profile_id',
            $studentProfileIds
        )
        ->whereIn(
            'meeting_id',
            $meetingIds
        )
        ->where(
            'status',
            'present'
        )
        ->count();
    }


    /*
    |--------------------------------------------------------------------------
    | Pengaman
    |--------------------------------------------------------------------------
    |
    | Total hadir tidak boleh lebih besar dari total slot.
    |
    */

    $totalPresent = min(
        $totalPresent,
        $totalAttendance
    );


    /*
    |--------------------------------------------------------------------------
    | PERSENTASE KEHADIRAN
    |--------------------------------------------------------------------------
    |
    | Rumus:
    |
    | total hadir
    | ------------------------------ × 100
    | jumlah peserta × jumlah pertemuan
    |
    */

    $attendancePercent = $totalAttendance > 0

        ? round(
            (
                $totalPresent /
                $totalAttendance
            ) * 100,
            2
        )

        : 0;


    /*
    |--------------------------------------------------------------------------
    | RESPONSE KPI
    |--------------------------------------------------------------------------
    */

    return response()->json([
        'total_member' => $totalMember,

        'total_meeting' => $totalMeeting,

        'total_attendance' => $totalAttendance,

        'attendance_percent' => $attendancePercent,
    ]);
}
public function downloadNilaiTemplate(
    $role,
    $schoolName,
    $schoolId,
    $extracurricularId
) {
    $extracurricular = Extracurricular::where(
        'school_partner_id',
        $schoolId
    )->findOrFail($extracurricularId);

    /*
    |--------------------------------------------------------------------------
    | Ambil periode aktif
    |--------------------------------------------------------------------------
    */

    $period = ExtracurricularPeriod::where(
        'extracurricular_id',
        $extracurricularId
    )
    ->where('is_active', true)
    ->first();

    /*
    |--------------------------------------------------------------------------
    | Kalau belum ada periode aktif, buat periode pertama
    |--------------------------------------------------------------------------
    */

    if (!$period) {

        $lastSequence = ExtracurricularPeriod::where(
            'extracurricular_id',
            $extracurricularId
        )->max('sequence') ?? 0;

        $period = ExtracurricularPeriod::create([
            'extracurricular_id' => $extracurricularId,
            'label' => 'Sesi ' . ($lastSequence + 1),
            'sequence' => $lastSequence + 1,
            'is_active' => true,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Tandai template sudah didownload
    |--------------------------------------------------------------------------
    */

    $period->update([
        'nilai_downloaded_at' => now(),
    ]);

    /*
    |--------------------------------------------------------------------------
    | Ambil peserta
    |--------------------------------------------------------------------------
    */

    $members = ExtracurricularStudent::where(
        'extracurricular_id',
        $extracurricularId
    )
    ->orderBy('student_name')
    ->get();

    /*
    |--------------------------------------------------------------------------
    | Jumlah pertemuan
    |--------------------------------------------------------------------------
    */

    $totalMeeting = ExtracurricularMeeting::where(
        'extracurricular_id',
        $extracurricularId
    )->count();

    /*
    |--------------------------------------------------------------------------
    | Template
    |--------------------------------------------------------------------------
    */

    $templatePath = public_path(
        'assets/template/ekskul/nilai/nilai_template.xlsx'
    );

    if (!file_exists($templatePath)) {
        abort(404, 'Template nilai tidak ditemukan.');
    }

    $spreadsheet = IOFactory::load($templatePath);

    $sheet = $spreadsheet->getActiveSheet();

    /*
    |--------------------------------------------------------------------------
    | Isi peserta
    |--------------------------------------------------------------------------
    */

    $row = 3;

    foreach ($members as $member) {

        $present = ExtracurricularAttendance::whereHas(
            'meeting',
            function ($query) use ($extracurricularId) {
                $query->where(
                    'extracurricular_id',
                    $extracurricularId
                );
            }
        )
        ->where(
            'student_profile_id',
            $member->student_profile_id
        )
        ->where(
            'status',
            'present'
        )
        ->count();

        $attendance = $present . ' / ' . $totalMeeting;

        $sheet->setCellValue(
            "A{$row}",
            $member->student_name
        );

        $studentProfile = StudentProfile::find(
            $member->student_profile_id
        );

        $sheet->setCellValue(
            "B{$row}",
            $studentProfile?->nisn ?? ''
        );

        $sheet->setCellValue(
            "C{$row}",
            $member->kelas ?? ''
        );

        $sheet->setCellValue(
            "D{$row}",
            $member->tipe_kelas ?: '-'
        );

        $sheet->setCellValue(
            "E{$row}",
            $attendance
        );

        // Kosong karena user harus mengisi
        $sheet->setCellValue("F{$row}", null);
        $sheet->setCellValue("G{$row}", null);

        $row++;
    }

    /*
    |--------------------------------------------------------------------------
    | Nama file
    |--------------------------------------------------------------------------
    */

    $safeName = preg_replace(
        '/[^A-Za-z0-9_\-]/',
        '_',
        $extracurricular->name ?? 'ekstrakurikuler'
    );

    $fileName =
        'nilai_' .
        $safeName .
        '_' .
        $period->label .
        '_' .
        now()->format('Ymd_His') .
        '.xlsx';

    /*
    |--------------------------------------------------------------------------
    | Temporary file
    |--------------------------------------------------------------------------
    */

    $tempPath = storage_path(
        'app/temp/' . $fileName
    );

    if (!is_dir(dirname($tempPath))) {
        mkdir(
            dirname($tempPath),
            0755,
            true
        );
    }

    $writer = IOFactory::createWriter(
        $spreadsheet,
        'Xlsx'
    );

    $writer->save($tempPath);

    return response()
        ->download(
            $tempPath,
            $fileName
        )
        ->deleteFileAfterSend(true);
}

public function uploadNilaiExcel(
    Request $request,
    $role,
    $schoolName,
    $schoolId,
    $extracurricularId
) {
    try {

        /*
        |--------------------------------------------------------------------------
        | 1. VALIDASI FILE
        |--------------------------------------------------------------------------
        */

        $request->validate([
            'file' => [
                'required',
                'file',
                'mimes:xlsx,xls',
                'max:10240',
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | 2. CEK EKSTRAKURIKULER
        |--------------------------------------------------------------------------
        */

        $extracurricular = Extracurricular::where(
            'school_partner_id',
            $schoolId
        )->findOrFail($extracurricularId);


        /*
        |--------------------------------------------------------------------------
        | 3. CARI PERIODE AKTIF
        |--------------------------------------------------------------------------
        */

        $period = ExtracurricularPeriod::where(
            'extracurricular_id',
            $extracurricularId
        )
            ->where('is_active', true)
            ->orderByDesc('sequence')
            ->first();


        /*
        |--------------------------------------------------------------------------
        | 4. JIKA BELUM ADA PERIODE
        |--------------------------------------------------------------------------
        */

        if (!$period) {

            $lastSequence =
                ExtracurricularPeriod::where(
                    'extracurricular_id',
                    $extracurricularId
                )->max('sequence') ?? 0;


            $period = ExtracurricularPeriod::create([

                'extracurricular_id' =>
                    $extracurricularId,

                'label' =>
                    'Sesi ' . ($lastSequence + 1),

                'sequence' =>
                    $lastSequence + 1,

                'is_active' =>
                    true,

                'nilai_downloaded_at' =>
                    null,

                'nilai_uploaded_at' =>
                    null,
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | 5. TEMPLATE HARUS SUDAH DIDOWNLOAD
        |--------------------------------------------------------------------------
        */

        if (!$period->nilai_downloaded_at) {

            return response()->json([
                'success' => false,

                'message' =>
                    'Silakan download template nilai terlebih dahulu.',
            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | 6. LOAD EXCEL
        |--------------------------------------------------------------------------
        */

        $file = $request->file('file');

        $spreadsheet = IOFactory::load(
            $file->getRealPath()
        );

        $sheet = $spreadsheet->getActiveSheet();

        $rows = $sheet->toArray(
            null,
            true,
            true,
            true
        );


        /*
        |--------------------------------------------------------------------------
        | 7. CEK DATA EXCEL
        |--------------------------------------------------------------------------
        */

        if (empty($rows)) {

            return response()->json([
                'success' => false,

                'message' =>
                    'File Excel kosong.',
            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | 8. CARI BARIS HEADER SECARA OTOMATIS
        |--------------------------------------------------------------------------
        |
        | Tidak lagi menganggap header selalu berada di baris 2.
        |
        | Sistem akan mencari header dari baris 1 sampai 10.
        |
        */

        $headerRowNumber = null;

        $headerMap = [];


        /*
        |--------------------------------------------------------------------------
        | 9. ALIAS HEADER
        |--------------------------------------------------------------------------
        |
        | Memungkinkan template memiliki nama seperti:
        |
        | Nama
        | Nama Siswa
        | Nama Peserta
        |
        | dan sebagainya.
        |
        */

        $headerAliases = [

            'nama' => [
                'nama',
                'nama siswa',
                'nama peserta',
                'nama lengkap',
                'nama lengkap siswa',
            ],

            'nisn' => [
                'nisn',
                'nomor nisn',
                'no nisn',
                'no. nisn',
            ],

            'kelas' => [
                'kelas',
                'kelas siswa',
                'kelas peserta',
            ],

            'tipe kelas' => [
                'tipe kelas',
                'tipe_kelas',
                'tipe-kelas',
                'jenis kelas',
            ],

            'jumlah absen' => [
                'jumlah absen',
                'jumlah_absen',
                'jumlah kehadiran',
                'absen',
                'total absen',
                'total_absen',
            ],

            'nilai' => [
                'nilai',
                'nilai akhir',
                'nilai ekstrakurikuler',
                'nilai ekskul',
                'hasil',
                'grade',
            ],

            'deskripsi' => [
                'deskripsi',
                'keterangan',
                'catatan',
                'deskripsi nilai',
            ],
        ];


        /*
        |--------------------------------------------------------------------------
        | 10. LOOP BARIS UNTUK MENCARI HEADER
        |--------------------------------------------------------------------------
        */

        $maxHeaderSearch =
            min(
                10,
                count($rows)
            );


        foreach ($rows as $rowNumber => $row) {

            /*
            | Hanya cari header di 10 baris pertama.
            */

            if ($rowNumber > $maxHeaderSearch) {
                break;
            }


            $tempMap = [];


            foreach ($row as $column => $value) {

                $normalized = strtolower(
                    trim(
                        preg_replace(
                            '/\s+/',
                            ' ',
                            (string) $value
                        )
                    )
                );


                /*
                | Hilangkan karakter tertentu.
                */

                $normalized =
                    str_replace(
                        [
                            '_',
                            '-',
                        ],
                        ' ',
                        $normalized
                    );


                $normalized =
                    preg_replace(
                        '/\s+/',
                        ' ',
                        $normalized
                    );


                if ($normalized === '') {
                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | COCOKKAN DENGAN ALIAS
                |--------------------------------------------------------------------------
                */

                foreach (
                    $headerAliases
                    as $canonical => $aliases
                ) {

                    foreach ($aliases as $alias) {

                        $aliasNormalized =
                            strtolower(
                                trim(
                                    preg_replace(
                                        '/\s+/',
                                        ' ',
                                        str_replace(
                                            [
                                                '_',
                                                '-',
                                            ],
                                            ' ',
                                            $alias
                                        )
                                    )
                                )
                            );


                        if (
                            $normalized ===
                            $aliasNormalized
                        ) {

                            $tempMap[$canonical] =
                                $column;

                            break 2;
                        }
                    }
                }
            }


            /*
            |--------------------------------------------------------------------------
            | CEK APAKAH HEADER LENGKAP
            |--------------------------------------------------------------------------
            */

            $requiredHeaders = [
                'nama',
                'nisn',
                'kelas',
                'tipe kelas',
                'jumlah absen',
                'nilai',
                'deskripsi',
            ];


            $allFound = true;


            foreach (
                $requiredHeaders
                as $required
            ) {

                if (
                    !isset(
                        $tempMap[$required]
                    )
                ) {

                    $allFound = false;

                    break;
                }
            }


            if ($allFound) {

                $headerRowNumber =
                    $rowNumber;

                $headerMap =
                    $tempMap;

                break;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | 11. HEADER TIDAK DITEMUKAN
        |--------------------------------------------------------------------------
        */

        if (
            !$headerRowNumber ||
            empty($headerMap)
        ) {

            /*
            | Ambil informasi header yang
            | ditemukan untuk debugging.
            */

            $debugRows = [];


            foreach (
                array_slice(
                    $rows,
                    0,
                    10,
                    true
                ) as $rowNumber => $row
            ) {

                $values = [];


                foreach ($row as $value) {

                    $value =
                        trim(
                            (string) $value
                        );


                    if ($value !== '') {
                        $values[] = $value;
                    }
                }


                if (!empty($values)) {

                    $debugRows[] =
                        'Baris '
                        . $rowNumber
                        . ': '
                        . implode(
                            ' | ',
                            $values
                        );
                }
            }


            return response()->json([
                'success' => false,

                'message' =>
                    'Header Excel tidak ditemukan. Pastikan file menggunakan template nilai yang benar.',

                'header_contoh' => [
                    'Nama',
                    'NISN',
                    'Kelas',
                    'Tipe Kelas',
                    'Jumlah Absen',
                    'Nilai',
                    'Deskripsi',
                ],

                'baris_excel' =>
                    $debugRows,
            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | 12. TOTAL PERTEMUAN
        |--------------------------------------------------------------------------
        */

        $totalPertemuan =
            ExtracurricularMeeting::where(
                'extracurricular_id',
                $extracurricularId
            )->count();


        /*
        |--------------------------------------------------------------------------
        | 13. TRANSACTION
        |--------------------------------------------------------------------------
        */

        DB::beginTransaction();


        $processed = 0;

        $failed = [];


        /*
        |--------------------------------------------------------------------------
        | 14. LOOP DATA EXCEL
        |--------------------------------------------------------------------------
        */

        foreach (
            $rows
            as $rowNumber => $row
        ) {

            /*
            | Skip semua baris sebelum
            | dan termasuk header.
            */

            if (
                $rowNumber <=
                $headerRowNumber
            ) {
                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | AMBIL DATA
            |--------------------------------------------------------------------------
            */

            $nama =
                trim(
                    (string) (
                        $row[
                            $headerMap['nama']
                        ] ?? ''
                    )
                );


            $nisn =
                trim(
                    (string) (
                        $row[
                            $headerMap['nisn']
                        ] ?? ''
                    )
                );


            $kelas =
                trim(
                    (string) (
                        $row[
                            $headerMap['kelas']
                        ] ?? ''
                    )
                );


            $tipeKelas =
                trim(
                    (string) (
                        $row[
                            $headerMap['tipe kelas']
                        ] ?? ''
                    )
                );


            $jumlahAbsen =
                trim(
                    (string) (
                        $row[
                            $headerMap['jumlah absen']
                        ] ?? ''
                    )
                );


            /*
            |--------------------------------------------------------------------------
            | NILAI
            |--------------------------------------------------------------------------
            |
            | NILAI BEBAS.
            |
            | Tidak boleh menggunakan numeric validation.
            |
            */

            $nilai =
                $row[
                    $headerMap['nilai']
                ] ?? null;


            if ($nilai !== null) {

                $nilai =
                    trim(
                        (string) $nilai
                    );
            }


            if ($nilai === '') {

                $nilai = null;
            }


            /*
            |--------------------------------------------------------------------------
            | DESKRIPSI
            |--------------------------------------------------------------------------
            */

            $deskripsi =
                trim(
                    (string) (
                        $row[
                            $headerMap['deskripsi']
                        ] ?? ''
                    )
                );


            /*
            |--------------------------------------------------------------------------
            | BARIS KOSONG
            |--------------------------------------------------------------------------
            */

            if (
                $nama === '' &&
                $nisn === ''
            ) {
                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | NORMALISASI NISN
            |--------------------------------------------------------------------------
            */

            $nisn =
                preg_replace(
                    '/\.0$/',
                    '',
                    $nisn
                );


            /*
            |--------------------------------------------------------------------------
            | CARI SISWA BERDASARKAN NISN
            |--------------------------------------------------------------------------
            */

            $studentProfile = null;


            if ($nisn !== '') {

                $studentProfile =
                    StudentProfile::where(
                        'nisn',
                        $nisn
                    )->first();
            }


            /*
            |--------------------------------------------------------------------------
            | FALLBACK NAMA
            |--------------------------------------------------------------------------
            */

            if (
                !$studentProfile &&
                $nama !== ''
            ) {

                $studentProfile =
                    StudentProfile::where(
                        'nama_lengkap',
                        $nama
                    )->first();
            }


            /*
            |--------------------------------------------------------------------------
            | SISWA TIDAK DITEMUKAN
            |--------------------------------------------------------------------------
            */

            if (!$studentProfile) {

                $failed[] = [

                    'row' =>
                        $rowNumber,

                    'nama' =>
                        $nama,

                    'nisn' =>
                        $nisn,

                    'message' =>
                        'Siswa tidak ditemukan.',
                ];

                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | CEK MEMBER
            |--------------------------------------------------------------------------
            */

            $member =
                ExtracurricularStudent::where(
                    'extracurricular_id',
                    $extracurricularId
                )
                    ->where(
                        'student_profile_id',
                        $studentProfile->id
                    )
                    ->first();


            /*
            |--------------------------------------------------------------------------
            | BUKAN MEMBER
            |--------------------------------------------------------------------------
            */

            if (!$member) {

                $failed[] = [

                    'row' =>
                        $rowNumber,

                    'nama' =>
                        $nama,

                    'nisn' =>
                        $nisn,

                    'message' =>
                        'Siswa bukan anggota ekstrakurikuler.',
                ];

                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | JUMLAH ABSEN
            |--------------------------------------------------------------------------
            */

            /*
|--------------------------------------------------------------------------
| JUMLAH ABSEN
|--------------------------------------------------------------------------
|
| Kita buat lebih fleksibel.
|
| Contoh yang diterima:
|
| 3
| 3.0
| "3"
| "3 kali"
| "Hadir 3"
|
| Kalau kosong -> 0
|
*/

$jumlahAbsenRaw = trim(
    (string) (
        $row[$headerMap['jumlah absen']] ?? ''
    )
);


$jumlahAbsen = 0;


if ($jumlahAbsenRaw !== '') {

    /*
    | Ambil angka pertama yang ditemukan.
    |
    | Contoh:
    | "3 kali"       -> 3
    | "Hadir 3"      -> 3
    | "3 pertemuan"  -> 3
    | "3.0"          -> 3
    */

    if (
        preg_match(
            '/\d+(?:[.,]\d+)?/',
            $jumlahAbsenRaw,
            $matches
        )
    ) {

        $jumlahAbsen = (float) str_replace(
            ',',
            '.',
            $matches[0]
        );

        $jumlahAbsen = (int) $jumlahAbsen;

    } else {

        $failed[] = [

            'row' =>
                $rowNumber,

            'nama' =>
                $nama,

            'nisn' =>
                $nisn,

            'message' =>
                "Jumlah absen '{$jumlahAbsenRaw}' tidak mengandung angka.",

        ];

        continue;
    }
}


/*
|--------------------------------------------------------------------------
| TIDAK BOLEH NEGATIF
|--------------------------------------------------------------------------
*/

if ($jumlahAbsen < 0) {

    $failed[] = [

        'row' =>
            $rowNumber,

        'nama' =>
            $nama,

        'nisn' =>
            $nisn,

        'message' =>
            'Jumlah absen tidak boleh negatif.',

    ];

    continue;
}


            /*
            |--------------------------------------------------------------------------
            | SIMPAN NILAI
            |--------------------------------------------------------------------------
            */

            ExtracurricularValue::updateOrCreate(

                [
                    'period_id' =>
                        $period->id,

                    'student_profile_id' =>
                        $studentProfile->id,
                ],

                [

                    'student_name' =>
                        $studentProfile->nama_lengkap
                        ?? $nama,

                    'nisn' =>
                        $studentProfile->nisn
                        ?? $nisn,

                    'kelas' =>
                        $kelas,

                    'tipe_kelas' =>
                        $tipeKelas,

                    'total_absen' =>
                        $jumlahAbsen,

                    'total_pertemuan' =>
                        $totalPertemuan,

                    /*
                    |--------------------------------------------------------------------------
                    | NILAI BEBAS
                    |--------------------------------------------------------------------------
                    */

                    'nilai' =>
                        $nilai,

                    'deskripsi' =>
                        $deskripsi !== ''
                            ? $deskripsi
                            : null,
                ]
            );


            $processed++;
        }


        /*
        |--------------------------------------------------------------------------
        | 15. TIDAK ADA DATA BERHASIL
        |--------------------------------------------------------------------------
        */

        if ($processed === 0) {

            DB::rollBack();


            $detail =
                collect($failed)
                    ->take(10)
                    ->map(
                        function ($item) {

                            return
                                "Baris {$item['row']}: "
                                . $item['message'];
                        }
                    )
                    ->implode(' | ');


            return response()->json([

                'success' =>
                    false,

                'message' =>
                    'Tidak ada data nilai yang berhasil diproses.'
                    . (
                        $detail
                            ? ' ' . $detail
                            : ''
                    ),

                'failed' =>
                    $failed,

            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | 16. TUTUP PERIODE LAMA
        |--------------------------------------------------------------------------
        */

        $period->update([

            'nilai_uploaded_at' =>
                now(),

            'is_active' =>
                false,
        ]);


        /*
        |--------------------------------------------------------------------------
        | 17. AMBIL MEETING
        |--------------------------------------------------------------------------
        */

        $meetingIds =
            ExtracurricularMeeting::where(
                'extracurricular_id',
                $extracurricularId
            )->pluck('id');


        /*
        |--------------------------------------------------------------------------
        | 18. HAPUS ATTENDANCE
        |--------------------------------------------------------------------------
        */

        if (
            $meetingIds->count() > 0
        ) {

            ExtracurricularAttendance::whereIn(
                'meeting_id',
                $meetingIds
            )->delete();
        }


        /*
        |--------------------------------------------------------------------------
        | 19. HAPUS MEETING
        |--------------------------------------------------------------------------
        */

        ExtracurricularMeeting::where(
            'extracurricular_id',
            $extracurricularId
        )->delete();


        /*
        |--------------------------------------------------------------------------
        | 20. HAPUS MEMBER
        |--------------------------------------------------------------------------
        */

        ExtracurricularStudent::where(
            'extracurricular_id',
            $extracurricularId
        )->delete();


        /*
        |--------------------------------------------------------------------------
        | 21. BUAT PERIODE BARU
        |--------------------------------------------------------------------------
        */

        $nextSequence =
            ((int) $period->sequence) + 1;


        $newPeriod =
            ExtracurricularPeriod::create([

                'extracurricular_id' =>
                    $extracurricularId,

                'label' =>
                    'Sesi ' . $nextSequence,

                'sequence' =>
                    $nextSequence,

                'is_active' =>
                    true,

                'nilai_downloaded_at' =>
                    null,

                'nilai_uploaded_at' =>
                    null,
            ]);


        /*
        |--------------------------------------------------------------------------
        | 22. COMMIT
        |--------------------------------------------------------------------------
        */

        DB::commit();


        /*
        |--------------------------------------------------------------------------
        | 23. PESAN
        |--------------------------------------------------------------------------
        */

        $message =
            "Upload nilai berhasil. "
            . "{$processed} data nilai berhasil disimpan ke "
            . "{$period->label}. "
            . "Data member, pertemuan, dan absensi telah dikosongkan. "
            . "Sesi baru {$newPeriod->label} telah dibuat.";


        if (
            count($failed) > 0
        ) {

            $message .=
                ' '
                . count($failed)
                . ' data tidak diproses.';
        }


        /*
        |--------------------------------------------------------------------------
        | 24. RESPONSE
        |--------------------------------------------------------------------------
        */

        return response()->json([

            'success' =>
                true,

            'message' =>
                $message,

            'uploaded_period_id' =>
                $period->id,

            'new_period_id' =>
                $newPeriod->id,

            'processed' =>
                $processed,

            'failed' =>
                $failed,

        ]);


    } catch (\Throwable $e) {


        /*
        |--------------------------------------------------------------------------
        | ROLLBACK
        |--------------------------------------------------------------------------
        */

        if (
            DB::transactionLevel() > 0
        ) {

            DB::rollBack();
        }


        /*
        |--------------------------------------------------------------------------
        | LOG
        |--------------------------------------------------------------------------
        */

        Log::error(
            'Upload nilai ekstrakurikuler gagal',
            [

                'error' =>
                    $e->getMessage(),

                'file' =>
                    $e->getFile(),

                'line' =>
                    $e->getLine(),

                'extracurricular_id' =>
                    $extracurricularId ?? null,
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | RESPONSE ERROR
        |--------------------------------------------------------------------------
        */

        return response()->json([

            'success' =>
                false,

            'message' =>
                'Upload nilai gagal: '
                . $e->getMessage(),

        ], 500);
    }
}

/**
 * ============================================================
 * KPI JUMLAH ANGGOTA
 * ============================================================
 */
/*
|--------------------------------------------------------------------------
| KPI ANGGOTA
|--------------------------------------------------------------------------
| Endpoint:
| GET /{extracurricularId}/kpi/members
|
| Fungsi:
| - Total siswa yang ikut ekskul
| - Daftar kelas
| - Jumlah siswa per kelas
| - Total siswa sekolah per kelas
| - Persentase 9/10, dst
| - Filter berdasarkan ekskul
| - Filter berdasarkan kelas
| - Filter berdasarkan sesi
|--------------------------------------------------------------------------
*/

public function kpiMembers(
    $role,
    $schoolName,
    $schoolId
) {
    $mode = request('mode', 'all');
    $extracurricularId = request('extracurricular_id');
    $kelasFilter = request('kelas');

    /*
    |--------------------------------------------------------------------------
    | EKSKUL AKTIF SEKOLAH
    |--------------------------------------------------------------------------
    */

    $extracurriculars = Extracurricular::where(
        'school_partner_id',
        $schoolId
    )
    ->where('status', 'active')
    ->orderBy('name')
    ->get();

    /*
    |--------------------------------------------------------------------------
    | MEMBER AKTIF
    |--------------------------------------------------------------------------
    |
    | Tidak menggunakan period_id / sesi.
    | Data yang digunakan adalah seluruh anggota yang saat ini
    | tercatat pada ekstrakurikuler aktif.
    |
    */

    $membersQuery = ExtracurricularStudent::query()
    ->whereIn(
        'extracurricular_id',
        $extracurriculars->pluck('id')
    )
    ->whereNotNull('student_profile_id')
    ->with([
        'studentProfile',
        'extracurricular'
    ]);

    /*
    |--------------------------------------------------------------------------
    | FILTER EKSKUL
    |--------------------------------------------------------------------------
    */

    if (
        !empty($extracurricularId) &&
        $extracurricularId !== 'all'
    ) {
        $membersQuery->where(
            'extracurricular_id',
            $extracurricularId
        );
    }

    /*
    |--------------------------------------------------------------------------
    | AMBIL MEMBER
    |--------------------------------------------------------------------------
    */

    $members = $membersQuery->get();

    /*
    |--------------------------------------------------------------------------
    | UNIQUE SISWA
    |--------------------------------------------------------------------------
    */

    $uniqueMembers = $members
        ->filter(function ($member) {
            return !empty($member->student_profile_id);
        })
        ->unique('student_profile_id')
        ->values();

    /*
    |--------------------------------------------------------------------------
    | HELPER AMBIL NAMA KELAS
    |--------------------------------------------------------------------------
    */

    $getKelas = function ($studentProfile) {

        if (!$studentProfile) {
            return null;
        }

        $kelas = $studentProfile->kelas;

        /*
        | Jika kelas adalah relationship/model
        */

        if (is_object($kelas)) {

            return $kelas->name
                ?? $kelas->nama
                ?? $kelas->kelas
                ?? $kelas->nama_kelas
                ?? null;
        }

        /*
        | Jika accessor/string
        */

        return $kelas;
    };

    /*
    |--------------------------------------------------------------------------
    | FILTER KELAS
    |--------------------------------------------------------------------------
    */

    if (
        !empty($kelasFilter) &&
        $kelasFilter !== 'all'
    ) {

        $uniqueMembers = $uniqueMembers
            ->filter(function ($member) use (
                $kelasFilter,
                $getKelas
            ) {

                $kelas = $getKelas(
                    $member->studentProfile
                );

                return (string) $kelas ===
                    (string) $kelasFilter;
            })
            ->values();
    }

    /*
    |--------------------------------------------------------------------------
    | DAFTAR KELAS
    |--------------------------------------------------------------------------
    |
    | JANGAN:
    |
    | StudentProfile::pluck('kelas')
    |
    | karena kelas bukan kolom langsung.
    |
    */

    $classes = $uniqueMembers
        ->map(function ($member) use ($getKelas) {

            return $getKelas(
                $member->studentProfile
            );

        })
        ->filter(function ($kelas) {

            return $kelas !== null &&
                $kelas !== '';
        })
        ->unique()
        ->sort()
        ->values();

    /*
    |--------------------------------------------------------------------------
    | DATA GRAFIK
    |--------------------------------------------------------------------------
    */

    $classData = $classes
        ->map(function ($kelas) use (
            $uniqueMembers,
            $getKelas
        ) {

            /*
            | Jumlah anggota ekskul pada kelas
            */

            $joined = $uniqueMembers
                ->filter(function ($member) use (
                    $kelas,
                    $getKelas
                ) {

                    $studentKelas =
                        $getKelas(
                            $member->studentProfile
                        );

                    return (string) $studentKelas ===
                        (string) $kelas;
                })
                ->unique('student_profile_id')
                ->count();

            /*
            | Total seluruh siswa sekolah pada kelas tersebut
            */

            $total = StudentProfile::query()
                ->with('kelas')
                ->get()
                ->filter(function ($student) use (
                    $kelas,
                    $getKelas
                ) {

                    $studentKelas =
                        $getKelas($student);

                    return (string) $studentKelas ===
                        (string) $kelas;
                })
                ->count();

            $percentage =
                $total > 0
                    ? round(
                        ($joined / $total) * 100,
                        2
                    )
                    : 0;

            return [

                'kelas' =>
                    $kelas,

                'joined' =>
                    $joined,

                'member' =>
                    $joined,

                'jumlah' =>
                    $joined,

                'total_member' =>
                    $joined,

                'total' =>
                    $total,

                'total_siswa' =>
                    $total,

                'persentase' =>
                    $percentage,

                'percentage' =>
                    $percentage,

                'label' =>
                    $joined . '/' . $total,
            ];

        })
        ->values();

    /*
    |--------------------------------------------------------------------------
    | TOTAL SISWA SEKOLAH
    |--------------------------------------------------------------------------
    */

    $totalStudent =
        $kelasFilter &&
        $kelasFilter !== 'all'

            ? StudentProfile::query()
                ->with('kelas')
                ->get()
                ->filter(function ($student) use (
                    $kelasFilter,
                    $getKelas
                ) {

                    return (string)
                        $getKelas($student)
                        ===
                        (string)
                        $kelasFilter;

                })
                ->count()

            : StudentProfile::count();

    /*
    |--------------------------------------------------------------------------
    | RESPONSE
    |--------------------------------------------------------------------------
    */

    return response()->json([

        'success' => true,

        /*
        | Tidak ada period_id lagi
        */

        'joined' =>
            $uniqueMembers->count(),

        'total_member' =>
            $uniqueMembers->count(),

        'total_members' =>
            $uniqueMembers->count(),

        'total' =>
            $totalStudent,

        'total_student' =>
            $totalStudent,

        'classes' =>
            $classData,

        'class_data' =>
            $classData,

        'classes_options' =>
            $classes,

        'extracurriculars' =>
            $extracurriculars
                ->map(function ($item) {

                    return [
                        'id' =>
                            $item->id,

                        'name' =>
                            $item->name,
                    ];

                })
                ->values(),

        /*
        | Data chart langsung
        */

        'chart' => [

            'labels' =>
                $classData
                    ->pluck('kelas')
                    ->values(),

            'members' =>
                $classData
                    ->pluck('jumlah')
                    ->values(),

            'total' =>
                $classData
                    ->pluck('total_siswa')
                    ->values(),

            'percentage' =>
                $classData
                    ->pluck('percentage')
                    ->values(),
        ],
    ]);
}


/*
|--------------------------------------------------------------------------
| KPI ANGGOTA BERDASARKAN FILTER
|--------------------------------------------------------------------------
|
| Filter:
|
| period_id
| extracurricular_id
| kelas
|
|--------------------------------------------------------------------------
*/

public function kpiMembersFilter(
    Request $request,
    $role,
    $schoolName,
    $schoolId,
    $extracurricularId = null
) {
    $periodId = $request->period_id;

    $filterExtracurricular =
        $request->extracurricular_id;

    $filterKelas =
        $request->kelas;

    /*
    |--------------------------------------------------------------------------
    | EKSKUL SEKOLAH
    |--------------------------------------------------------------------------
    */

    $extracurriculars = Extracurricular::where(
        'school_partner_id',
        $schoolId
    )
    ->where('status', 'active')
    ->orderBy('name')
    ->get();

    /*
    |--------------------------------------------------------------------------
    | MEMBER
    |--------------------------------------------------------------------------
    */

    $query = ExtracurricularStudent::query()
        ->whereIn(
            'extracurricular_id',
            $extracurriculars->pluck('id')
        )
        ->whereNotNull('student_profile_id')
        ->with([
            'studentProfile',
            'extracurricular'
        ]);

    /*
    |--------------------------------------------------------------------------
    | FILTER EKSKUL
    |--------------------------------------------------------------------------
    */

    if (
        !empty($filterExtracurricular) &&
        $filterExtracurricular !== 'all'
    ) {

        $query->where(
            'extracurricular_id',
            $filterExtracurricular
        );

    } elseif (
        !empty($extracurricularId) &&
        $extracurricularId !== 'all'
    ) {

        $query->where(
            'extracurricular_id',
            $extracurricularId
        );
    }

    /*
    |--------------------------------------------------------------------------
    | AMBIL DATA
    |--------------------------------------------------------------------------
    */

    $members = $query->get();

    /*
    |--------------------------------------------------------------------------
    | FILTER SESI
    |--------------------------------------------------------------------------
    |
    | PENTING:
    | Tidak menggunakan period_id.
    |
    | Sesi ditentukan dari created_at anggota.
    |
    */

    if (!empty($periodId)) {

        $period = ExtracurricularPeriod::find(
            $periodId
        );

        if ($period) {

            $startDate =
                $period->getAttribute('start_date')
                ?? $period->getAttribute('started_at')
                ?? $period->getAttribute('from_date');

            $endDate =
                $period->getAttribute('end_date')
                ?? $period->getAttribute('ended_at')
                ?? $period->getAttribute('to_date');

            if ($startDate) {

                $start =
                    \Carbon\Carbon::parse(
                        $startDate
                    )->startOfDay();

                $end =
                    $endDate
                        ? \Carbon\Carbon::parse(
                            $endDate
                        )->endOfDay()
                        : now()->endOfDay();

                $members = $members->filter(
                    function ($member) use (
                        $start,
                        $end
                    ) {

                        if (!$member->created_at) {
                            return false;
                        }

                        $createdAt =
                            \Carbon\Carbon::parse(
                                $member->created_at
                            );

                        return $createdAt->between(
                            $start,
                            $end
                        );
                    }
                );
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | FILTER KELAS
    |--------------------------------------------------------------------------
    */

    if (
        !empty($filterKelas) &&
        $filterKelas !== 'all'
    ) {

        $members = $members->filter(
            function ($member) use (
                $filterKelas
            ) {

                $kelas =
                    $member->studentProfile?->kelas
                    ?? $member->kelas
                    ?? null;

                return (string) $kelas ===
                    (string) $filterKelas;
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | UNIQUE SISWA
    |--------------------------------------------------------------------------
    */

    $uniqueStudents = $members
        ->filter(function ($member) {

            return !empty(
                $member->student_profile_id
            );

        })
        ->unique(
            'student_profile_id'
        )
        ->values();

    /*
    |--------------------------------------------------------------------------
    | SEMUA KELAS
    |--------------------------------------------------------------------------
    */

$classes = StudentProfile::query()
    ->with('kelas')
    ->get()
    ->map(function ($student) {

        $kelas = $student->kelas;

        if (is_object($kelas)) {

            return $kelas->name
                ?? $kelas->nama
                ?? $kelas->kelas
                ?? $kelas->nama_kelas
                ?? null;
        }

        return $kelas;
    })
    ->filter()
    ->unique()
    ->sort()
    ->values();

    /*
    |--------------------------------------------------------------------------
    | DATA GRAFIK
    |--------------------------------------------------------------------------
    */

    $classData = $classes
        ->map(function ($kelas) use (
            $uniqueStudents
        ) {

            $jumlah = $uniqueStudents
                ->filter(function ($member) use (
                    $kelas
                ) {

                    $studentKelas =
                        $member
                            ->studentProfile
                            ?->kelas
                        ?? $member->kelas
                        ?? null;

                    return (string) $studentKelas ===
                        (string) $kelas;

                })
                ->unique(
                    'student_profile_id'
                )
                ->count();

            /*
            | Total siswa sekolah
            */

            $totalSiswa =
                StudentProfile::query()
                    ->where(
                        'kelas',
                        $kelas
                    )
                    ->count();

            return [

                'kelas' =>
                    $kelas,

                'jumlah' =>
                    $jumlah,

                'joined' =>
                    $jumlah,

                'member' =>
                    $jumlah,

                'total_member' =>
                    $jumlah,

                'total' =>
                    $totalSiswa,

                'total_siswa' =>
                    $totalSiswa,

                'label' =>
                    $jumlah .
                    '/' .
                    $totalSiswa,

                'percentage' =>
                    $totalSiswa > 0
                        ? round(
                            ($jumlah /
                                $totalSiswa) *
                            100,
                            1
                        )
                        : 0,

                'persentase' =>
                    $totalSiswa > 0
                        ? round(
                            ($jumlah /
                                $totalSiswa) *
                            100,
                            1
                        )
                        : 0,
            ];

        })
        ->values();

    /*
    |--------------------------------------------------------------------------
    | RESPONSE
    |--------------------------------------------------------------------------
    */

    return response()->json([

        'success' => true,

        'period_id' =>
            $periodId,

        'extracurricular_id' =>
            $filterExtracurricular
            ?? $extracurricularId,

        'total_member' =>
            $uniqueStudents->count(),

        'joined' =>
            $uniqueStudents->count(),

        'total_members' =>
            $uniqueStudents->count(),

        'class_data' =>
            $classData,

        'classes' =>
            $classData,

        'classes_options' =>
            $classes,

        'chart' => [

            'labels' =>
                $classData
                    ->pluck('kelas')
                    ->values(),

            'members' =>
                $classData
                    ->pluck('jumlah')
                    ->values(),

            'total' =>
                $classData
                    ->pluck('total_siswa')
                    ->values(),

            'percentage' =>
                $classData
                    ->pluck('percentage')
                    ->values(),
        ],
    ]);
}


/*
|--------------------------------------------------------------------------
| KPI PERTEMUAN
|--------------------------------------------------------------------------
|
| Menampilkan:
|
| - Total pertemuan berdasarkan sesi
| - Rincian per ekskul
| - Dropdown sesi
|--------------------------------------------------------------------------
*/

public function kpiMeetings(
    $role,
    $schoolName,
    $schoolId
) {
    $periodId = request('period_id');

    /*
    |--------------------------------------------------------------------------
    | EKSKUL AKTIF
    |--------------------------------------------------------------------------
    */

    $extracurriculars = Extracurricular::where(
        'school_partner_id',
        $schoolId
    )
    ->where('status', 'active')
    ->orderBy('name')
    ->get();

    /*
    |--------------------------------------------------------------------------
    | SEMUA PERTEMUAN
    |--------------------------------------------------------------------------
    */

    $meetings = ExtracurricularMeeting::whereIn(
        'extracurricular_id',
        $extracurriculars->pluck('id')
    )
    ->orderBy('meeting_date')
    ->orderBy('id')
    ->get();

    /*
    |--------------------------------------------------------------------------
    | FILTER SESI
    |--------------------------------------------------------------------------
    */

    if (!empty($periodId)) {

        $period =
            ExtracurricularPeriod::find(
                $periodId
            );

        if ($period) {

            $startDate =
                $period->getAttribute('start_date')
                ?? $period->getAttribute('started_at')
                ?? $period->getAttribute('from_date');

            $endDate =
                $period->getAttribute('end_date')
                ?? $period->getAttribute('ended_at')
                ?? $period->getAttribute('to_date');

            if ($startDate) {

                $start =
                    \Carbon\Carbon::parse(
                        $startDate
                    )->startOfDay();

                $end =
                    $endDate
                        ? \Carbon\Carbon::parse(
                            $endDate
                        )->endOfDay()
                        : now()->endOfDay();

                $meetings =
                    $meetings->filter(
                        function ($meeting) use (
                            $start,
                            $end
                        ) {

                            if (
                                !$meeting->meeting_date
                            ) {
                                return false;
                            }

                            $date =
                                \Carbon\Carbon::parse(
                                    $meeting->meeting_date
                                );

                            return $date->between(
                                $start,
                                $end
                            );
                        }
                    )
                    ->values();
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | GROUP PER EKSKUL
    |--------------------------------------------------------------------------
    */

    $meetingGrouped =
        $meetings->groupBy(
            'extracurricular_id'
        );

    /*
    |--------------------------------------------------------------------------
    | RINCIAN EKSKUL
    |--------------------------------------------------------------------------
    */

    $meetingData =
        $extracurriculars
            ->map(function (
                $extracurricular
            ) use (
                $meetingGrouped
            ) {

                $total =
                    $meetingGrouped
                        ->get(
                            $extracurricular->id,
                            collect()
                        )
                        ->count();

                return [

                    'extracurricular_id' =>
                        $extracurricular->id,

                    'id' =>
                        $extracurricular->id,

                    'name' =>
                        $extracurricular->name,

                    'total' =>
                        $total,

                    'total_meeting' =>
                        $total,

                    'meetings' =>
                        $total,
                ];

            })
            ->sortBy('name')
            ->values();

    /*
    |--------------------------------------------------------------------------
    | RESPONSE
    |--------------------------------------------------------------------------
    */

    return response()->json([

        'success' => true,

        'period_id' =>
            $periodId,

        'total_meeting' =>
            $meetings->count(),

        'total' =>
            $meetings->count(),

        'extracurriculars' =>
            $meetingData,

        'data' =>
            $meetingData,
    ]);
}

public function updateNilai(
    Request $request,
    $role,
    $schoolName,
    $schoolId,
    $extracurricularId
) {
    try {

        /*
        |--------------------------------------------------------------------------
        | VALIDASI
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([

            'members' => [
                'required',
                'array',
            ],

            'members.*.student_profile_id' => [
                'required',
            ],

            /*
             * NILAI BEBAS
             *
             * Bisa:
             * 100
             * A
             * bagus
             * 100A
             * A+
             * abc123
             * kosong
             * simbol
             */

            'members.*.nilai' => [
                'nullable',
            ],

            /*
             * DESKRIPSI BEBAS
             */

            'members.*.deskripsi' => [
                'nullable',
            ],

        ]);


        /*
        |--------------------------------------------------------------------------
        | CEK EKSTRAKURIKULER
        |--------------------------------------------------------------------------
        */

        $extracurricular = Extracurricular::query()
            ->where(
                'id',
                $extracurricularId
            )
            ->where(
                'school_partner_id',
                $schoolId
            )
            ->first();


        if (!$extracurricular) {

            return response()->json([

                'status' => 'error',

                'message' =>
                    'Ekstrakurikuler tidak ditemukan.',

            ], 404);

        }


        /*
        |--------------------------------------------------------------------------
        | AMBIL PERIOD_ID
        |--------------------------------------------------------------------------
        |
        | URL halaman:
        |
        | ?period_id=4
        |
        */

        $periodId = $request->input(
            'period_id'
        );


        /*
        |--------------------------------------------------------------------------
        | FALLBACK DARI QUERY URL
        |--------------------------------------------------------------------------
        */

        if (
            $periodId === null ||
            $periodId === ''
        ) {

            $periodId =
                $request->query(
                    'period_id'
                );

        }


        /*
        |--------------------------------------------------------------------------
        | PERIOD WAJIB
        |--------------------------------------------------------------------------
        */

        if (
            $periodId === null ||
            $periodId === ''
        ) {

            return response()->json([

                'status' => 'error',

                'message' =>
                    'Period ID tidak ditemukan.',

            ], 422);

        }


        /*
        |--------------------------------------------------------------------------
        | VALIDASI PERIOD
        |--------------------------------------------------------------------------
        */

        $period = ExtracurricularPeriod::query()
            ->where(
                'id',
                $periodId
            )
            ->where(
                'extracurricular_id',
                $extracurricularId
            )
            ->first();


        if (!$period) {

            return response()->json([

                'status' => 'error',

                'message' =>
                    'Periode nilai tidak ditemukan.',

            ], 404);

        }


        /*
        |--------------------------------------------------------------------------
        | UPDATE ARSIP NILAI
        |--------------------------------------------------------------------------
        |
        | PENTING:
        |
        | UPDATE KE:
        |
        | extracurricular_nilai
        |
        | BUKAN:
        |
        | extracurricular_students
        |
        */

        $updated = 0;


        foreach (
            $validated['members']
            as $member
        ) {

            /*
            |--------------------------------------------------------------------------
            | STUDENT PROFILE ID
            |--------------------------------------------------------------------------
            */

            $studentProfileId =
                $member[
                    'student_profile_id'
                ] ?? null;


            if (
                $studentProfileId === null ||
                $studentProfileId === ''
            ) {

                continue;

            }


            /*
            |--------------------------------------------------------------------------
            | NILAI
            |--------------------------------------------------------------------------
            |
            | Tidak menggunakan numeric.
            |
            */

            $nilai =
                array_key_exists(
                    'nilai',
                    $member
                )
                    ? $member['nilai']
                    : null;


            if (
                $nilai === '' ||
                $nilai === null
            ) {

                $nilai = null;

            } else {

                $nilai =
                    (string) $nilai;

            }


            /*
            |--------------------------------------------------------------------------
            | DESKRIPSI
            |--------------------------------------------------------------------------
            */

            $deskripsi =
                array_key_exists(
                    'deskripsi',
                    $member
                )
                    ? $member['deskripsi']
                    : null;


            if (
                $deskripsi === '' ||
                $deskripsi === null
            ) {

                $deskripsi = null;

            } else {

                $deskripsi =
                    (string) $deskripsi;

            }


            /*
            |--------------------------------------------------------------------------
            | CARI ARSIP NILAI
            |--------------------------------------------------------------------------
            |
            | KUNCI UTAMA:
            |
            | period_id
            | student_profile_id
            |
            */

            $nilaiData =
                ExtracurricularValue::query()
                    ->where(
                        'period_id',
                        $periodId
                    )
                    ->where(
                        'student_profile_id',
                        $studentProfileId
                    )
                    ->first();


            /*
            |--------------------------------------------------------------------------
            | JIKA DATA TIDAK ADA
            |--------------------------------------------------------------------------
            */

            if (!$nilaiData) {

                Log::warning(
                    'ARSIP NILAI TIDAK DITEMUKAN',
                    [

                        'period_id' =>
                            $periodId,

                        'student_profile_id' =>
                            $studentProfileId,

                        'extracurricular_id' =>
                            $extracurricularId,

                    ]
                );

                continue;

            }


            /*
            |--------------------------------------------------------------------------
            | UPDATE NILAI
            |--------------------------------------------------------------------------
            */

            $nilaiData->nilai =
                $nilai;


            /*
            |--------------------------------------------------------------------------
            | UPDATE DESKRIPSI
            |--------------------------------------------------------------------------
            */

            $nilaiData->deskripsi =
                $deskripsi;


            /*
            |--------------------------------------------------------------------------
            | SIMPAN
            |--------------------------------------------------------------------------
            */

            $nilaiData->save();


            $updated++;

        }


        /*
        |--------------------------------------------------------------------------
        | RESPONSE
        |--------------------------------------------------------------------------
        */

        return response()->json([

            'status' =>
                'success',

            'message' =>
                $updated .
                ' data nilai berhasil diperbarui.',

            'updated' =>
                $updated,

            'period_id' =>
                $periodId,

        ]);


    } catch (
        \Illuminate\Validation\ValidationException $e
    ) {

        return response()->json([

            'status' =>
                'error',

            'message' =>
                $e->validator
                    ->errors()
                    ->first(),

            'errors' =>
                $e->validator
                    ->errors(),

        ], 422);


    } catch (\Throwable $e) {

        Log::error(
            'UPDATE ARSIP NILAI ERROR',
            [

                'message' =>
                    $e->getMessage(),

                'file' =>
                    $e->getFile(),

                'line' =>
                    $e->getLine(),

                'school_id' =>
                    $schoolId,

                'extracurricular_id' =>
                    $extracurricularId,

                'period_id' =>
                    $request->input(
                        'period_id'
                    ),

            ]
        );


        return response()->json([

            'status' =>
                'error',

            'message' =>
                $e->getMessage(),

        ], 500);

    }
}
}