<?php

namespace App\Http\Controllers\Lms\ContentBank;

use App\Events\LmsContentManagement;
use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Kurikulum;
use App\Models\LmsContent;
use App\Models\SchoolLmsContent;
use App\Models\SchoolPartner;
use App\Models\ServiceRule;
use App\Services\LMS\LmsContentService;
use App\Services\ReviewContent\LmsReviewContentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ContentBankController extends Controller
{
    // CONSTRUCT LMS REVIEW CONTENT
    public function __construct(protected LmsReviewContentService $reviewContentService) 
    {}
    
    // function content management view
    public function lmsContentManagementView($schoolName = null, $schoolId = null)
    {
        $getCurriculum = Kurikulum::all();

        return view('Features.lms.administrator.content-management.lms-content-management', compact('getCurriculum', 'schoolName', 'schoolId'));
    }

    // function paginate lms content
    public function paginateLmsContentManagement($schoolName = null, $schoolId = null)
    {
        // jika ada schoolId maka ambil content dari sekolah tersebut dan dari global
        if ($schoolId) {
            $schoolPartner = SchoolPartner::findOrFail($schoolId);

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

            $jenjang = strtoupper($schoolPartner->jenjang_sekolah);

            $allowedKelas = $mappingClasses[$jenjang] ?? [];

            // ambil kelas sesuai dengan jenjang sekolahnya, lalu ambil id nya saja
            $kelasIds = Kelas::whereIn(DB::raw('LOWER(kelas)'), $allowedKelas)->pluck('id');

            $getContent = LmsContent::with(['UserAccount', 'UserAccount.OfficeProfile', 'UserAccount.SchoolStaffProfile', 'Kurikulum', 'Kelas', 'Mapel', 'Bab', 'SubBab', 'Service',
                'SchoolPartner', 'SchoolLmsContent' => function ($query) use ($schoolId) {
                    $query->where('school_partner_id', $schoolId);
                },
            ])->where(function ($query) use ($schoolId, $kelasIds) {
                $query->where('school_partner_id', $schoolId)->orWhere(function ($q) use ($kelasIds) {
                    $q->whereNull('school_partner_id')->whereIn('kelas_id', $kelasIds);
                });
            })->orderBy('created_at', 'desc')->paginate(10);;
        } else {
            $getContent = LmsContent::with(['UserAccount', 'UserAccount.OfficeProfile', 'UserAccount.SchoolStaffProfile', 'Kurikulum', 'Kelas', 'Mapel', 'Bab', 'SubBab', 
            'Service', 'SchoolLmsContent'])->whereNull('school_partner_id')->orderBy('created_at', 'desc')->paginate(10);
        }

        return response()->json([
            'data'   => $getContent->items(),
            'links'  => (string) $getContent->links(),
            'current_page' => $getContent->currentPage(),
            'per_page' => $getContent->perPage(),
            'reviewContent' => '/lms/content-management/:contentId/review',
            'reviewContentBySchool' => '/lms/school-subscription/:schoolName/:schoolId/academic-management/content-management/:contentId/review',
            'editContent' => '/lms/content-management/:contentId/edit',
            'editContentBySchool' => '/lms/school-subscription/:schoolName/:schoolId/academic-management/content-management/:contentId/edit',
        ]);
    }
    
    // function content management store
    public function lmsContentManagementStore(Request $request, LmsContentService $service, $schoolName = null, $schoolId = null)
    {
        // base validation
        $rules = [
            'service_id'   => 'required',
            'kurikulum_id' => 'required',
            'kelas_id'     => 'required',
            'mapel_id'     => 'required',
            'bab_id'       => 'required',
            'sub_bab_id'   => 'required',
        ];

        $messages = [
            'kurikulum_id.required' => 'Harap pilih kurikulum.',
            'kelas_id.required'     => 'Harap pilih kelas.',
            'mapel_id.required'     => 'Harap pilih mapel.',
            'bab_id.required'       => 'Harap pilih bab.',
            'sub_bab_id.required'   => 'Harap pilih sub bab.',
            'service_id.required'   => 'Harap pilih service.',
        ];

        // DYNAMIC VALIDATION (BERDASARKAN SERVICE RULE)
        $serviceRules = ServiceRule::where('service_id', $request->service_id)->get();

        foreach ($serviceRules as $rule) {

            // TEXT INPUT (ARRAY)
            if ($rule->upload_type === 'text') {
                $rules["text.{$rule->id}"] = 'required|array|min:1';
                $rules["text.{$rule->id}.*"] = 'required|string';

                $messages["text.{$rule->id}.required"] = "Text wajib diisi";
                $messages["text.{$rule->id}.array"]    = "Text wajib diisi";
                $messages["text.{$rule->id}.min"]      = "Text minimal 1 data";
                $messages["text.{$rule->id}.*.required"] = "Text tidak boleh kosong";
            }

            // FILE INPUT
            if ($rule->upload_type === 'file') {
                // default jika null
                $maxMb = $rule->max_size_mb ?? 100;
                $maxKb = $maxMb * 1024;

                $rules["files.{$rule->id}"] = "required|file|max:{$maxKb}";

                $messages["files.{$rule->id}.required"] = "File wajib diunggah.";
                $messages["files.{$rule->id}.file"]     = "Format file tidak valid.";
                $messages["files.{$rule->id}.max"]      = "File telah melebihi kapasitas yang ditentukan.";
            }
        }

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        // store via service
        $content = $service->store(
            $validator->validated(),
            Auth::id(),
            $schoolId ?? null
        );

        broadcast(new LmsContentManagement($content))->toOthers();

        // success response
        return response()->json([
            'status'  => 'success',
            'message' => 'Content berhasil ditambahkan',
            'data'    => $content,
        ]);
    }

    // function activate lms content
    public function lmsContentManagementActivate(Request $request, $contentId, $schoolName = null, $schoolId = null)
    {
        $isEnable = $request->action === 'enable';

        LmsContent::findOrFail($contentId);

        if ($schoolId) {
            SchoolLmsContent::updateOrCreate(
                [
                    'lms_content_id' => $contentId,
                    'school_partner_id' => $schoolId,
                ],
                [
                    'is_active' => $isEnable,
                ]
            );
        } else {
            $status = $isEnable ? 1 : 0;

            $affected = LmsContent::where('id', $contentId)->update([
                'is_active' => $status,
            ]);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Status content berhasil diubah.',
        ]);
    }

    // function review content default view (function untuk review content milik bc)
    public function lmsReviewContentDefault($contentId, $schoolName = null, $schoolId = null)
    {
        $data = $this->reviewContentService->getByContentId($contentId);

        return view('Features.lms.administrator.content-management.administrator-review-content', compact('contentId', 'data', 'schoolName', 'schoolId'));
    }

    // function review content school view (function untuk review content milik sekolah)
    public function lmsReviewContentSchool($schoolName, $schoolId, $contentId)
    {
        $data = $this->reviewContentService->getByContentId($contentId);

        return view('Features.lms.administrator.content-management.administrator-review-content', compact('contentId', 'data', 'schoolName', 'schoolId'));
    }

    // function edit content default view (function untuk edit content milik bc)
    public function lmsDefaultContentManagementEditView($contentId, $schoolName = null, $schoolId = null)
    {
        $content = LmsContent::with(['Kurikulum', 'Kelas', 'Mapel', 'Bab', 'SubBab', 'Service'])->findOrFail($contentId);

        $getCurriculum = Kurikulum::all();

        return view('Features.lms.administrator.content-management.administrator-content-management-edit',compact('content', 'getCurriculum', 'schoolName', 'schoolId'));
    }

    // function edit content default view (function untuk edit content milik sekolah)
    public function lmsSchoolContentManagementEditView($schoolName, $schoolId, $contentId)
    {
        $content = LmsContent::with(['Kurikulum', 'Kelas', 'Mapel', 'Bab', 'SubBab', 'Service'])->findOrFail($contentId);

        $getCurriculum = Kurikulum::all();

        return view('Features.lms.administrator.content-management.administrator-content-management-edit',compact('content', 'getCurriculum', 'schoolName', 'schoolId'));
    }

    // function form edit content
    public function lmsContentManagementFormEdit($contentId)
    {
        $data = $this->reviewContentService->getByContentId($contentId);

        return response()->json([
            'data' => $data
        ]);
    }

    // function form action edit content
    public function lmsContentManagementEdit(Request $request, $contentId)
    {
        // AMBIL RULE DARI SERVICE YANG DIPILIH USER
        $serviceRules = ServiceRule::where('service_id', $request->service_id)->get();

        // base validation
        $rulesValidation = [
            'kurikulum_id' => 'required',
            'kelas_id'     => 'required',
            'mapel_id'     => 'required',
            'bab_id'       => 'required',
            'sub_bab_id'   => 'required',
            'service_id'   => 'required',
        ];

        $messages = [
            'kurikulum_id.required' => 'Harap pilih kurikulum.',
            'kelas_id.required'     => 'Harap pilih kelas.',
            'mapel_id.required'     => 'Harap pilih mapel.',
            'bab_id.required'       => 'Harap pilih bab.',
            'sub_bab_id.required'   => 'Harap pilih sub bab.',
            'service_id.required'   => 'Harap pilih service.',
        ];

        // dynamic validation
        foreach ($serviceRules as $rule) {

            /* ================= TEXT ================= */
            if ($rule->upload_type === 'text') {

                $rulesValidation["text.{$rule->id}"] = [
                    $rule->is_required ? 'required' : 'nullable',
                    'array',
                    $rule->is_required ? 'min:1' : null,
                ];

                $rulesValidation["text.{$rule->id}.*"] = [
                    'required',
                    'string',
                ];

                if ($rule->is_required) {
                    $messages["text.{$rule->id}.required"] = 'Text tidak boleh kosong.';
                    $messages["text.{$rule->id}.min"]      = 'Minimal satu data harus diisi.';
                }

                $messages["text.{$rule->id}.*.required"] = "Text tidak boleh kosong.";
            }

            /* ================= FILE ================= */
            if ($rule->upload_type === 'file') {

                $hasExisting = $request->input("existing_files.{$rule->id}") == 1;

                // default max size (MB)
                $maxMb = $rule->max_size_mb ?? 100;
                $maxKb = $maxMb * 1024;

                $fileRules = [];

                $fileRules[] = $hasExisting ? 'nullable' : 'required';
                $fileRules[] = 'file';
                $fileRules[] = "max:{$maxKb}";

                $rulesValidation["files.{$rule->id}"] = $fileRules;

                $messages["files.{$rule->id}.required"] = "File wajib diunggah.";
                $messages["files.{$rule->id}.file"]     = "Format file tidak valid.";
                $messages["files.{$rule->id}.max"]      = "File telah melebihi kapasitas yang ditentukan.";
            }
        }

        $validator = Validator::make($request->all(), $rulesValidation, $messages);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $content = LmsContent::findOrFail($contentId);

        $lmsContentService = new LmsContentService();

        // update via service
        $updated = $lmsContentService->update(
            $content,
            $request->all(),
            Auth::id()
        );

        broadcast(new LmsContentManagement($updated))->toOthers();

        return response()->json([
            'status'  => 'success',
            'message' => 'Content berhasil diperbarui',
        ]);
    }
}