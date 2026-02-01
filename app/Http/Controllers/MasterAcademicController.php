<?php

namespace App\Http\Controllers;

use App\Models\Bab;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\SchoolMapel;
use App\Models\SchoolPartner;
use App\Models\Service;
use App\Models\SubBab;
use Illuminate\Support\Facades\DB;

class MasterAcademicController extends Controller
{
    // GET KELAS BY FASE
    public function getKelas($id)
    {
        $kelas = Kelas::where('fase_id', $id)->get();
        return response()->json($kelas);
    }

    // GET KELAS BY KURIKULUM
    public function getKelasByKurikulum($id, $schoolId = null)
    {
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

            $kelas = Kelas::where('kurikulum_id', $id)
            ->when(isset($mappingClasses[$jenjang]), function ($query) use ($mappingClasses, $jenjang) {
                $query->whereIn(DB::raw('LOWER(kelas)'),array_map('strtolower', $mappingClasses[$jenjang]));
            })->get();
        } else {
            $kelas = Kelas::where('kurikulum_id', $id)->get();
        }
        return response()->json($kelas);
    }

    // GET SERVICE BY KURIKULUM
    public function getServiceByKurikulum($id, $schoolId = null)
    {
        // jika ada schoolId maka gunakan service yang untuk school partner only
        if ($schoolId) {
            $service = Service::where('kurikulum_id', $id)->where('school_partner_status', true)->get();
        } else {
            $service = Service::where('kurikulum_id', $id)->where('school_partner_status', true)->orWhere('school_partner_status', false)->get();
        }
        return response()->json($service);
    }

    // GET MAPEL BY KELAS
    public function getMapelByKelas($id, $schoolId = null)
    {
        if ($schoolId) {
            $mata_pelajaran = SchoolMapel::with(['Mapel'])->whereHas('Mapel', function ($query) use ($id) {
                $query->where('kelas_id', $id);
            })->where('school_partner_id', $schoolId)->get();
        } else {
            $mata_pelajaran = Mapel::where('kelas_id', $id)->whereNull('school_partner_id')->where('status_mata_pelajaran', 'active')->get();
        }
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
