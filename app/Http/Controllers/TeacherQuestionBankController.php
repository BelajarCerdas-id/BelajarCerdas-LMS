<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Kurikulum;
use App\Models\LmsQuestionBank;
use App\Models\SchoolPartner;
use App\Models\TeacherMapel;
use App\Services\ClassName\ClassNameService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TeacherQuestionBankController extends Controller
{
    private function extractClassLevel($className)
    {
        $classNameService = new ClassNameService();
        return $classNameService->extractClassLevel($className);
    }
    
    // function teacher question bank management view
    public function teacherQuestionBankManagement($role, $schoolName, $schoolId)
    {
        $getCurriculum = Kurikulum::all();
        
        return view('features.lms.teacher.question-bank-management.teacher-question-bank-management', compact('role', 'schoolName', 'schoolId', 'getCurriculum'));
    }

    // function paginate teacher question bank management
    public function paginateTeacherQuestionBankManagement(Request $request, $role, $schoolName, $schoolId)
    {
        $user = Auth::user();

        $schoolPartner = SchoolPartner::findOrFail($schoolId);
        $jenjang = strtoupper($schoolPartner->jenjang_sekolah);

        // DEFAULT LEVEL BERDASARKAN JENJANG
        $startLevelMap = [
            'SD'  => 1,  'MI'  => 1,
            'SMP' => 7,  'MTS' => 7,
            'SMA' => 10, 'SMK' => 10,
            'MA'  => 10, 'MAK' => 10,
        ];

        $defaultLevel = $startLevelMap[$jenjang] ?? 1;

        // MAPPING KELAS BERDASARKAN JENJANG
        $mappingClasses = [
            'SD'  => ['kelas 1','kelas 2','kelas 3','kelas 4','kelas 5','kelas 6'],
            'MI'  => ['kelas 1','kelas 2','kelas 3','kelas 4','kelas 5','kelas 6'],
            'SMP' => ['kelas 7','kelas 8','kelas 9'],
            'MTS' => ['kelas 7','kelas 8','kelas 9'],
            'SMA' => ['kelas 10','kelas 11','kelas 12'],
            'SMK' => ['kelas 10','kelas 11','kelas 12'],
            'MA'  => ['kelas 10','kelas 11','kelas 12'],
            'MAK' => ['kelas 10','kelas 11','kelas 12'],
        ];

        $allowedKelas = $mappingClasses[$jenjang] ?? [];

        $kelasIds = Kelas::whereIn(DB::raw('LOWER(kelas)'), $allowedKelas)->pluck('id');

        // TEACHER MAPEL
        $teacherMapels = TeacherMapel::where('user_id', $user->id)->where('is_active', true)
            ->whereHas('SchoolClass', function ($q) use ($schoolId) {
                $q->where('school_partner_id', $schoolId);
            })->with(['SchoolClass', 'Mapel'])->get();

        // TAHUN AJARAN
        $tahunAjaran = $teacherMapels->pluck('SchoolClass.tahun_ajaran')->unique()->sortDesc()->values();

        $searchYear = $request->filled('search_year') ? $request->search_year : ($tahunAjaran->first() ?? null);

        // FILTER BERDASARKAN TAHUN AJARAN
        $schoolClasses = $teacherMapels->where('SchoolClass.tahun_ajaran', $searchYear)->values();

        // LEVEL KELAS UNIK
        $classLevels = $schoolClasses->pluck('SchoolClass.class_name')->map(fn($c) => (int) $this->extractClassLevel($c))->unique()->sort()->values();

        $selectedClass = $request->filled('search_class') ? (int) $request->search_class : ($classLevels->first() ?? $defaultLevel);

        // FILTER ROMBEL SESUAI LEVEL
        $schoolClasses = $schoolClasses->filter(fn($item) => (int)$this->extractClassLevel($item->SchoolClass->class_name) === $selectedClass)->values();

        // AMBIL MAPEL ID GURU
        $mapelIds = $teacherMapels->pluck('mapel_id')->unique();

        $getQuestions = LmsQuestionBank::with(['UserAccount', 'UserAccount.OfficeProfile', 'UserAccount.SchoolStaffProfile','Kurikulum', 'Kelas', 'Mapel', 'Bab', 'SubBab',
            'SchoolPartner',
            'SchoolQuestionBank' => function ($q) use ($schoolId) {

            if ($schoolId) {
                $q->where('school_partner_id', $schoolId);
            }
            
        }])->whereIn('mapel_id', $mapelIds)->whereIn('kelas_id', $kelasIds)->orderBy('created_at', 'desc');

        $getQuestions->where(function ($q1) use ($schoolId, $kelasIds) {
            $q1->where('school_partner_id', $schoolId)
            ->orWhere(function ($q2) use ($kelasIds) {
                $q2->whereNull('school_partner_id')->whereIn('kelas_id', $kelasIds);
            });
        });

        $questionCollection = $getQuestions->get();

        // Filter berdasarkan level kelas
        if ($selectedClass) {
            $questionCollection = $questionCollection->filter(function ($item) use ($selectedClass) {

                if (!$item || !$item->kelas_id) {
                    return false;
                }

                return $this->extractClassLevel($item->kelas_id) == $selectedClass;
            });
        }

        $rows = $questionCollection->groupBy(fn ($q) => $q->sub_bab_id.'-'.$q->tipe_soal.'-'.$q->school_partner_id)->values();

        // Pagination manual
        $page = $request->get('page', 1);
        $perPage = 20;

        $paged = $rows->slice(
            ($page - 1) * $perPage,
            $perPage
        )->values();

        $paginated = new LengthAwarePaginator(
            $paged,
            $rows->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $getSchool = SchoolPartner::with('UserAccount.SchoolStaffProfile')->where('id', $schoolId)->first();

        return response()->json([
            'data' => $paginated->values(),
            'links' => (string) $paginated->links(),
            'current_page' => $paginated->currentPage(),
            'per_page' => $paginated->perPage(),
            'schoolIdentity' => $getSchool,
            'source' => $source ?? null,
            'tahunAjaran'   => $tahunAjaran,
            'selectedYear'  => $searchYear,
            'selectedClass' => $selectedClass,
            'className'     => $classLevels,
            'lmsReviewQuestion' => '/lms/:role/:schoolName/:schoolId/teacher-question-bank-management/source/:source/review/question-type/:questionType/:subBabId',
        ]);
    }

    // function teacher question bank management detail
    public function teacherQuestionBankManagementDetail($role, $schoolName, $schoolId, $source, $questionType, $subBabId)
    {
        return view('features.lms.teacher.question-bank-management.teacher-question-bank-management-detail', compact('role', 'schoolName', 'schoolId', 
            'source', 'questionType', 'subBabId'));
    }

    // function teacher question bank management edit
    public function teacherQuestionBankManagementEdit($role, $schoolName, $schoolId, $source, $questionType, $subBabId, $questionId)
    {
        // Mengambil data soal berdasarkan ID
        $editQuestion = LmsQuestionBank::find($questionId);

        if (!$editQuestion) {
            return redirect()->route('lms.teacherQuestionBankManagement.detail.view', [$role, $schoolId, $schoolName, $source, $questionType, $subBabId]);
        }

        // Mengambil data soal yang punya pertanyaan (questions) yang sama, lalu dikelompokkan berdasarkan isi questions-nya
        $dataSoal = LmsQuestionBank::where('questions', $editQuestion->questions)->get()->groupBy('questions');

        // Simpan hasil pengelompokan ke variabel baru
        $groupedSoal = $dataSoal;

        return view('features.lms.teacher.question-bank-management.teacher-question-bank-management-edit', compact('role', 'schoolId', 'schoolName', 
        'source', 'subBabId', 'questionId', 'questionType'));
    }
}
