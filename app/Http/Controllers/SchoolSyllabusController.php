<?php

namespace App\Http\Controllers;

use App\Models\Fase;
use App\Models\Kelas;
use App\Models\Kurikulum;
use App\Models\SchoolPartner;
use App\Models\UserAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
}
