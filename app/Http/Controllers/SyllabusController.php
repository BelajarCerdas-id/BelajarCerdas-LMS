<?php

namespace App\Http\Controllers;

use App\Events\SyllabusCrud;
use App\Models\Fase;
use App\Models\Kelas;
use App\Models\Kurikulum;
use App\Models\Mapel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SyllabusController extends Controller
{
    // function management kurikulum view
    public function curriculumView()
    {
        return view('syllabus-services.list-kurikulum');
    }

    // paginate management kurikulum
    public function paginateSyllabusCuriculum(Request $request)
    {
        $getSyllabusCuriculum = Kurikulum::with(['UserAccount.OfficeProfile'])->orderBy('created_at', 'desc')->paginate(20);

        return response()->json([
            'data' => $getSyllabusCuriculum->items(),
            'links' => (string) $getSyllabusCuriculum->links(),
            'faseDetail' => '/syllabus/curiculum/:nama_kurikulum/:id/fase',
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

        return view('syllabus-services.list-fase', compact( 'curriculumName', 'curriculumId', 'dataFase'));
    }

    // function paginate management fase
    public function paginateSyllabusFase(Request $request, $curriculumName, $curriculumId)
    {
        $getSyllabusFase = Fase::with(['UserAccount.OfficeProfile', 'Kurikulum'])->where('kurikulum_id', $curriculumId)
        ->orderBy('created_at', 'asc')->paginate(20);

        return response()->json([
            'data' => $getSyllabusFase->items(),
            'links' => (string) $getSyllabusFase->links(),
            'kelasDetail' => '/syllabus/curiculum/:curriculumName/:curriculumId/:faseId/kelas',
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

        return view('syllabus-services.list-kelas', compact('curriculumName', 'curriculumId', 'faseId', 'dataKelas'));
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
            'mapelDetail' => '/syllabus/curiculum/:curriculumName/:curriculumId/:faseId/:kelasId/mapel',
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

        return view('syllabus-services.list-mapel', compact('curriculumName', 'curriculumId', 'faseId', 'kelasId', 'dataMapel'));
    }

    // function paginate management mapel
    public function paginateSyllabusMapel($curriculumName, $curriculumId, $faseId, $kelasId)
    {

        // Query dengan filter lengkap
        $getSyllabusMapel = Mapel::with(['UserAccount.OfficeProfile', 'Kurikulum'])->where('fase_id', $faseId)->where('kurikulum_id', $curriculumId)
            ->where('kelas_id', $kelasId)
            ->orderBy('created_at', 'asc')
            ->paginate(20);

        return response()->json([
            'data' => $getSyllabusMapel->items(),
            'links' => (string) $getSyllabusMapel->links(),
            'babDetail' => '/syllabus/curiculum/:curriculumName/:curriculumId/:faseId/:kelasId/:mapelId/bab',
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
}
