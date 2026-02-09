<?php

namespace App\Http\Controllers;

use App\Events\SyllabusCrud;
use App\Imports\Syllabus\SyllabusSheetImport;
use App\Models\Bab;
use App\Models\Fase;
use App\Models\Kelas;
use App\Models\Kurikulum;
use App\Models\Mapel;
use App\Models\SchoolMapel;
use App\Models\SchoolPartner;
use App\Models\SubBab;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class SyllabusController extends Controller
{
    // function management kurikulum view
    public function curriculumView()
    {
        return view('syllabus-services.default.list-kurikulum');
    }

    // paginate management kurikulum
    public function paginateSyllabusCuriculum(Request $request)
    {
        $getSyllabusCuriculum = Kurikulum::with(['UserAccount.OfficeProfile'])->orderBy('created_at', 'desc')->paginate(20);

        return response()->json([
            'data' => $getSyllabusCuriculum->items(),
            'links' => (string) $getSyllabusCuriculum->links(),
            'faseDetail' => '/syllabus/curriculum/:curriculumName/:curriculumId/fase',
        ]);
    }

    // function management kurikulum store
    public function curiculumStore(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'nama_kurikulum' => [
                'required',
                Rule::unique('kurikulums', 'nama_kurikulum')
            ],
        ], [
            'nama_kurikulum.required' => 'Harap masukkan nama kurikulum.',
            'nama_kurikulum.unique' => 'Nama kurikulum telah terdaftar.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $curriculum = Kurikulum::create([
            'user_id' => $user->id,
            'nama_kurikulum' => $request->nama_kurikulum,
            'kode' => $request->nama_kurikulum,
        ]);

        broadcast(new SyllabusCrud('kurikulum', 'create', $curriculum))->toOthers();

        return response()->json([
            'status' => 'success',
            'message' => 'Kurikulum berhasil diubah.',
            'data' => $curriculum
        ]);
    }

    // function management kurikulum udpate
    public function curiculumEdit(Request $request, String $id)
    {
        $user = Auth::user();

        $curriculum = Kurikulum::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nama_kurikulum' => [
                'required',
                Rule::unique('kurikulums', 'nama_kurikulum')
            ],
        ], [
            'nama_kurikulum.required' => 'Harap masukkan nama kurikulum.',
            'nama_kurikulum.unique' => 'Nama kurikulum telah terdaftar.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $curriculum->update([
            'user_id' => $user->id,
            'nama_kurikulum' => $request->nama_kurikulum,
            'kode' => $request->nama_kurikulum,
        ]);

        broadcast(new SyllabusCrud('kurikulum', 'update', $curriculum))->toOthers();

        return response()->json([
            'status' => 'success',
            'message' => 'Kurikulum berhasil diubah.',
            'data' => $curriculum
        ]);
    }

    // function management fase view
    public function faseView($curriculumName, $curriculumId)
    {
        $dataFase = Fase::where('kurikulum_id', $curriculumId)->get();

        return view('syllabus-services.default.list-fase', compact( 'curriculumName', 'curriculumId', 'dataFase'));
    }

    // function paginate management fase
    public function paginateSyllabusFase(Request $request, $curriculumName, $curriculumId)
    {
        $getSyllabusFase = Fase::with(['UserAccount.OfficeProfile', 'Kurikulum'])->where('kurikulum_id', $curriculumId)
        ->orderBy('created_at', 'asc')->paginate(20);

        return response()->json([
            'data' => $getSyllabusFase->items(),
            'links' => (string) $getSyllabusFase->links(),
            'kelasDetail' => '/syllabus/curriculum/:curriculumName/:curriculumId/:faseId/kelas',
        ]);
    }

    // function management fase store
    public function faseStore(Request $request, $curriculumId)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'nama_fase' => [
                'required',
                Rule::unique('fases', 'nama_fase')->where('kurikulum_id', $curriculumId)
            ],
        ], [
            'nama_fase.required' => 'Harap masukkan Fase.',
            'nama_fase.unique' => 'Fase telah terdaftar.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422); // Gunakan 422 Unprocessable Entity untuk validasi
        }

        $data = Fase::create([
            'user_id' => $user->id,
            'nama_fase' => $request->nama_fase,
            'kode' => $request->nama_fase,
            'kurikulum_id' => $curriculumId,
        ]);

        broadcast(new SyllabusCrud('fase', 'create', $data))->toOthers();

        return response()->json([
            'status' => 'success',
            'message' => 'Fase berhasil ditambahkan.',
            'data' => $data
        ]);
    }

    // function paginate management fase edit
    public function faseEdit(Request $request, $curriculumId, $faseId)
    {
        $dataFase = Fase::findOrFail($faseId);

        $validator = Validator::make($request->all(), [
            'nama_fase' => [
                'required',
                Rule::unique('fases', 'nama_fase')->where('kurikulum_id', $curriculumId)
        ],
        ], [
            'nama_fase.required' => 'Harap masukkan Fase.',
            'nama_fase.unique' => 'Fase telah terdaftar.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $dataFase->update([
            'nama_fase' => $request->nama_fase,
            'kode' => $request->nama_fase,
        ]);

        broadcast(new SyllabusCrud('fase', 'update', $dataFase))->toOthers();

        return response()->json([
            'status' => 'success',
            'message' => 'Fase berhasil diubah.',
            'data' => $dataFase
        ]);
    }

    // function management kelas view
    public function kelasView($curriculumName, $curriculumId, $faseId)
    {
        $dataKelas = Kelas::where('fase_id', $faseId)->where('kurikulum_id', $curriculumId)->get();

        return view('syllabus-services.default.list-kelas', compact('curriculumName', 'curriculumId', 'faseId', 'dataKelas'));
    }

    // function paginate management kelas
    public function paginateSyllabusKelas($curriculumName, $curriculumId, $faseId)
    {
        $getSyllabusKelas = Kelas::with(['UserAccount.OfficeProfile', 'Kurikulum'])->where('fase_id', $faseId)
        ->where('kurikulum_id', $curriculumId)
        ->orderBy('created_at', 'asc')->paginate(20);

        return response()->json([
            'data' => $getSyllabusKelas->items(),
            'links' => (string) $getSyllabusKelas->links(),
            'mapelDetail' => '/syllabus/curriculum/:curriculumName/:curriculumId/:faseId/:kelasId/mapel',
        ]);
    }

    // function management kelas store
    public function kelasStore(Request $request, $curriculumId, $faseId)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'kelas' => [
                'required',
                Rule::unique('kelas', 'kelas')->where('fase_id', $faseId)->where('kurikulum_id', $curriculumId)
            ],
        ], [
            'kelas.required' => 'Harap masukkan kelas.',
            'kelas.unique' => 'Kelas telah terdaftar.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = Kelas::create([
            'user_id' => $user->id,
            'kelas' => $request->kelas,
            'kode' => $request->kelas,
            'fase_id' => $faseId,
            'kurikulum_id' => $curriculumId,
        ]);

        broadcast(new SyllabusCrud('kelas', 'create', $data))->toOthers();

        return response()->json([
            'status' => 'success',
            'message' => 'Kelas berhasil ditambahkan.',
            'data' => $data
        ]);
    }

    // function management kelas edit
    public function kelasEdit(Request $request, $curriculumId, $faseId, $kelasId)
    {
        $user = Auth::user();

        $dataKelas = Kelas::findOrFail($kelasId);

        $validator = Validator::make($request->all(), [
            'kelas' => [
                'required', Rule::unique('kelas', 'kelas')->where('fase_id', $faseId)->where('kurikulum_id', $curriculumId)
            ],
        ], [
            'kelas.required' => 'Harap masukkan kelas.',
            'kelas.unique' => 'Kelas telah terdaftar.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $dataKelas->update([
            'user_id' => $user->id,
            'kelas' => $request->kelas,
            'kode' => $request->kelas,
        ]);

        broadcast(new SyllabusCrud('kelas', 'update', $dataKelas))->toOthers();

        return response()->json([
            'status' => 'success',
            'message' => 'Kelas berhasil diubah.',
            'data' => $dataKelas
        ]);
    }

    // function management mapel view
    public function mapelView($curriculumName, $curriculumId, $faseId, $kelasId)
    {
        $dataMapel = Mapel::where('kelas_id', $kelasId)->where('fase_id', $faseId)->where('kurikulum_id', $curriculumId)->get();

        return view('syllabus-services.default.list-mapel', compact('curriculumName', 'curriculumId', 'faseId', 'kelasId', 'dataMapel'));
    }

    // function paginate management mapel
    public function paginateSyllabusMapel($curriculumName, $curriculumId, $faseId, $kelasId)
    {

        // Query dengan filter lengkap
        $getSyllabusMapel = Mapel::whereNull('school_partner_id')->with(['UserAccount.OfficeProfile', 'Kurikulum'])->where('fase_id', $faseId)->where('kurikulum_id', $curriculumId)
            ->where('kelas_id', $kelasId)
            ->orderBy('created_at', 'asc')
            ->paginate(20);

        return response()->json([
            'data' => $getSyllabusMapel->items(),
            'links' => (string) $getSyllabusMapel->links(),
            'babDetail' => '/syllabus/curriculum/:curriculumName/:curriculumId/:faseId/:kelasId/:mapelId/bab',
        ]);
    }

    public function mapelStore(Request $request, $curriculumId, $faseId, $kelasId)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'mata_pelajaran' => [
                'required',
                Rule::unique('mapels', 'mata_pelajaran')->where('kelas_id', $kelasId)->where('kurikulum_id', $curriculumId)
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
        ]);

        broadcast(new SyllabusCrud('mapel', 'create', $data))->toOthers();

        return response()->json([
            'status' => 'success',
            'message' => 'Mata Pelajaran berhasil ditambahkan.',
            'data' => $data
        ]);
    }

    public function mapelEdit(Request $request, $curriculumId, $faseId, $kelasId, $mapelId)
    {
        $user = Auth::user();

        $dataMapel = Mapel::findOrFail($mapelId);

        $validator = Validator::make($request->all(), [
            'mata_pelajaran' => [
                'required', Rule::unique('mapels', 'mata_pelajaran')->where('kelas_id', $kelasId)
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

        $dataMapel->update([
            'user_id' => $user->id,
            'mata_pelajaran' => $request->mata_pelajaran,
            'kode' => $request->mata_pelajaran,
        ]);

        broadcast(new SyllabusCrud('mapel', 'update', $dataMapel))->toOthers();

        return response()->json([
            'status' => 'success',
            'message' => 'Mata Pelajaran berhasil diubah.',
            'data' => $dataMapel
        ]);
    }

    public function mapelActivate(Request $request, $mapelId)
    {
        $request->validate([
            'status_mata_pelajaran' => 'required|in:active,inactive',
        ]);

        $dataMapel = Mapel::findOrFail($mapelId);
        $dataMapel->update([
            'status_mata_pelajaran' => $request->status_mata_pelajaran,
        ]);

        broadcast(new SyllabusCrud('mapel', 'activate', $dataMapel))->toOthers();

        return response()->json([
            'status' => 'success',
            'message' => 'Status Mata Pelajaran Berhasil Diubah',
            'data' => $dataMapel
        ]);
    }

    // function management bab view
    public function babView($curriculumName, $curriculumId, $faseId, $kelasId, $mapelId)
    {
        $dataBab = Bab::where('kelas_id', $kelasId)->where('mapel_id', $mapelId)
        ->where('fase_id', $faseId)->where('kurikulum_id', $curriculumId)->get();

        return view('syllabus-services.default.list-bab', compact('curriculumName', 'curriculumId', 'faseId', 'kelasId', 'mapelId', 'dataBab'));
    }

    // function paginate management bab
    public function paginateSyllabusBab($curriculumName, $curriculumId, $faseId, $kelasId, $mapelId)
    {
        // Query dengan filter lengkap
        $getSyllabusBab = Bab::with(['UserAccount.OfficeProfile', 'Kurikulum'])->where('fase_id', $faseId)
        ->where('kurikulum_id', $curriculumId)->where('kelas_id', $kelasId)->where('mapel_id', $mapelId)
        ->orderBy('created_at', 'asc')->paginate(20);

        return response()->json([
            'data' => $getSyllabusBab->items(),
            'links' => (string) $getSyllabusBab->links(),
            'subBabDetail' => '/syllabus/curriculum/:curriculumName/:curriculumId/:faseId/:kelasId/:mapelId/:babId/sub-bab',
        ]);
    }


    // function management bab store
    public function babStore(Request $request, $curriculumId, $faseId, $kelasId, $mapelId)
    {
        $user = Auth::user();
        $validator = Validator::make($request->all(), [
            'nama_bab' => [
                'required',
                Rule::unique('babs', 'nama_bab')->where('kelas_id', $kelasId)->where('kurikulum_id', $curriculumId)
                ->where('mapel_id', $mapelId)
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
            'mapel_id' => $mapelId,
            'fase_id' => $faseId,
            'kurikulum_id' => $curriculumId,
        ]);

        broadcast(new SyllabusCrud('bab', 'create', $data))->toOthers();

        return response()->json([
            'status' => 'success',
            'message' => 'Bab berhasil ditambahkan.',
            'data' => $data
        ]);
    }

    // function management bab edit
    public function babEdit(Request $request, $curriculumId, $faseId, $kelasId, $mapelId, $babId)
    {
        $user = Auth::user();

        $dataBab = Bab::findOrFail($babId);

        $validator = Validator::make($request->all(), [
            'nama_bab' => [
                'required',
                Rule::unique('babs', 'nama_bab')->where('kelas_id', $kelasId)->where('kurikulum_id', $curriculumId)
                ->where('mapel_id', $mapelId)
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

        $dataBab->update([
            'user_id' => $user->id,
            'nama_bab' => $request->nama_bab,
            'semester' => $request->semester,
            'kode' => $request->nama_bab,
        ]);

        broadcast(new SyllabusCrud('bab', 'update', $dataBab))->toOthers();

        return response()->json([
            'status' => 'success',
            'message' => 'Bab berhasil diubah.',
            'data' => $dataBab
        ]);
    }

    // function management bab activate
    public function babActivate(Request $request, $babId)
    {
        $request->validate([
            'status_bab' => 'required|in:active,inactive',
        ]);

        $bab = Bab::findOrFail($babId);

        $bab->update([
            'status_bab' => $request->status_bab,
        ]);

        broadcast(new SyllabusCrud('bab', 'activate', $bab))->toOthers();

        return response()->json(['message' => 'Status berhasil diperbarui']);
    }

    // function management sub bab view
    public function subBabView($curriculumName, $curriculumId, $faseId, $kelasId, $mapelId, $babId)
    {
        return view('syllabus-services.default.list-sub-bab', compact( 'curriculumName', 'curriculumId', 'faseId', 'kelasId', 'mapelId',  'babId'));
    }

    // function paginate management sub bab
    public function paginateSyllabusSubBab($curriculumName, $curriculumId, $faseId, $kelasId, $mapelId, $babId)
    {
        // Query dengan filter lengkap
        $getSyllabusSubBab = SubBab::with(['UserAccount.OfficeProfile', 'Kurikulum'])->where('fase_id', $faseId)->where('kurikulum_id', $curriculumId)
            ->where('kelas_id', $kelasId)->where('mapel_id', $mapelId)->where('bab_id', $babId)
            ->orderBy('created_at', 'asc')
            ->paginate(20);

        return response()->json([
            'data' => $getSyllabusSubBab->items(),
            'links' => (string) $getSyllabusSubBab->links(),
        ]);
    }


    // function management sub bab store
    public function subBabStore(Request $request, $curriculumId, $faseId, $kelasId, $mapelId, $babId)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'sub_bab' => [
                'required',
                Rule::unique('sub_babs', 'sub_bab')->where('kelas_id', $kelasId)->where('kurikulum_id', $curriculumId)
                ->where('mapel_id', $mapelId)->where('bab_id', $babId)
            ],
        ], [
            'sub_bab.required' => 'Harap masukkan sub bab.',
            'sub_bab.unique' => 'Sub Bab telah terdaftar.',
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
            'bab_id' => $babId,
            'kelas_id' => $kelasId,
            'mapel_id' => $mapelId,
            'fase_id' => $faseId,
            'kurikulum_id' => $curriculumId,
        ]);

        broadcast(new SyllabusCrud('subBab', 'delete', $data))->toOthers();

        return response()->json([
            'status' => 'success',
            'message' => 'Sub Bab berhasil ditambahkan.',
            'data' => $data
        ]);
    }

    // function management sub bab edit
    public function subBabEdit(Request $request, $curriculumId, $faseId, $kelasId, $mapelId, $babId, $subBabId)
    {
        $user = Auth::user();

        $dataSubBab = SubBab::findOrFail($subBabId);

        $validator = Validator::make($request->all(), [
            'sub_bab' => [
                'required',
                Rule::unique('sub_babs', 'sub_bab')->where('kelas_id', $kelasId)->where('kurikulum_id', $curriculumId)
                ->where('mapel_id', $mapelId)->where('bab_id', $babId)
            ],
        ], [
            'sub_bab.required' => 'Harap masukkan sub bab.',
            'sub_bab.unique' => 'Sub Bab telah terdaftar.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $dataSubBab->update([
            'user_id' => $user->id,
            'sub_bab' => $request->sub_bab,
            'kode' => $request->sub_bab,
        ]);

        broadcast(new SyllabusCrud('subBab', 'update', $dataSubBab))->toOthers();

        return response()->json([
            'status' => 'success',
            'message' => 'Sub Bab berhasil diubah.',
            'data' => $dataSubBab
        ]);
    }

    // function management sub bab activate
    public function subBabActivate(Request $request, $subBabId)
    {
        $request->validate([
            'status_sub_bab' => 'required|in:active,inactive',
        ]);

        $subBab = SubBab::findOrFail($subBabId);

        $subBab->update([
            'status_sub_bab' => $request->status_sub_bab,
        ]);

        broadcast(new SyllabusCrud('subBab', 'activate', $subBab))->toOthers();


        return response()->json(['message' => 'Status berhasil diperbarui']);
    }

    // function bulkUpload syllabus (EXCEL)
    public function bulkUploadSyllabus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bulkUpload-syllabus' => 'required|file|mimes:xlsx,xls,csv|max:100000',
        ], [
            'bulkUpload-syllabus.required' => 'File tidak boleh kosong.',
            'bulkUpload-syllabus.mimes' => 'Format file harus .xlsx.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => [
                    'form_errors' => $validator->errors(),
                    'excel_validation_errors' => [],
                ]
            ], 422);
        }

        try {
            $userId = Auth::id();
            Excel::import(new SyllabusSheetImport($userId, $request->file('bulkUpload-syllabus')), $request->file('bulkUpload-syllabus'));

            return response()->json([
                'status' => 'success',
                'message' => 'Import syllabus berhasil.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'errors' => [
                    'form_errors' => [],
                    'excel_validation_errors' => $e->errors()['import'] ?? [],
                ]
            ], 422);
        }
    }
}
