<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Kurikulum;
use App\Models\LmsContent;
use App\Models\SchoolPartner;
use App\Models\TeacherMapel;
use App\Services\ClassName\ClassNameService;
use App\Services\ReviewContent\LmsReviewContentService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class TeacherContentController extends Controller
{
    public function __construct(protected LmsReviewContentService $reviewContentService) 
    {}

    private function extractClassLevel($className)
    {
        $classNameService = new ClassNameService();
        return $classNameService->extractClassLevel($className);
    }
    
    // function teacher content management view
    public function teacherContentManagement($role, $schoolName, $schoolId)
    {
        $getCurriculum = Kurikulum::all();
        
        return view('features.lms.teacher.content-management.teacher-content-management', compact('role', 'schoolName', 'schoolId', 'getCurriculum'));
    }

    // function paginate teacher content management
    public function paginateTeacherContentManagement(Request $request, $role, $schoolName, $schoolId)
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

        // LMS CONTENT
        $query = LmsContent::with(['UserAccount', 'UserAccount.SchoolStaffProfile', 'UserAccount.OfficeProfile', 'Kurikulum', 'Bab', 'SubBab', 'Kelas', 'Mapel', 'LmsContentItem', 
            'Service', 'SchoolPartner', 'SchoolLmsContent' => function ($q) use ($schoolId) {
                $q->where('school_partner_id', $schoolId);
            }
        ])->where(function ($q) use ($schoolId) {

            $q->where(function ($q) use ($schoolId) {

                // Jika ada override untuk sekolah
                $q->whereHas('SchoolLmsContent', function ($qOverride) use ($schoolId) {
                    $qOverride->where('school_partner_id', $schoolId);
                })

                // Jika tidak ada override, pakai global
                ->orWhere(function ($qGlobal) use ($schoolId) {
                    $qGlobal->whereNull('school_partner_id')->whereDoesntHave('SchoolLmsContent', function ($qCheck) use ($schoolId) {
                        $qCheck->where('school_partner_id', $schoolId);
                    });
                });
            });
        })->whereIn('mapel_id', $mapelIds)->whereIn('kelas_id', $kelasIds)->orderByDesc('created_at');

        $contentCollection = $query->orderBy('created_at', 'desc')->get();

        // Filter berdasarkan level kelas
        if ($selectedClass) {
            $contentCollection = $contentCollection->filter(function ($item) use ($selectedClass) {

                if (!$item || !$item->kelas_id) {
                    return false;
                }

                return $this->extractClassLevel($item->kelas_id) == $selectedClass;
            });
        }

        // manual pagination karena sudah menjadi collection
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 20;

        $getContent = new LengthAwarePaginator(
            $contentCollection->forPage($currentPage, $perPage)->values(),
            $contentCollection->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url()]
        );

        return response()->json([
            'data'   => $getContent->items(),
            'links'  => (string) $getContent->links(),
            'current_page' => $getContent->currentPage(),
            'per_page' => $getContent->perPage(),
            'tahunAjaran'   => $tahunAjaran,
            'selectedYear'  => $searchYear,
            'selectedClass' => $selectedClass,
            'className'     => $classLevels,
            'reviewContent' => '/lms/:role/:schoolName/:schoolId/teacher-content-management/:contentId/review',
            'editContent' => '/lms/:role/:schoolName/:schoolId/teacher-content-management/:contentId/edit',
        ]);
    }

    // function teacher review content
    public function teacherReviewContent($role, $schoolName, $schoolId, $contentId)
    {
        $data = $this->reviewContentService->getByContentId($contentId);

        return view('features.lms.teacher.content-management.teacher-review-content', compact('role', 'schoolName', 'schoolId', 'contentId', 'data'));
    }

    // function teacher edit content
    public function teacherEditContent($role, $schoolName, $schoolId, $contentId)
    {
        $content = LmsContent::with(['Kurikulum', 'Kelas', 'Mapel', 'Bab', 'SubBab', 'Service'])->findOrFail($contentId);

        $getCurriculum = Kurikulum::all();

        return view('features.lms.teacher.content-management.teacher-edit-content', compact('role', 'schoolName', 'schoolId', 'contentId', 'content', 'getCurriculum'));
    }
}
