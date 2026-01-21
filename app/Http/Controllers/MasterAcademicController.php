<?php

namespace App\Http\Controllers;

use App\Models\Bab;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\SubBab;

class MasterAcademicController extends Controller
{
    // GET KELAS BY FASE
    public function getKelas($id)
    {
        $kelas = Kelas::where('fase_id', $id)->get();
        return response()->json($kelas);
    }

    // GET KELAS BY KURIKULUM
    public function getKelasByKurikulum($id)
    {
        $kelas = Kelas::where('kurikulum_id', $id)->get();
        return response()->json($kelas);
    }

    // GET MAPEL BY KELAS
    public function getMapelByKelas($id)
    {
        $mata_pelajaran = Mapel::where('kelas_id', $id)->where('status_mata_pelajaran', 'active')->get();
        return response()->json($mata_pelajaran);
    }

    // GET BAB BY MAPEL
    public function getBabByMapel($mapel_id)
    {
        $bab = Bab::where('mapel_id', $mapel_id)->where('status_bab', 'active')->get();
        return response()->json($bab);
    }

    // GET SUB BAB BY BAB
    public function getSubBabByBab($bab_id)
    {
        $subBab = SubBab::where('bab_id', $bab_id)->where('status_sub_bab', 'active')->get();
        return response()->json($subBab);
    }

}
