<?php

namespace App\Http\Controllers;

use App\Events\DailyReflectionLivePreview;
use App\Events\StudentDailyReflectionForm;
use App\Models\Kelas;
use App\Models\SchoolClass;
use App\Models\SchoolPartner;
use App\Models\SchReflAnswer;
use App\Models\SchReflQuestion;
use App\Models\SchReflTarget;
use App\Models\StudentSchoolClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class StudentVicePrincipalController extends Controller
{
    public function index($role, $schoolName, $schoolId)
    {
        return view('features.lms.student-vice-principal.dashboard', compact('role', 'schoolName', 'schoolId'));     
    }

    public function loadStudentReflectionChart(Request $request, $role, $schoolName, $schoolId)
    {
        $months = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        $period = $request->period ?? 'monthly';
        $year = $request->year ?? now()->year;
        $month = $request->month ?? now()->month;

        $query = SchReflAnswer::whereHas(
            'UserAccount.StudentProfile',
            function ($query) use ($schoolId) {
                $query->where('school_partner_id', $schoolId);
            }
        );

        $availableYears = SchReflAnswer::whereHas(
            'UserAccount.StudentProfile',
            function ($query) use ($schoolId) {
                $query->where('school_partner_id', $schoolId);
            }
        )->selectRaw('YEAR(created_at) as year')->distinct()->orderByDesc('year')->pluck('year')->values();

        switch ($period) {

            case 'daily':

                $data = (clone $query)
                    ->selectRaw('DAY(created_at) as label')
                    ->selectRaw('COUNT(*) as total')
                    ->whereYear('created_at', $year)
                    ->whereMonth('created_at', $month)
                    ->groupBy('label')
                    ->orderBy('label')
                    ->get();

                return response()->json([
                    'years' => $availableYears,
                    'title' => "Trend Harian {$months[$month]} {$year}",
                    'labels' => $data->pluck('label')->values(),
                    'data' => $data->pluck('total')->values(),
                ]);

            case 'weekly':

                $data = (clone $query)
                    ->selectRaw('CEIL(DAY(created_at) / 7) as label')
                    ->selectRaw('COUNT(*) as total')
                    ->whereYear('created_at', $year)
                    ->whereMonth('created_at', $month)
                    ->groupBy('label')
                    ->orderBy('label')
                    ->get();

                return response()->json([
                    'years' => $availableYears,
                    'title' => "Trend Mingguan {$months[$month]} {$year}",
                    'labels' => $data->pluck('label')
                        ->map(fn ($week) => "Minggu {$week}")
                        ->values(),
                    'data' => $data->pluck('total')->values(),
                ]);

            case 'monthly':

                $data = (clone $query)
                    ->selectRaw('MONTH(created_at) as label')
                    ->selectRaw('COUNT(*) as total')
                    ->whereYear('created_at', $year)
                    ->groupBy('label')
                    ->orderBy('label')
                    ->get();

                return response()->json([
                    'years' => $availableYears,
                    'title' => "Trend Bulanan Tahun {$year}",
                    'labels' => $data->pluck('label')
                        ->map(fn ($monthNumber) => $months[$monthNumber])
                        ->values(),
                    'data' => $data->pluck('total')->values(),
                ]);

            // yearly
            default:

                $data = (clone $query)
                    ->selectRaw('YEAR(created_at) as label')
                    ->selectRaw('COUNT(*) as total')
                    ->groupBy('label')
                    ->orderBy('label')
                    ->get();

                return response()->json([
                    'years' => $availableYears,
                    'title' => "Trend Tahunan",
                    'labels' => $data->pluck('label')->values(),
                    'data' => $data->pluck('total')->values(),
                ]);
        }
    }

    public function reflectionMaangement($role, $schoolName, $schoolId)
    {
        $schoolPartner = SchoolPartner::findOrFail($schoolId);
        $jenjang = strtoupper($schoolPartner->jenjang_sekolah);

        // FILTER BERDASARKAN JENJANG
        if (in_array($jenjang, ['SD', 'MI'])) {
            $kelas = Kelas::whereIn('kelas', [
                'Kelas 1',
                'Kelas 2',
                'Kelas 3',
                'Kelas 4',
                'Kelas 5',
                'Kelas 6',
            ])->get();
        }

        elseif (in_array($jenjang, ['SMP', 'MTS'])) {
            $kelas = Kelas::whereIn('kelas', [
                'Kelas 7',
                'Kelas 8',
                'Kelas 9',
            ])->get();
        }

        elseif (in_array($jenjang, ['SMA', 'SMK', 'MA', 'MAK'])) {
            $kelas = Kelas::whereIn('kelas', [
                'Kelas 10',
                'Kelas 11',
                'Kelas 12',
            ])->get();
        }

        else {
            $kelas = collect();
        }

        return view(
            'features.lms.student-vice-principal.reflection-management.reflection-management', compact('role', 'schoolName', 'schoolId', 'kelas')
        );
    }

    public function paginateReflectionHistoryRecent(Request $request, $role, $schoolName, $schoolId)
    {
        $user = Auth::user();

        $reflection = SchReflQuestion::with(['SchReflTarget.Kelas.SchoolClass' => function ($query) {
                $query->withCount('StudentSchoolClass');
            }
        ])->where('school_partner_id', $schoolId)->latest()
        ->withCount([
            'SchReflAnswer as total_responden' => function ($query) {
                $query->select(DB::raw('COUNT(DISTINCT user_id)')); // Menghitung jumlah unik dari user_id
            }
        ])->take(5)->get();

        return response()->json([
            'data' => $reflection,
            'reflectionHistoryDetail' => '/lms/:role/:schoolName/:schoolId/reflection-management/history-detail/:reflectionQuestionId',
        ]);
    }

    public function paginateDailyReflectionLivePreview(Request $request, $role, $schoolName, $schoolId) 
    {
        $timezone = request()->header('X-Timezone', 'Asia/Jakarta');

        $today = now($timezone)->toDateString();

        $reflections = SchReflQuestion::where('school_partner_id', $schoolId)->whereDate('created_at', $today)->latest()->paginate(1);

        if ($reflections->isEmpty()) {

            return response()->json([
                'data' => [],
                'pagination' => null
            ]);
        }

        $emotionConfig = config('reflection-management.emotion-status');

        $formattedReflections = $reflections->getCollection()->map(function ($reflection) use ($emotionConfig) {

            $totalAnswers = SchReflAnswer::where(
                'sch_refl_question_id',
                $reflection->id
            )->count();

            $emotionCounts = SchReflAnswer::select(
                    'emotion_status',
                    DB::raw('COUNT(*) as total')
                )
                ->where('sch_refl_question_id', $reflection->id)
                ->groupBy('emotion_status')
                ->pluck('total', 'emotion_status');

            $formattedEmotions = collect($emotionConfig)->map(function ($emotion) use ($emotionCounts, $totalAnswers) {

                $total = $emotionCounts[$emotion['value']] ?? 0;

                preg_match(
                    '/hover:bg-(\w+)-50/',
                    $emotion['classes']['hover'],
                    $matches
                );

                $color = $matches[1] ?? 'slate';

                return [
                    'label' => $emotion['label'],
                    'value' => $emotion['value'],
                    'icon' => $emotion['icon'],
                    'color' => $color,
                    'total' => $total,
                    'percentage' => $totalAnswers > 0
                        ? round(($total / $totalAnswers) * 100)
                        : 0,
                ];
            });

            return [
                'id' => $reflection->id,
                'title' => $reflection->title,
                'question' => $reflection->question,
                'created_at' => $reflection->created_at,
                'total_answers' => $totalAnswers,
                'emotions' => $formattedEmotions,
            ];
        });

        return response()->json([
            'data' => $formattedReflections,
            'links' => (string) $reflections->links(),
        ]);
    }

    public function createReflectionView($role, $schoolName, $schoolId)
    {
        return view('features.lms.student-vice-principal.reflection-management.create-reflection', compact('role', 'schoolName', 'schoolId'));
    }

    public function reflectionManagementForm(Request $request, $role, $schoolName, $schoolId)
    {
        $query = SchoolClass::where('school_partner_id', $schoolId);

        $tahunAjaran = $query->pluck('tahun_ajaran')->unique()->sortDesc()->values();

        $searchYear = $request->filled('search_year') ? $request->search_year : ($tahunAjaran->first() ?? null);

        $kelas = Kelas::whereIn('id', function ($query) use ($schoolId, $searchYear) {
            $query->select('kelas_id')->from('school_classes')->where('school_partner_id', $schoolId)->where('tahun_ajaran', $searchYear)->distinct();
        })->withCount([
            'schoolClass as total_rombel' => function ($query) use ($schoolId, $searchYear) {
                $query->where('school_partner_id', $schoolId)->where('tahun_ajaran', $searchYear);
            }
        ])
        ->get()
        ->map(function ($kelasItem) use ($schoolId, $searchYear) {

            // ambil school class id
            $schoolClassIds = SchoolClass::where('school_partner_id', $schoolId)->where('tahun_ajaran', $searchYear)->where('kelas_id', $kelasItem->id)->pluck('id');

            // hitung total siswa aktif
            $totalSiswa = StudentSchoolClass::whereIn('school_class_id', $schoolClassIds)->where('student_class_status', 'active')->count();

            return [
                'id' => $kelasItem->id,
                'kelas' => $kelasItem->kelas,
                'total_rombel' => $kelasItem->total_rombel,
                'total_siswa' => $totalSiswa,
            ];
        });

        return response()->json([
            'kelas' => $kelas,
            'tahunAjaran' => $tahunAjaran,
            'selectedYear' => $searchYear
        ]);
    }

    public function reflectionManagementStore(Request $request, $role, $schoolName, $schoolId) {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'question' => 'required',
            'target_class_id' => 'required|array|min:1',
            'target_class_id.*' => 'required|integer',
            'tahun_ajaran' => 'required',
        ], [
            'title.required' => 'Harap isi judul refleksi.',
            'question.required' => 'pertanyaan refleksi tidak boleh kosong.',
            'target_class_id.required' => 'Harap pilih jenjang kelas.',
            'tahun_ajaran.required' => 'Harap pilih tahun ajaran.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $reflection = SchReflQuestion::create([
            'user_id' => $user->id,
            'school_partner_id' => $schoolId,
            'title' => $request->title,
            'question' => $request->question,
            'tahun_ajaran' => $request->tahun_ajaran,
        ]);

        foreach ($request->target_class_id as $index => $targetClassId) {
            SchReflTarget::create([
                'sch_refl_question_id' => $reflection->id,
                'target_class_id' => $targetClassId
            ]);
        }

        broadcast(new DailyReflectionLivePreview('SchReflQuestion', 'create', $reflection));
        broadcast(new StudentDailyReflectionForm('SchReflQuestion', 'create', $reflection));

        return response()->json([
            'message' => 'Refleksi berhasil disimpan',
            'reflection' => $reflection
        ]);
    }

    public function reflectionManagementHistoryView($role, $schoolName, $schoolId) {
        return view('features.lms.student-vice-principal.reflection-management.reflection-management-history', compact('role', 'schoolName', 'schoolId'));
    }

    public function paginateReflectionHistory($role, $schoolName, $schoolId)
    {
        $SchReflQuestion = SchReflQuestion::with(['SchReflTarget.Kelas.SchoolClass' => function ($query) {
                $query->withCount('StudentSchoolClass');
            }
        ])->where('school_partner_id', $schoolId)->latest()->withCount([
            'SchReflAnswer as total_responden' => function ($query) {
                $query->select(DB::raw('COUNT(DISTINCT user_id)')); // Menghitung jumlah unik dari user_id
            }
        ])->paginate(10);

        return response()->json([
            'data' => $SchReflQuestion->items(),
            'links' => (string) $SchReflQuestion->links(),
            'current_page' => $SchReflQuestion->currentPage(),
            'per_page' => $SchReflQuestion->perPage(),
            'historyDetail' => '/lms/:role/:schoolName/:schoolId/reflection-management/history-detail/:reflectionQuestionId',
        ]);
    }

    public function reflectionManagementHistoryDetailView($role, $schoolName, $schoolId, $reflectionQuestionId) {
        $emotionConfig = config('reflection-management.emotion-status');

        $categories = collect($emotionConfig)->groupBy('category');

        $emotionTitles = $categories->map(function ($items) {
            return 'Gabungan status emosi ' . $items->pluck('label')->implode(' dan ');
        });

        return view('features.lms.student-vice-principal.reflection-management.reflection-management-history-detail', compact('role', 'schoolName', 'schoolId', 
        'reflectionQuestionId', 'emotionTitles'));
    }

    public function loadReflectionDetailHeader($role, $schoolName, $schoolId, $reflectionQuestionId)
    {
        $SchReflQuestion = SchReflQuestion::with(['SchReflTarget.Kelas.SchoolClass' => function ($query) {
                $query->withCount('StudentSchoolClass');
            }
        ])->where('school_partner_id', $schoolId)->latest()->withCount([
            'SchReflAnswer as total_responden' => function ($query) {
                $query->select(DB::raw('COUNT(DISTINCT user_id)')); // Menghitung jumlah unik dari user_id
            }
        ])->findOrFail($reflectionQuestionId);

        return response()->json([
            'data' => $SchReflQuestion
        ]);
    }

    public function loadReflectionDetailSummary($role, $schoolName, $schoolId, $reflectionQuestionId)
    {
        $query = SchReflQuestion::with(['SchReflTarget.Kelas.SchoolClass' => function ($query) {
                $query->withCount('StudentSchoolClass');
            }
        ])->where('school_partner_id', $schoolId)->latest()->withCount([
            'SchReflAnswer as total_responden' => function ($query) {
                $query->select(DB::raw('COUNT(DISTINCT user_id)')); // Menghitung jumlah unik dari user_id
            }
        ])->findOrFail($reflectionQuestionId);

        $tahunAjaran = $query->pluck('tahun_ajaran')->unique()->sortDesc()->values();

        $kelas = Kelas::whereIn('id', function ($query) use ($schoolId, $tahunAjaran) {
            $query->select('kelas_id')->from('school_classes')->where('school_partner_id', $schoolId)->where('tahun_ajaran', $tahunAjaran)->distinct();
        })->withCount([
            'schoolClass as total_rombel' => function ($query) use ($schoolId, $tahunAjaran) {
                $query->where('school_partner_id', $schoolId)->where('tahun_ajaran', $tahunAjaran);
            }
        ])
        ->get()
        ->map(function ($kelasItem) use ($schoolId, $tahunAjaran) {

            // ambil school class id
            $schoolClassIds = SchoolClass::where('school_partner_id', $schoolId)->where('tahun_ajaran', $tahunAjaran)->where('kelas_id', $kelasItem->id)->pluck('id');

            // hitung total siswa aktif
            $totalSiswa = StudentSchoolClass::whereIn('school_class_id', $schoolClassIds)->where('student_class_status', 'active')->count();

            return [
                'id' => $kelasItem->id,
                'kelas' => $kelasItem->kelas,
                'total_rombel' => $kelasItem->total_rombel,
                'total_siswa' => $totalSiswa,
            ];
        });

        $totalSiswa = $kelas->sum('total_siswa');

        $totalResponden = $query->total_responden;

        $participationPercentage = $totalSiswa > 0 ? round(($totalResponden / $totalSiswa) * 100, 1) : 0;

        $emotionConfig = config('reflection-management.emotion-status');

        $categories = collect($emotionConfig)->groupBy('category');

        $positiveStatuses = $categories->get('positive', collect())->pluck('value')->toArray();

        $neutralStatuses = $categories->get('neutral', collect())->pluck('value')->toArray();

        $attentionStatuses = $categories->get('attention', collect())->pluck('value')->toArray();

        $positive = SchReflAnswer::where('sch_refl_question_id', $reflectionQuestionId)->whereIn('emotion_status', $positiveStatuses)->count();

        $neutral = SchReflAnswer::where('sch_refl_question_id', $reflectionQuestionId)->whereIn('emotion_status', $neutralStatuses)->count();

        $attention = SchReflAnswer::where('sch_refl_question_id', $reflectionQuestionId)->whereIn('emotion_status', $attentionStatuses)->count();

        return response()->json([
            'data' => $query,
            'kelas' => $kelas,
            'tahunAjaran' => $tahunAjaran,
            'participation_percentage' => $participationPercentage,
            'positive' => $positive,
            'neutral' => $neutral,
            'attention' => $attention
        ]);
    }

    public function loadReflectionDetailChart($role, $schoolName, $schoolId, $reflectionQuestionId)
    {
        $reflection = SchReflQuestion::findOrFail($reflectionQuestionId);

        // CHART BAR
        $reflectionTargets = SchReflTarget::with('Kelas')->where('sch_refl_question_id', $reflectionQuestionId)->get();
        
        $targetClasses = $reflectionTargets->map(function ($target) {
            return $target->Kelas->kelas;
        });

        $targetClassIds = SchReflTarget::where('sch_refl_question_id', $reflectionQuestionId)->pluck('target_class_id');

        $schoolClasses = SchoolClass::with('Kelas')->where('school_partner_id', $schoolId)->where('tahun_ajaran', $reflection->tahun_ajaran)
            ->whereIn('kelas_id', $targetClassIds)->get();

        $barChart = $schoolClasses->map(function ($schoolClass) use ($reflectionQuestionId) {
            $studentIds = StudentSchoolClass::where('school_class_id', $schoolClass->id)->where('student_class_status', 'active')->pluck('student_id');

            $totalSiswa = $studentIds->count();

            $answered = SchReflAnswer::where('sch_refl_question_id', $reflectionQuestionId)->whereIn('user_id', $studentIds)->count();

            return [
                'kelas' => $schoolClass->class_name,
                'sudah_menjawab' => $answered,
                'belum_menjawab' => max(0, $totalSiswa - $answered),
            ];
        });

        // CHART DOUGHNUT
        $emotionConfig = config('reflection-management.emotion-status');

        $emotionsCounts = SchReflAnswer::where('sch_refl_question_id', $reflectionQuestionId)
        ->select('emotion_status', DB::raw('count(*) as total'))->groupBy('emotion_status')->pluck('total', 'emotion_status');

        $labels = collect($emotionConfig)->pluck('label')->toArray();

        $colors = collect($emotionConfig)->pluck('chart_color')->toArray();

        $data = collect($emotionConfig)->map(function ($emotion) use ($emotionsCounts) {
            return $emotionsCounts[$emotion['value']] ?? 0;
        });

        return response()->json([
            'reflection' => $reflection,

            'reflection_answered' => [
                'labels' => $targetClasses,
                'answered' => $barChart->pluck('sudah_menjawab'),
                'unanswered' => $barChart->pluck('belum_menjawab'),
            ],

            'emotion_chart' => [
                'labels' => $labels,
                'data' => $data,
                'colors' => $colors,
            ]
        ]);
    }

    public function paginateReflectionStudentAnswer(Request $request, $role, $schoolName, $schoolId, $reflectionQuestionId)
    {
        $reflectionAnswered = SchReflAnswer::with(['SchoolClass', 'UserAccount.StudentProfile'])
        ->where('sch_refl_question_id', $reflectionQuestionId)->latest()->paginate(10);

        $emotionConfig = collect(config('reflection-management.emotion-status'))->keyBy('value');

        $reflectionAnswered->getCollection()->transform(function ($item) use ($emotionConfig) {

            $emotion = $emotionConfig->get($item->emotion_status);

            preg_match('/hover:bg-(\w+)-50/', $emotion['classes']['hover'] ?? '', $matches);

            $item->emotion_color = $matches[1] ?? 'slate';

            return $item;
        });
        return response()->json([
            'data' => $reflectionAnswered->items(),
            'links' => (string) $reflectionAnswered->links(),
            'current_page' => $reflectionAnswered->currentPage(),
            'per_page' => $reflectionAnswered->perPage(),
        ]);
    }
}
