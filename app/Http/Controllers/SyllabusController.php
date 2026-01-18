<?php

namespace App\Http\Controllers;

use App\Events\SyllabusCrud;
use App\Models\Kurikulum;
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
}
