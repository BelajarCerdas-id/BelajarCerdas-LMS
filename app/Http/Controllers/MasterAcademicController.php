<?php

namespace App\Http\Controllers;

use App\Models\Bab;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\SchoolClass;
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
            $mata_pelajaran = Mapel::where(function ($query) use ($id, $schoolId) {

                // MAPEL KHUSUS SEKOLAH
                $query->whereHas('SchoolMapel', function ($q) use ($id, $schoolId) {
                    $q->where('school_partner_id', $schoolId)->where('kelas_id', $id)
                        ->where('is_active', 1);
                })

                // ATAU MAPEL GLOBAL
                ->orWhere(function ($q) use ($id, $schoolId) {
                    $q->whereNull('school_partner_id')
                        ->where('kelas_id', $id)
                        ->where('status_mata_pelajaran', 'active')

                        // JANGAN AMBIL JIKA ADA SCHOOL OVERRIDE
                        ->whereDoesntHave('SchoolMapel', function ($sq) use ($id, $schoolId) {
                            $sq->where('school_partner_id', $schoolId)->where('kelas_id', $id);
                    });
                });

            })->get();
        } else {
            $mata_pelajaran = Mapel::where('kelas_id', $id)->whereNull('school_partner_id')->where('status_mata_pelajaran', 'active')->get();
        }
        return response()->json($mata_pelajaran);
    }

    // GET ROMBEL BY KELAS
    public function getRombelByKelas($kelasId, $schoolId) {
        $rombel = SchoolClass::where('kelas_id', $kelasId)->where('school_partner_id', $schoolId)->orderBy('created_at', 'desc')->get();

        return response()->json($rombel);
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
