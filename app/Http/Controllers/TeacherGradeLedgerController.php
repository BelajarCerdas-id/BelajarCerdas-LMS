<?php

namespace App\Http\Controllers;

use App\Exports\GradeLedgerExport;
use App\Models\Mapel;
use App\Models\SchoolAssessmentType;
use App\Models\SchoolAssessmentTypeWeight;
use App\Models\SchoolPartner;
use App\Models\StudentAssessmentSummary;
use App\Models\StudentSchoolClass;
use App\Models\TeacherMapel;
use App\Services\ClassName\ClassNameService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class TeacherGradeLedgerController extends Controller
{
    private function extractClassLevel($className)
    {
        $classNameService = new ClassNameService();
        return $classNameService->extractClassLevel($className);
    }
    
    public function teacherClassList($role, $schoolName, $schoolId)
    {
        return view('features.lms.teacher.grade-ledger.teacher-class-list', compact('role', 'schoolName', 'schoolId'));
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
            'teacherGradeLedger' => '/lms/:role/:schoolName/:schoolId/grade-ledger/classes/subject-teacher/:subjectTeacherId'
        ]);
    }

    public function teacherGradeLedger($role, $schoolName, $schoolId, $subjectTeacherId)
    {
        return view('features.lms.teacher.grade-ledger.teacher-grade-ledger-management', compact('role', 'schoolName', 'schoolId', 'subjectTeacherId'));
    }

    public function paginateTeacherGradeLedger(Request $request, $role, $schoolName, $schoolId, $subjectTeacherId)
    {
        $user = Auth::user();

        $semester = $request->semester ?? 1;

        // AMBIL TEACHER MAPEL
        $teacherMapel = TeacherMapel::with(['Mapel', 'SchoolClass'])->where('id', $subjectTeacherId)->where('user_id', $user->id)->firstOrFail();

        // ambil rombel
        $classId = $teacherMapel->school_class_id;

        // CLASS
        $schoolClass = $teacherMapel->SchoolClass;

        $semester = $request->semester ?? 1;

        // MAPEL DI KELAS
        $subjects = Mapel::where('kelas_id', $schoolClass->kelas_id)
            ->where(function ($q) use ($schoolId) {
                $q->whereNull('school_partner_id')->orWhere('school_partner_id', $schoolId);
            })
            ->where('status_mata_pelajaran', 'active')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->mata_pelajaran ?? '-'
                ];
            })
            ->values();

        // AMBIL TYPE & WEIGHT
        $assessmentTypes = SchoolAssessmentType::where('school_partner_id', $schoolId)->where('is_active', 1)->get();

        $weights = SchoolAssessmentTypeWeight::where('school_partner_id', $schoolId)->get()->keyBy('assessment_type_id');

        // SISWA
        $students = StudentSchoolClass::with('UserAccount.StudentProfile')->where('school_class_id', $classId)->where('student_class_status', 'active')->get();

        // SORT A-Z BY NAME
        $students = $students->sortBy(function ($student) {
            return strtolower($student->UserAccount->StudentProfile->nama_lengkap ?? '');
        })->values();

        $data = [];

        foreach ($students as $student) {

            $studentId = $student->student_id;

            $row = [
                'name' => $student->UserAccount->StudentProfile->nama_lengkap ?? '-',
                'subjects' => [],
                'avg' => 0
            ];

            $totalScore = 0;
            $countMapel = 0;

            foreach ($subjects as $subject) {

                $mapelId = $subject['id'];

                // AMBIL SUMMARY
                $summaries = StudentAssessmentSummary::with('SchoolAssessment')->where('student_id', $studentId)->whereHas('SchoolAssessment', function ($q) use ($classId, $mapelId, $semester) {
                    $q->where('school_class_id', $classId)->where('mapel_id', $mapelId)->where('semester', $semester);
                })->get()->groupBy('root_assessment_id')->map(function ($items) {
                    return collect($items)->sortByDesc('id')->first();
                });

                // HITUNG PER TYPE
                $typeScores = [];

                foreach ($assessmentTypes as $type) {

                    $scores = [];

                    foreach ($summaries as $summary) {
                        if ($summary->SchoolAssessment->assessment_type_id == $type->id) {
                            $scores[] = $summary->final_score;
                        }
                    }

                    $avg = count($scores) ? array_sum($scores) / count($scores) : 0;

                    $typeScores[] = [
                        'type_id' => $type->id,
                        'avg' => $avg,
                        'count' => count($scores)
                    ];
                }

                // NORMALISASI
                $total = 0;
                $totalWeight = 0;

                foreach ($typeScores as $type) {
                    $weight = $weights[$type['type_id']]->weight ?? 0;

                    if ($type['count'] > 0 && $weight > 0) {
                        $total += $type['avg'] * $weight;
                        $totalWeight += $weight;
                    }
                }

                $avgScore = round($total / 100);

                $row['subjects'][$subject['name']] = $avgScore;

                if ($avgScore > 0) {
                    $totalScore += $avgScore;
                    $countMapel++;
                }
            }

            $row['avg'] = $countMapel > 0 ? round($totalScore / $countMapel) : 0;

            $data[] = $row;
        }

        // SUMMARY
        $avgList = collect($data)->pluck('avg');

        $summary = [
            'total_students' => count($data),
            'avg' => $avgList->count() ? round($avgList->avg()) : 0,
            'max' => $avgList->max() ?? 0,
            'min' => $avgList->min() ?? 0,
        ];

        return response()->json([
            'students' => $data,
            'subjects' => $subjects->pluck('name'),
            'summary' => $summary,
            'teacherMapel' => $teacherMapel,
            'classInfo' => [
                'class_name' => $schoolClass->class_name,
                'semester' => $semester
            ]
        ]);
    }

    public function exportGradeLedger(Request $request, $role, $schoolName, $schoolId, $subjectTeacherId, $semester)
    {
        $response = $this->paginateTeacherGradeLedger($request, $role, $schoolName, $schoolId, $subjectTeacherId)->getData(true);

        $students = $response['students'];
        $subjects = $response['subjects'];
        $semester = $semester ?? 1;
        $tahunAjaran = $response['teacherMapel']['school_class']['tahun_ajaran'] ?? '-';
        $schoolClass = $response['teacherMapel']['school_class']['class_name'] ?? '-';

        // SAFE filename (hindari slash)
        $schoolNameSafe = str_replace(['/', '\\'], '-', $schoolName);
        $tahunAjaranSafe = str_replace(['/', '\\'], '-', $tahunAjaran);

        $fileName = "Leger Nilai - {$schoolNameSafe} - {$schoolClass} - Semester {$semester} - {$tahunAjaranSafe}.xlsx";

        return Excel::download(new GradeLedgerExport($students, $subjects, $schoolName, $schoolClass, $semester, $tahunAjaran), $fileName);
    }
}
