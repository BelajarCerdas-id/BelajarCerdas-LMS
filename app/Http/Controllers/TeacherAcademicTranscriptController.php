<?php

namespace App\Http\Controllers;

use App\Exports\AcademicTranscriptExport;
use App\Models\Mapel;
use App\Models\SchoolAssessment;
use App\Models\SchoolAssessmentType;
use App\Models\SchoolAssessmentTypeWeight;
use App\Models\SchoolPartner;
use App\Models\StudentSchoolClass;
use App\Models\SubjectPassingGradeCriteria;
use App\Models\TeacherMapel;
use App\Models\UserAccount;
use App\Services\ClassName\ClassNameService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class TeacherAcademicTranscriptController extends Controller
{
    private function extractClassLevel($className)
    {
        $classNameService = new ClassNameService();
        return $classNameService->extractClassLevel($className);
    }
    
    public function teacherClassList($role, $schoolName, $schoolId)
    {
        return view('features.lms.teacher.academic-transcript.teacher-class-list', compact('role', 'schoolName', 'schoolId'));
    }

    public function paginateTeacherClassList(Request $request, $role, $schoolName, $schoolId)
    {
        $user = Auth::user();

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
        
        $subjectTeacher = TeacherMapel::with(['Mapel', 'SchoolClass' => function ($query) {
                $query->withCount('StudentSchoolClass');
            },
            'SchoolClass.UserAccount.SchoolStaffProfile'
        ])
        ->where('user_id', $user->id)->where('is_active', 1)->get();

        // TAHUN AJARAN
        $tahunAjaran = $subjectTeacher->pluck('SchoolClass.tahun_ajaran')->unique()->sortDesc()->values();

        $searchYear = $request->filled('search_year') ? $request->search_year : ($tahunAjaran->first() ?? null);

        // FILTER BERDASARKAN TAHUN AJARAN
        $schoolClasses = $subjectTeacher->where('SchoolClass.tahun_ajaran', $searchYear)->values();

        // LEVEL KELAS UNIK
        $classLevels = $schoolClasses->pluck('SchoolClass.class_name')->map(fn($c) => (int) $this->extractClassLevel($c))->unique()->sort()->values();

        $selectedClass = $request->filled('search_class') ? (int) $request->search_class : ($classLevels->first() ?? $defaultLevel);

        // FILTER ROMBEL SESUAI LEVEL
        $schoolClasses = $schoolClasses->filter(fn($item) => (int)$this->extractClassLevel($item->SchoolClass->class_name) === $selectedClass)->values();

        // AMBIL MAPEL GURU
        $subjects = $schoolClasses->unique('mapel_id')->map(function ($item) {
            return [
                'id' => $item->mapel_id,
                'name' => $item->Mapel->mata_pelajaran ?? '-',
            ];
        })->values();

        // Filter berdasarkan level kelas
        if ($selectedClass) {
            $subjectTeacher = $subjectTeacher->filter(function ($item) use ($selectedClass) {

                if (!$item || !$item->SchoolClass->class_name) {
                    return false;
                }

                return $this->extractClassLevel($item->SchoolClass->class_name) == $selectedClass;
            });
        }

        $searchSubject = $request->filled('search_subject') ? (int) $request->search_subject : null;

        if ($searchSubject) {
            $schoolClasses = $schoolClasses->filter(function ($item) use ($searchSubject) {
                return $item->mapel_id == $searchSubject;
            })->values();
        }

        return response()->json([
            'data' => $schoolClasses,
            'tahunAjaran'   => $tahunAjaran,
            'selectedYear'  => $searchYear,
            'selectedClass' => $selectedClass,
            'className'     => $classLevels,
            'subject' => $subjects,
            'teacherAcademicTranscript' => '/lms/:role/:schoolName/:schoolId/academic-transcript/classes/subject-teacher/:subjectTeacherId'
        ]);
    }

    public function teacherAcademicTranscript($role, $schoolName, $schoolId, $subjectTeacherId)
    {
        return view('features.lms.teacher.academic-transcript.teacher-academic-transcript-management', compact('role', 'schoolName', 'schoolId', 'subjectTeacherId'));
    }

    public function paginateTeacherAcademicTranscript(Request $request, $role, $schoolName, $schoolId, $subjectTeacherId)
    {
        $user = Auth::user();

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

        // ambil class dari teacher
        $teacherMapel = TeacherMapel::with(['SchoolClass'])->where('id', $subjectTeacherId)->where('user_id', $user->id)->firstOrFail();

        $classId = $teacherMapel->school_class_id;

        // ambil semua siswa di kelas ini
        $studentIds = StudentSchoolClass::where('school_class_id', $classId)->pluck('student_id');

        // Ambil assessment type & weight
        $assessmentTypes = SchoolAssessmentType::where('school_partner_id', $schoolId)->where('is_active', 1)->get();

        $weights = SchoolAssessmentTypeWeight::where('school_partner_id', $schoolId)->get()->keyBy('assessment_type_id');

        // Ambil semua assessment siswa
        $assessments = SchoolAssessment::with(['Mapel', 'SchoolClass', 'StudentAssessmentSummary' => function ($q) use ($studentIds) {
            $q->whereIn('student_id', $studentIds);
        },
            'StudentAssessmentSummary.UserAccount'
        ])
        ->whereHas('StudentAssessmentSummary', function ($q) use ($studentIds) {
            $q->whereIn('student_id', $studentIds);
        })
        ->get();

        // GROUP DATA
        $grouped = [];

        foreach ($assessments as $assessment) {
            $mapelName = $assessment->Mapel->mata_pelajaran ?? null;
            $tahun = $assessment->SchoolClass->tahun_ajaran ?? 'Tanpa Tahun';
            $className = $assessment->SchoolClass->class_name;
            $classLevel = $this->extractClassLevel($className);
            $semester = $assessment->semester;

            if (!$mapelName) continue;

            foreach ($assessment->StudentAssessmentSummary as $summary) {
                $studentId = $summary->student_id;

                $grouped[$studentId][$assessment->mapel_id][$classLevel][$tahun][$semester][] = [
                    'type_id' => $assessment->assessment_type_id,
                    'score' => $summary->final_score
                ];
            }
        }

        $classLevelsFromStudentHistory = [];

        $studentClasses = StudentSchoolClass::with('SchoolClass')
            ->whereIn('student_id', $studentIds)
            ->get();

        foreach ($studentClasses as $sc) {
            if ($sc->SchoolClass) {
                $level = $this->extractClassLevel($sc->SchoolClass->class_name);
                $classLevelsFromStudentHistory[$level] = true;
            }
        }

        // CLASS LIST
        $classLevelsFromData = $classLevelsFromStudentHistory;

        foreach ($grouped as $studentId => $mapelData) {
            foreach ($mapelData as $mapelId => $classData) {
                foreach ($classData as $classLevel => $yearData) {
                    $classLevelsFromData[$classLevel] = true;
                }
            }
        }

        $classList = array_keys($classLevelsFromData);
        sort($classList);

        // default selected class
        $selectedClass = $request->class_level ?? (count($classList) ? min($classList) : $defaultLevel);

            // ambil mapel default dan custom by school
        $allMapels = Mapel::with(['TeacherMapel' => function ($q) use ($classId) {
            $q->where('is_active', 1)->where('school_class_id', $classId);
        },
            'TeacherMapel.UserAccount.SchoolStaffProfile'
        ])
        ->where('status_mata_pelajaran', 'active')
        ->where(function ($q) use ($schoolId) {

            $q->where(function ($qGlobal) use ($schoolId) {
                $qGlobal->whereNull('school_partner_id')
                    ->whereDoesntHave('SchoolMapel', function ($qSM) use ($schoolId) {
                        $qSM->where('school_partner_id', $schoolId)->where('is_active', 0);
                    });
            })
            ->orWhere(function ($q2) use ($schoolId) {
                $q2->where('school_partner_id', $schoolId)
                    ->whereHas('SchoolMapel', function ($q3) use ($schoolId) {
                        $q3->where('school_partner_id', $schoolId)->where('is_active', 1);
                    });
            });

        })->where('kelas_id', $selectedClass)->get();

        // Ambil tahun ajaran
        $years = $assessments->filter(function ($a) use ($selectedClass) {
            return $this->extractClassLevel($a->SchoolClass->class_name) == $selectedClass;
        })->pluck('SchoolClass.tahun_ajaran')->filter()->unique()->sort()->values();

        if ($years->isEmpty()) {
            $years = collect([
                $teacherMapel->SchoolClass->tahun_ajaran ?? 'Tanpa Tahun'
            ]);
        }

        // filter mapel by class
        $mapels = [];

        foreach ($allMapels as $mapel) {

            $mapelName = $mapel->mata_pelajaran;

            // cek apakah mapel ini punya data di class terpilih
            $hasData = false;

            foreach ($grouped as $studentData) {
                if (isset($studentData[$mapel->id][$selectedClass])) {
                    $hasData = true;
                    break;
                }
            }

            if (!$hasData) continue;

            foreach ($years as $year) {
                $mapels[$mapelName][$selectedClass][$year][1] = true;
                $mapels[$mapelName][$selectedClass][$year][2] = true;
            }
        }

        // Ambil data siswa
        $students = [];

        foreach ($studentIds as $studentId) {
            $student = UserAccount::find($studentId);

            $students[$studentId] = [
                'name' => $student->StudentProfile->nama_lengkap ?? '-',
                'mapels' => []
            ];
        }

        // HEADER MAPEL
        $mapels = [];

        foreach ($allMapels as $mapel) {
            $mapelName = $mapel->mata_pelajaran;

            foreach ($years as $year) {
                $mapels[$mapelName][$selectedClass][$year][1] = true;
                $mapels[$mapelName][$selectedClass][$year][2] = true;
            }
        }

        // HITUNG NILAI
        foreach ($grouped as $studentId => $mapelData) {

            foreach ($mapelData as $mapelId => $yearsData) {

                $mapelName = Mapel::find($mapelId)->mata_pelajaran ?? '-';

                foreach ($yearsData as $classLevel => $classData) {

                    // FILTER SESUAI DROPDOWN
                    if ($classLevel != $selectedClass) continue;

                    foreach ($classData as $tahun => $semesterData) {
                        foreach ($semesterData as $semester => $scores) {

                            $typeScores = [];

                            foreach ($assessmentTypes as $type) {

                                $filtered = array_filter($scores, function ($item) use ($type) {
                                    return $item['type_id'] == $type->id;
                                });

                                $values = array_column($filtered, 'score');

                                $avg = count($values) ? array_sum($values) / count($values) : 0;

                                $typeScores[] = [
                                    'type_id' => $type->id,
                                    'avg' => $avg,
                                    'count' => count($values)
                                ];
                            }

                            $total = 0;
                            $totalWeight = 0;

                            foreach ($typeScores as $type) {
                                $weight = $weights[$type['type_id']]->weight ?? 0;

                                if ($type['count'] > 0 && $weight > 0) {
                                    $total += $type['avg'] * $weight;
                                    $totalWeight += $weight;
                                }
                            }

                            $finalScore = $totalWeight > 0 ? round($total / 100) : 0;

                            $students[$studentId]['mapels'][$mapelName][$classLevel][$tahun][$semester] = $finalScore;
                        }
                    }
                }
            }
        }

        return response()->json([
            'teacherMapel' => $teacherMapel,
            'students' => array_values($students),
            'mapels' => $mapels,
            'summary' => [
                'total_students' => count($students),
            ],
            'className' => $classList,
            'selectedClass' => (int) $selectedClass,
        ]);
    }

    public function exportAcademicTranscript(Request $request, $role, $schoolName, $schoolId, $subjectTeacherId)
    {
        // reuse logic paginate
        $response = $this->paginateTeacherAcademicTranscript($request, $role, $schoolName, $schoolId, $subjectTeacherId)->getData(true);

        $teacherMapel = $response['teacherMapel'];
        $students = $response['students'];

        $classId = $teacherMapel['school_class_id'];
        $schoolClass = $teacherMapel['school_class']['class_name'] ?? '-';
        $schoolLogo = SchoolPartner::find($schoolId)?->logo;

        // ambil semua siswa di kelas aktif
        $studentIds = StudentSchoolClass::where('school_class_id', $classId)
            ->pluck('student_id');

        // ambil semua histori kelas siswa
        $studentClasses = StudentSchoolClass::with('SchoolClass')->whereIn('student_id', $studentIds)->get();

        $classYears = [];

        foreach ($studentClasses as $sc) {
            if ($sc->SchoolClass) {
                $level = $this->extractClassLevel($sc->SchoolClass->class_name);
                $year  = $sc->SchoolClass->tahun_ajaran ?? 'Tanpa Tahun';

                $classYears[$level][$year] = true;
            }
        }

        ksort($classYears);

        // ambil semua mapel aktif sesuai jenjang kelas yang pernah dilalui
        $allMapels = Mapel::where('status_mata_pelajaran', 'active')
            ->whereIn('kelas_id', array_keys($classYears))
            ->get();

        $mapels = [];

        foreach ($allMapels as $mapel) {
            $mapelName = $mapel->mata_pelajaran;
            $kelasId = $mapel->kelas_id;

            if (!isset($classYears[$kelasId])) continue;

            foreach ($classYears[$kelasId] as $year => $v) {
                $mapels[$mapelName][$kelasId][$year][1] = true;
                $mapels[$mapelName][$kelasId][$year][2] = true;
            }
        }

        $kkm = SubjectPassingGradeCriteria::where('school_partner_id', $schoolId)->where('kelas_id', $teacherMapel['school_class']['kelas_id'])
        ->where('mapel_id', $teacherMapel['mapel_id'])->value('kkm_value') ?? 75;

        $schoolNameSafe = str_replace(['/', '\\'], '-', $schoolName);

        $fileName = "Transkrip Nilai - {$schoolNameSafe} - {$schoolClass}.xlsx";

        return Excel::download(new AcademicTranscriptExport($students, $mapels, $kkm, $schoolName, $schoolClass, $schoolLogo), $fileName);
    }
}
