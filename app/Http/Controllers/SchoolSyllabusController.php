<?php

namespace App\Http\Controllers;

use App\Events\SyllabusCrud;
use App\Models\Bab;
use App\Models\Fase;
use App\Models\Kelas;
use App\Models\Kurikulum;
use App\Models\Mapel;
use App\Models\SchoolMapel;
use App\Models\SchoolPartner;
use App\Models\SubBab;
use App\Models\UserAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SchoolSyllabusController extends Controller
{
    // function kurikulum view
    public function curriculumView($schoolName, $schoolId)
    {
        return view('syllabus-services.school.list-kurikulum', compact('schoolName', 'schoolId'));
    }

    // function paginate kurikulum
    public function paginateCurriculum($schoolName, $schoolId)
    {
        $getCurriculum = Kurikulum::with(['UserAccount.OfficeProfile'])->orderBy('created_at', 'desc')->paginate(20);

        return response()->json([
            'data' => $getCurriculum->items(),
            'links' => (string) $getCurriculum->links(),
            'faseDetail' => '/lms/school-subscription/:schoolName/:schoolId/:curriculumName/:curriculumId/fase',
        ]);
    }

    // function fase view
    public function faseView($schoolName, $schoolId, $curriculumName, $curriculumId)
    {
        return view('syllabus-services.school.list-fase', compact('schoolName', 'schoolId', 'curriculumName', 'curriculumId'));
    }

    // function paginate fase
    public function paginateFase($schoolName, $schoolId, $curriculumName, $curriculumId)
    {
        $users = UserAccount::with(['StudentProfile', 'SchoolStaffProfile'])->where(function ($query) use ($schoolId) {
            $query->whereHas('StudentProfile', function ($q) use ($schoolId) {
                $q->where('school_partner_id', $schoolId);
            })->orWhereHas('SchoolStaffProfile', function ($q) use ($schoolId) {
                $q->where('school_partner_id', $schoolId);
            });
        })->get();
        
        $getSchool = SchoolPartner::with(['UserAccount.SchoolStaffProfile'])->where('id', $schoolId)->first();

        // mapping pahse based jenjang school
        $phaseMap = [
            'SD' => ['fase a', 'fase b', 'fase c'],
            'MI' => ['fase a', 'fase b', 'fase c'],
            'SMP' => ['fase d'],
            'MTS' => ['fase d'],
            'SMA' => ['fase e', 'fase f'],
            'SMK' => ['fase e', 'fase f'],
            'MA' => ['fase e', 'fase f'],
            'MAK' => ['fase e', 'fase f'],
        ];

        $allowedPhases = $phaseMap[$getSchool->jenjang_sekolah] ?? [];

        $dataFase = Fase::whereIn(DB::raw('LOWER(kode)'), $allowedPhases)->get();

        $countUsers = $users->count();

        return response()->json([
            'data' => $dataFase,
            'schoolIdentity' => $getSchool,
            'countUsers' => $countUsers,
            'kelasDetail' => '/lms/school-subscription/:schoolName/:schoolId/:curriculumName/:curriculumId/:faseId/kelas',
        ]);
    }

    // function kelas view
    public function kelasView($schoolName, $schoolId, $curriculumName, $curriculumId, $faseId)
    {
        return view('syllabus-services.school.list-kelas', compact('schoolName', 'schoolId', 'curriculumName', 'curriculumId', 'faseId'));
    }

    // function paginate kelas
    public function paginateKelas($schoolName, $schoolId, $curriculumName, $curriculumId, $faseId)
    {
        $users = UserAccount::with(['StudentProfile', 'SchoolStaffProfile'])->where(function ($query) use ($schoolId) {
            $query->whereHas('StudentProfile', function ($q) use ($schoolId) {
                $q->where('school_partner_id', $schoolId);
            })->orWhereHas('SchoolStaffProfile', function ($q) use ($schoolId) {
                $q->where('school_partner_id', $schoolId);
            });
        })->get();

        $getSchool = SchoolPartner::with(['UserAccount.SchoolStaffProfile'])->where('id', $schoolId)->first();

        $dataKelas = Kelas::where('fase_id', $faseId)->where('kurikulum_id', $curriculumId)->orderBy('created_at', 'asc')->paginate(20);

        $countUsers = $users->count();

        return response()->json([
            'data' => $dataKelas->items(),
            'links' => (string) $dataKelas->links(),
            'schoolIdentity' => $getSchool,
            'countUsers' => $countUsers,
            'mapelDetail' => '/lms/school-subscription/:schoolName/:schoolId/:curriculumName/:curriculumId/:faseId/:kelasId/mapel',
        ]);
    }

    // function mapel view
    public function mapelView($schoolName, $schoolId, $curriculumName, $curriculumId, $faseId, $kelasId)
    {
        return view('syllabus-services.school.list-mapel', compact('schoolName', 'schoolId', 'curriculumName', 'curriculumId', 'faseId', 'kelasId'));
    }

    // function paginate mapel
    public function paginateMapel($schoolName, $schoolId, $curriculumName, $curriculumId, $faseId, $kelasId)
    {
        $users = UserAccount::with(['StudentProfile', 'SchoolStaffProfile'])->where(function ($query) use ($schoolId) {
            $query->whereHas('StudentProfile', function ($q) use ($schoolId) {
                $q->where('school_partner_id', $schoolId);
            })->orWhereHas('SchoolStaffProfile', function ($q) use ($schoolId) {
                $q->where('school_partner_id', $schoolId);
            });
        })->get();

        $getSchool = SchoolPartner::with(['UserAccount.SchoolStaffProfile'])->where('id', $schoolId)->first();

        $customMapelIds = Mapel::where('school_partner_id', $schoolId)->where('kurikulum_id', $curriculumId)->where('fase_id', $faseId)
        ->where('kelas_id', $kelasId)->pluck('id');

        $dataSchoolMapel = Mapel::with([
            'UserAccount.SchoolStaffProfile',
            'UserAccount.OfficeProfile',
            'SchoolPartner',
            'SchoolMapel' => function ($q) use ($schoolId) {
                $q->where('school_partner_id', $schoolId);
            }
        ])
        ->where('kurikulum_id', $curriculumId)
        ->where('fase_id', $faseId)
        ->where('kelas_id', $kelasId)
        ->where(function ($query) use ($schoolId, $customMapelIds) {

            $query->where('school_partner_id', $schoolId);

            $query->orWhere(function ($q) use ($customMapelIds) {
                $q->whereNull('school_partner_id');

                if ($customMapelIds->isNotEmpty()) {
                    $q->whereNotIn('id', $customMapelIds);
                }
            });

        })
        ->orderByRaw('school_partner_id IS NULL')
        ->orderBy('created_at', 'asc')
        ->paginate(20);

            
        $countUsers = $users->count();

        return response()->json([
            'data' => $dataSchoolMapel->items(),
            'links' => (string) $dataSchoolMapel->links(),
            'schoolIdentity' => $getSchool,
            'countUsers' => $countUsers,
            'babDetail' => '/lms/school-subscription/:schoolName/:schoolId/:curriculumName/:curriculumId/:faseId/:kelasId/:mapelId/bab',
        ]);
    }

    // function mapel store
    public function mapelStore(Request $request, $schoolName, $schoolId, $curriculumName, $curriculumId, $faseId, $kelasId)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'mata_pelajaran' => [
                'required',
                Rule::unique('mapels', 'mata_pelajaran')
                    ->where(function ($query) use ($schoolId, $kelasId, $curriculumId) {
                        $query->where('kelas_id', $kelasId)
                            ->where('kurikulum_id', $curriculumId)
                            ->where(function ($q) use ($schoolId) {
                                $q->where('school_partner_id', $schoolId)
                                    ->orWhereNull('school_partner_id');
                            });
                    }),
            ],
        ], [
            'mata_pelajaran.required' => 'Harap masukkan nama mata pelajaran.',
            'mata_pelajaran.unique' => 'Mata pelajaran telah terdaftar.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = Mapel::create([
            'user_id' => $user->id,
            'mata_pelajaran' => $request->mata_pelajaran,
            'kode' => $request->mata_pelajaran,
            'kelas_id' => $kelasId,
            'fase_id' => $faseId,
            'kurikulum_id' => $curriculumId,
            'school_partner_id' => $schoolId
        ]);

        $schoolMapel = SchoolMapel::create([
            'school_partner_id' => $schoolId,
            'mapel_id' => $data->id,
        ]);

        broadcast(new SyllabusCrud('mapel', 'create', $data))->toOthers();

        return response()->json([
            'status' => 'success',
            'message' => 'Mata Pelajaran berhasil ditambahkan.',
            'data' => $data
        ]);
    }

    // function mapel edit
    public function mapelEdit(Request $request, $schoolName, $schoolId, $curriculumName, $curriculumId, $faseId, $kelasId, $mapelId)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'mata_pelajaran' => [
                'required',
                Rule::unique('mapels', 'mata_pelajaran')
                    ->where(function ($query) use ($schoolId, $kelasId, $curriculumId) {
                        $query->where('kelas_id', $kelasId)
                            ->where('kurikulum_id', $curriculumId)
                            ->where(function ($q) use ($schoolId) {
                                $q->where('school_partner_id', $schoolId)
                                    ->orWhereNull('school_partner_id');
                            });
                    }),
            ],
        ], [
            'mata_pelajaran.required' => 'Harap masukkan nama mata pelajaran.',
            'mata_pelajaran.unique' => 'Mata pelajaran telah terdaftar.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = Mapel::findOrFail($mapelId);

        $data->update([
            'user_id' => $user->id,
            'mata_pelajaran' => $request->mata_pelajaran,
            'kode' => $request->mata_pelajaran,
        ]);

        broadcast(new SyllabusCrud('mapel', 'update', $data))->toOthers();

        return response()->json([
            'status' => 'success',
            'message' => 'Mata Pelajaran berhasil diubah.',
            'data' => $data
        ]);
    }

    // function mapel activate
    public function mapelActivate(Request $request, $schoolName, $schoolId, $curriculumName, $curriculumId, $faseId, $kelasId, $mapelId)
    {
        $data = SchoolMapel::where('id', $mapelId)->update([
            'is_active' => $request->is_active,
        ]);

        broadcast(new SyllabusCrud('mapel', 'activate', $data))->toOthers();

        return response()->json([
            'status' => 'success',
            'message' => 'Status Mata Pelajaran Berhasil Diubah.',
            'data' => $data
        ]);
    }

    // function bab view
    public function babView($schoolName, $schoolId, $curriculumName, $curriculumId, $faseId, $kelasId, $mapelId)
    {
        return view('syllabus-services.school.list-bab', compact('schoolName', 'schoolId', 'curriculumName', 'curriculumId', 'faseId', 'kelasId', 'mapelId'));
    }

    // function paginate bab
    public function paginateBab($schoolName, $schoolId, $curriculumName, $curriculumId, $faseId, $kelasId, $mapelId)
    {
        $users = UserAccount::with(['StudentProfile', 'SchoolStaffProfile'])->where(function ($query) use ($schoolId) {
            $query->whereHas('StudentProfile', function ($q) use ($schoolId) {
                $q->where('school_partner_id', $schoolId);
            })->orWhereHas('SchoolStaffProfile', function ($q) use ($schoolId) {
                $q->where('school_partner_id', $schoolId);
            });
        })->get();

        $getSchool = SchoolPartner::with(['UserAccount.SchoolStaffProfile'])->where('id', $schoolId)->first();

        $getBab = Bab::with(['UserAccount.OfficeProfile', 'UserAccount.SchoolStaffProfile', 'SchoolPartner'])->where('fase_id', $faseId)
        ->where('kurikulum_id', $curriculumId)->where('kelas_id', $kelasId)->where('mapel_id', $mapelId)
        ->orderBy('created_at', 'asc')->paginate(20);

        $countUsers = $users->count();

        $mapel = Mapel::where('id', $mapelId)->first();

        return response()->json([
            'data' => $getBab->items(),
            'links' => (string) $getBab->links(),
            'schoolIdentity' => $getSchool,
            'countUsers' => $countUsers,
            'mapel' => $mapel,
            'subBabDetail' => '/lms/school-subscription/:schoolName/:schoolId/:curriculumName/:curriculumId/:faseId/:kelasId/:mapelId/:babId/sub-bab',
        ]);
    }

    // function bab store
    public function babStore(Request $request, $schoolName, $schoolId, $curriculumName, $curriculumId, $faseId, $kelasId, $mapelId)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'nama_bab' => [
                'required',
                Rule::unique('babs', 'nama_bab')
                    ->where(function ($query) use ($schoolId, $kelasId, $mapelId, $curriculumId) {
                        $query->where('kelas_id', $kelasId)->where('mapel_id', $mapelId)
                            ->where('kurikulum_id', $curriculumId)
                            ->where(function ($q) use ($schoolId) {
                                $q->where('school_partner_id', $schoolId)
                                    ->orWhereNull('school_partner_id');
                            });
                    }),
            ],
            'semester' => 'required',
        ], [
            'nama_bab.required' => 'Harap masukkan bab.',
            'nama_bab.unique' => 'Bab telah terdaftar.',
            'semester.required' => 'Harap pilih semester.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = Bab::create([
            'user_id' => $user->id,
            'nama_bab' => $request->nama_bab,
            'semester' => $request->semester,
            'kode' => $request->nama_bab,
            'kelas_id' => $kelasId,
            'fase_id' => $faseId,
            'mapel_id' => $mapelId,
            'kurikulum_id' => $curriculumId,
            'school_partner_id' => $schoolId
        ]);

        broadcast(new SyllabusCrud('bab', 'store', $data))->toOthers();

        return response()->json([
            'status' => 'success',
            'message' => 'Bab Berhasil Ditambahkan.',
            'data' => $data
        ]);
    }

    // function bab edit
    public function babEdit(Request $request, $schoolName, $schoolId, $curriculumName, $curriculumId, $faseId, $kelasId, $mapelId, $babId)
    {
        $validator = Validator::make($request->all(), [
            'nama_bab' => [
                'required',
                Rule::unique('babs', 'nama_bab')->where(function ($query) use ($schoolId, $kelasId, $mapelId, $curriculumId) {
                    $query->where('kelas_id', $kelasId)->where('mapel_id', $mapelId)
                        ->where('kurikulum_id', $curriculumId)
                        ->where(function ($q) use ($schoolId) {
                        $q->where('school_partner_id', $schoolId)->orWhereNull('school_partner_id');
                    });
                }),
            ],
            'semester' => 'required',
        ], [
            'nama_bab.required' => 'Harap masukkan bab.',
            'nama_bab.unique' => 'Bab telah terdaftar.',
            'semester.required' => 'Harap pilih semester.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $dataBab = Bab::findOrFail($babId);

        $dataBab->update([
            'user_id' => Auth::user()->id,
            'nama_bab' => $request->nama_bab,
            'semester' => $request->semester,
            'kode' => $request->nama_bab,
        ]);

        broadcast(new SyllabusCrud('bab', 'update', $dataBab))->toOthers();

        return response()->json([
            'status' => 'success',
            'message' => 'Bab Berhasil diubah.',
            'data' => $dataBab
        ]);
    }

    // function bab activate
    public function babActivate(Request $request, $schoolName, $schoolId, $curriculumName, $curriculumId, $faseId, $kelasId, $mapelId, $babId)
    {
        $data = Bab::where('id', $babId)->update([
            'status_bab' => $request->status_bab,
        ]);

        broadcast(new SyllabusCrud('bab', 'activate', $data))->toOthers();

        return response()->json([
            'status' => 'success',
            'message' => 'Status Mata Pelajaran Berhasil Diubah.',
            'data' => $data
        ]);
    }

    // function sub bab view
    public function subBabView($schoolName, $schoolId, $curriculumName, $curriculumId, $faseId, $kelasId, $mapelId, $babId)
    {
        return view('syllabus-services.school.list-sub-bab', compact('schoolName', 'schoolId', 'curriculumName', 'curriculumId', 'faseId', 
        'kelasId', 'mapelId', 'babId'));
    }

    // function paginate sub bab
    public function paginateSubBab($schoolName, $schoolId, $curriculumName, $curriculumId, $faseId, $kelasId, $mapelId, $babId)
    {
        $users = UserAccount::with(['StudentProfile', 'SchoolStaffProfile'])->where(function ($query) use ($schoolId) {
            $query->whereHas('StudentProfile', function ($q) use ($schoolId) {
                $q->where('school_partner_id', $schoolId);
            })->orWhereHas('SchoolStaffProfile', function ($q) use ($schoolId) {
                $q->where('school_partner_id', $schoolId);
            });
        })->get();

        $getSchool = SchoolPartner::with(['UserAccount.SchoolStaffProfile'])->where('id', $schoolId)->first();

        $getSubBab = SubBab::with(['UserAccount.OfficeProfile', 'UserAccount.SchoolStaffProfile', 'SchoolPartner'])->where('fase_id', $faseId)
        ->where('kurikulum_id', $curriculumId)->where('kelas_id', $kelasId)->where('mapel_id', $mapelId)->where('bab_id', $babId)
        ->orderBy('created_at', 'asc')->paginate(20);

        $countUsers = $users->count();

        $bab = Bab::where('id', $babId)->first();

        return response()->json([
            'data' => $getSubBab->items(),
            'links' => (string) $getSubBab->links(),
            'schoolIdentity' => $getSchool,
            'countUsers' => $countUsers,
            'bab' => $bab,
            'subBabDetail' => '/lms/school-subscription/:schoolName/:schoolId/:curriculumName/:curriculumId/:faseId/:kelasId/:mapelId/:babId/sub-bab',
        ]);
    }

    // function sub bab store
    public function subBabStore(Request $request, $schoolName, $schoolId, $curriculumName, $curriculumId, $faseId, $kelasId, $mapelId, $babId)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'sub_bab' => [
                'required',
                Rule::unique('sub_babs', 'sub_bab')
                    ->where(function ($query) use ($schoolId, $kelasId, $mapelId, $babId, $curriculumId) {
                        $query->where('kelas_id', $kelasId)->where('mapel_id', $mapelId)->where('bab_id', $babId)
                            ->where('kurikulum_id', $curriculumId)
                            ->where(function ($q) use ($schoolId) {
                                $q->where('school_partner_id', $schoolId)
                                    ->orWhereNull('school_partner_id');
                            });
                    }),
            ],
        ], [
            'sub_bab.required' => 'Harap masukkan sub bab.',
            'sub_bab.unique' => 'Sub bab telah terdaftar.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = SubBab::create([
            'user_id' => $user->id,
            'sub_bab' => $request->sub_bab,
            'kode' => $request->sub_bab,
            'kelas_id' => $kelasId,
            'fase_id' => $faseId,
            'mapel_id' => $mapelId,
            'bab_id' => $babId,
            'kurikulum_id' => $curriculumId,
            'school_partner_id' => $schoolId
        ]);

        broadcast(new SyllabusCrud('subBab', 'store', $data))->toOthers();

        return response()->json([
            'status' => 'success',
            'message' => 'Sub bab Berhasil Ditambahkan.',
            'data' => $data
        ]);
    }

    // function sub bab edit
    public function subBabEdit(Request $request, $schoolName, $schoolId, $curriculumName, $curriculumId, $faseId, $kelasId, $mapelId, $babId, $subBabId)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'sub_bab' => [
                'required',
                Rule::unique('sub_babs', 'sub_bab')
                    ->where(function ($query) use ($schoolId, $kelasId, $mapelId, $babId, $curriculumId) {
                        $query->where('kelas_id', $kelasId)->where('mapel_id', $mapelId)->where('bab_id', $babId)
                            ->where('kurikulum_id', $curriculumId)
                            ->where(function ($q) use ($schoolId) {
                                $q->where('school_partner_id', $schoolId)
                                    ->orWhereNull('school_partner_id');
                            });
                    }),
            ],
        ], [
            'sub_bab.required' => 'Harap masukkan sub bab.',
            'sub_bab.unique' => 'Sub bab telah terdaftar.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $dataSubBab = SubBab::findOrFail($subBabId);

        $dataSubBab->update([
            'user_id' => $user->id,
            'sub_bab' => $request->sub_bab,
            'kode' => $request->sub_bab,
        ]);

        broadcast(new SyllabusCrud('subBab', 'update', $dataSubBab))->toOthers();

        return response()->json([
            'status' => 'success',
            'message' => 'Sub bab Berhasil diubah.',
            'data' => $dataSubBab
        ]);
    }

    // function sub bab activate
    public function subBabActivate(Request $request, $schoolName, $schoolId, $curriculumName, $curriculumId, $faseId, $kelasId, $mapelId, $babId, $subBabId)
    {
        $data = SubBab::where('id', $subBabId)->update([
            'status_sub_bab' => $request->status_sub_bab,
        ]);

        broadcast(new SyllabusCrud('subBab', 'activate', $data))->toOthers();

        return response()->json([
            'status' => 'success',
            'message' => 'Status Berhasil Diubah.',
            'data' => $data
        ]);
    }
}
