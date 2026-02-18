<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Kurikulum;
use App\Models\LmsContent;
use App\Models\LmsMeetingContent;
use App\Models\SchoolClass;
use App\Models\SchoolPartner;
use App\Models\Service;
use App\Models\TeacherMapel;
use App\Services\ClassName\ClassNameService;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TeacherContentReleaseController extends Controller
{
    private function extractClassLevel($className)
    {
        $classNameService = new ClassNameService();
        return $classNameService->extractClassLevel($className);
    }

    // private function guessMime
    private function guessMime($ext)
    {
        return match (strtolower($ext)) {
            'mp4', 'webm', 'ogg' => 'video/' . $ext,
            'pdf'               => 'application/pdf',
            'jpg', 'jpeg', 'png', 'webp' => 'image/' . $ext,
            default             => 'application/octet-stream',
        };
    }

    // function teacher content for release view
    public function teacherContentForRelease($role, $schoolName, $schoolId)
    {
        $getCurriculum = Kurikulum::all();
        
        return view('features.lms.teacher.content.teacher-content-for-release', compact('role', 'schoolName', 'schoolId', 'getCurriculum'));
    }

    // function paginate teacher content for release
    public function paginateTeacherContentForRelease($role, $schoolName, $schoolId)
    {
        $user = Auth::user();

        $data = LmsMeetingContent::with(['SchoolClass', 'Mapel', 'Service'])
            ->selectRaw('
                lms_meeting_contents.school_class_id,
                lms_meeting_contents.mapel_id,
                lms_meeting_contents.semester,
                lms_meeting_contents.service_id,
                COUNT(*) as total_meetings,
                MAX(lms_meeting_contents.created_at) as last_created
            ')
            ->where('lms_meeting_contents.teacher_id', $user->id)
            ->where('lms_meeting_contents.school_partner_id', $schoolId)
            ->groupBy('lms_meeting_contents.school_class_id', 'lms_meeting_contents.mapel_id', 'lms_meeting_contents.semester', 'lms_meeting_contents.service_id')
            ->orderByDesc('last_created')
            ->paginate(20);

        return response()->json([
            'data' => $data->items(),
            'links' => (string) $data->links(),
            'current_page' => $data->currentPage(),
            'per_page' => $data->perPage(),
            'teacherContentForReleaseReviewMeetings' => '/lms/:role/:schoolName/:schoolId/content-for-release/rombel-kelas/:schoolClassId/subject/:mapelId/semester/:semester/service/:serviceId/review-meetings',
        ]);
    }

    // function teacher content for release form
    public function teacherFormContentForRelease(Request $request, $role, $schoolName, $schoolId)
    {
        $teacherId = Auth::id();

        // VALIDASI SCHOOL
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
        $teacherMapels = TeacherMapel::where('user_id', $teacherId)->where('is_active', true)
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
        $query = LmsContent::with(['Kurikulum', 'Bab', 'SubBab', 'Kelas', 'Mapel', 'LmsContentItem', 'Service', 'SchoolPartner', 'SchoolLmsContent' => function ($q) use ($schoolId) {
                $q->where('school_partner_id', $schoolId)->where('is_active', true);
            }
        ])->where('is_active', true)->where(function ($q) use ($schoolId) {

            // Default content global
            $q->where(function ($q) use ($schoolId) {

                $q->where(function ($qGlobal) use ($schoolId) {
                    $qGlobal->whereNull('school_partner_id')
                        ->whereDoesntHave('SchoolLmsContent', function ($qSM) use ($schoolId) {
                            $qSM->where('school_partner_id', $schoolId)
                                ->where('is_active', 0);
                        });
                })
                    ->orWhere('school_partner_id', $schoolId);
            });
        })->whereIn('mapel_id', $mapelIds)->whereIn('kelas_id', $kelasIds)->orderByDesc('created_at');

        // FILTER SEARCH MATERI
        if ($request->filled('search_materi')) {
            $query->whereHas('LmsContentItem', function ($q) use ($request) {
                $q->where('original_filename', 'LIKE', '%' . $request->search_materi . '%');
            });
        }

        // FILTER CURRICULUM CORE
        foreach (['kurikulum_id','service_id','kelas_id','mapel_id','bab_id'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->$filter);
            }
        }

        $contents = $query->get();

        return response()->json([
            'tahunAjaran'   => $tahunAjaran,
            'selectedYear'  => $searchYear,
            'selectedClass' => $selectedClass,
            'className'     => $classLevels,
            'rombel'        => $schoolClasses,
            'contents'      => $contents
        ]);
    }

    // function teacher content for release store
    public function teacherContentForReleaseStore(Request $request, $role, $schoolName, $schoolId)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'school_class_id' => 'required',
            'mapel_id'        => 'required',
            'lms_content_id'  => 'required',
            'semester'        => 'required',
            'pertemuan'       => 'required',
            'meeting_date'    => 'required',
        ], [
            'school_class_id.required' => 'Harap pilih kelas.',
            'mapel_id.required'        => 'Mapel tidak ditemukan.',
            'lms_content_id.required'  => 'Harap pilih materi.',
            'semester.required'        => 'Harap pilih semester.',
            'pertemuan.required'       => 'Harap pilih pertemuan.',
            'meeting_date.required'    => 'Harap pilih tanggal.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {

            $classId = $request->school_class_id;
            $mapelId = $request->mapel_id;

            $meetingDate = Carbon::parse($request->meeting_date)->format('Y-m-d');

            $content = LmsContent::find($request->lms_content_id);

            if (!$content) {
                return response()->json([
                    'errors' => [
                        'lms_content_id' => ['Materi tidak ditemukan.']
                    ]
                ], 422);
            }

            // Validasi materi sesuai mapel
            if ($content->mapel_id != $mapelId) {
                return response()->json([
                    'status' => 'error',
                    'errors' => [
                        'lms_content_id' => ['Materi tidak sesuai dengan mata pelajaran rombel.']
                    ]
                ], 422);
            }

            LmsMeetingContent::updateOrCreate([
                'school_class_id' => $classId,
                'mapel_id' => $mapelId,
                'semester' => $request->semester,
                'meeting_number' => $request->pertemuan,
                'school_partner_id' => $schoolId,
                'service_id' => $content->service_id,
            ], [
                'teacher_id' => $user->id,
                'lms_content_id' => $request->lms_content_id,
                'meeting_date' => $meetingDate,
                'is_active' => $request->is_active,
            ]);

            DB::commit();

        } catch (QueryException $e) {

            DB::rollBack();

            // MySQL duplicate
            if ($e->getCode() === '23000') {

                return response()->json([
                    'status' => 'error',
                    'errors' => [
                        'pertemuan' => [
                            'Pertemuan ini telah terdaftar pada rombel kelas tersebut.'
                        ]
                    ]
                ], 422);
            }

            throw $e;
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Data berhasil disimpan.',
        ]);
    }

    // function edit teacher content for release
    public function teacherContentForReleaseEdit(Request $request, $role, $schoolName, $schoolId, $meetingContentId)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'semester' => 'required',
            'meeting_number' => 'required',
            'meeting_date' => 'required',
        ], [
            'semester.required' => 'Harap pilih semester.',
            'meeting_number.required' => 'Harap pilih pertemuan.',
            'meeting_date.required' => 'Harap pilih tanggal.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {

            $meeting = LmsMeetingContent::findOrFail($meetingContentId);

            $meeting->update([
                'teacher_id' => $user->id,
                'semester' => $request->semester,
                'meeting_number' => $request->meeting_number,
                'meeting_date' => $request->meeting_date
            ]);

            DB::commit();

        } catch (QueryException $e) {

            DB::rollBack();

            // MySQL duplicate
            if ($e->getCode() === '23000') {

                return response()->json([
                    'status' => 'error',
                    'errors' => [
                        'meeting_number' => [
                            'Pertemuan ini telah terdaftar pada rombel kelas tersebut.'
                        ]
                    ]
                ], 422);
            }

            throw $e;
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Data berhasil disimpan.',
        ]);
    }

    // function teacher content for release review meetings view
    public function teacherContentForReleaseReviewMeeting($role, $schoolName, $schoolId, $schoolClassId, $mapelId, $semester, $serviceId)
    {
        return view('features.lms.teacher.content.teacher-content-for-release-review-meetings', compact('role', 'schoolName', 
            'schoolId', 'schoolClassId', 'mapelId', 'semester', 'serviceId'));
    }

    // function paginate
    public function paginateTeacherContentForReleaseReviewMeeting($role, $schoolName, $schoolId, $schoolClassId, $mapelId, $semester, $serviceId)
    {
        $user = Auth::user();

        $getMeetingContent = LmsMeetingContent::with(['LmsContent', 'LmsContent.LmsContentItem', 'SchoolClass'])->where('teacher_id', $user->id)
            ->whereHas('LmsContent', function ($query) use ($serviceId) {
                $query->where('service_id', $serviceId);
            })
            ->where('school_partner_id', $schoolId)->where('school_class_id', $schoolClassId)->where('mapel_id', $mapelId)
            ->where('semester', $semester)->get();

        $getSchoolClass = SchoolClass::where('id', $schoolClassId)->first();

        $getService = Service::where('id', $serviceId)->first();

        return response()->json([
            'data' => $getMeetingContent,
            'schoolClass' => $getSchoolClass,
            'service' => $getService,
            'teacherContentForReleaseReviewContent' => '/lms/:role/:schoolName/:schoolId/content-for-release/rombel-kelas/:schoolClassId/subject/:mapelId/semester/:semester/service/:serviceId/review-content/:meetingContentId',
        ]);
    }

    public function teacherContentForReleaseActivate(Request $request, $role, $schoolName, $schoolId, $meetingContentId)
    {
        $user = Auth::user();

        LmsMeetingContent::where('id', $meetingContentId)->update([
            'teacher_id' => $user->id,
            'is_active' => $request->is_active,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Status berhasil diubah.',
        ]);
    }

    public function TeacherContentForReleaseReviewContent($role, $schoolName, $schoolId, $schoolClassId, $mapelId, $semester, $serviceId, $meetingContentId)
    {
        $items = LmsMeetingContent::with('LmsContent.LmsContentItem.ServiceRule', 'LmsContent.Service')->where('id', $meetingContentId)->get();

        $data = $items->map(function ($item) {
            $serviceName = $item->LmsContent?->Service?->name;

            if (!$item->LmsContent->LmsContentItem[0]->value_file) {
                return [
                    'service_name' => $serviceName,
                    'rule_id' => $item->service_rule_id,
                    'rule_name' => $item->LmsContent->LmsContentItem[0]->ServiceRule?->name,
                    'value_text' => $item->LmsContent->LmsContentItem[0]->value_text,
                    'type' => 'text'
                ];
            }

            $extension = pathinfo($item->LmsContent->LmsContentItem[0]->value_file, PATHINFO_EXTENSION);

            return [
                'service_name' => $serviceName,
                'rule_id'   => $item->service_rule_id,
                'rule_name' => $item->ServiceRule?->name,
                'file_name'=> $item->LmsContent->LmsContentItem[0]->original_filename,
                'file_url' => asset('lms-contents/' . $item->LmsContent->LmsContentItem[0]->value_file),
                'mime'     => $this->guessMime($extension),
                'type'     => 'file'
            ];

        });

        return view('features.lms.teacher.content.teacher-content-for-release-review-content', compact('role', 'schoolName', 
            'schoolId', 'schoolClassId', 'mapelId', 'semester', 'serviceId', 'meetingContentId', 'data'));
    }
}
