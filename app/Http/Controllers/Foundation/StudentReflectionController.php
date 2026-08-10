<?php

namespace App\Http\Controllers\Foundation;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\SchoolPartner;
use App\Models\SchReflAnswer;
use App\Models\SchReflQuestion;
use App\Models\StudentProfile;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class StudentReflectionController extends Controller
{
    public function studentReflectionView($role, $foundationId = null)
    {
        return view('features.lms.foundation.student-reflection.monitoring-student-reflection', compact('role', 'foundationId'));
    }

    public function loadReflectionYears($role, $foundationId = null)
    {
        $availableYears = collect();
        
        if ($foundationId) {
            $query = SchReflAnswer::whereHas('SchReflQuestion.schoolPartner', function ($query) use ($foundationId) {
                $query->where('school_foundation_id', $foundationId);
            });
    
            $availableYears = (clone $query)->selectRaw('YEAR(created_at) as year')->distinct()->orderByDesc('year')->pluck('year')->values();
        }

        return response()->json([
            'years' => $availableYears,
        ]);
    }

    public function studentReflectionKPI(Request $request, $role, $foundationId = null)
    {
        if ($foundationId) {
            $year = $request->year ?? now()->year;
    
            $totalStudents = StudentProfile::whereHas('SchoolPartner', function ($query) use ($foundationId) {
                $query->where('school_foundation_id', $foundationId);
            })->count();
    
            $totalReflection = SchReflQuestion::whereHas('schoolPartner', function ($query) use ($foundationId, $year) {
                $query->where('school_foundation_id', $foundationId);
            })->whereYear('created_at', $year)->count();
    
            $emotionCounts = SchReflAnswer::select('emotion_status', DB::raw('COUNT(*) as total'))->whereHas('SchReflQuestion.schoolPartner', function ($q) use ($foundationId, $year) {
                $q->where('school_foundation_id', $foundationId);
            })->whereHas('SchReflQuestion', function ($qq) use ($year) {
                $qq->whereYear('created_at', $year);
            })->groupBy('emotion_status')->get();
    
            $positiveEmotions = collect(config('reflection-management.emotion-status'))->where('category', 'positive')->pluck('value')->toArray();
    
            // Total seluruh jawaban
            $totalAnswers = $emotionCounts->sum('total');
    
            // Total jawaban positif
            $positiveCount = $emotionCounts->whereIn('emotion_status', $positiveEmotions)->sum('total');
    
            $positivePercentage = $totalAnswers > 0 ? round(($positiveCount / $totalAnswers) * 100, 1) : 0;
    
            // Emosi dominan
            $maxTotal = $emotionCounts->max('total');
    
            $dominantEmotions = $emotionCounts->where('total', $maxTotal)->values();
    
            $totalPossibleAnswers = $totalReflection * $totalStudents;
    
            $completionPercentage = $totalPossibleAnswers > 0 ? round(($totalAnswers / $totalPossibleAnswers) * 100, 1) : 0;
    
            $emotionConfig = collect(config('reflection-management.emotion-status'));
    
            $dominantEmotionData = null;
    
            // tampilkan jika benar-benar ada satu emosi dominan
            if ($dominantEmotions->count() === 1) {
    
                $emotion = $dominantEmotions->first();
    
                $config = $emotionConfig->firstWhere('value', $emotion->emotion_status);
    
                $dominantEmotionData = [
                    'label'      => $config['label'] ?? '-',
                    'value'      => $config['value'] ?? '-',
                    'category'   => $config['category'] ?? null,
                    'icon'       => $config['icon'] ?? 'fa-face-meh',
                    'total'      => $emotion->total,
                    'percentage' => $totalAnswers > 0 ? round(($emotion->total / $totalAnswers) * 100, 1) : 0,
                ];
            }
        }

        return response()->json([
            'total_reflection' => $totalReflection,
            'dominant_emotion' => $dominantEmotionData,
            'dominant_percentage' => $dominantEmotionData ? $dominantEmotionData['percentage'] : null,
            'positive_percentage' => $positivePercentage,
            'positive_emotions' => $positiveEmotions,
            'completionPercentage_percentage' => $completionPercentage,
        ]);
    }

    public function loadEmotionOverview(Request $request, $role, $foundationId = null)
    {
        $emotionConfig = collect(config('reflection-management.emotion-status'));

        $year = $request->year ?? now()->year;

        // ambil seluruh emosi
        $emotionCounts = SchReflAnswer::select('emotion_status', DB::raw('COUNT(*) as total'))->whereHas('SchReflQuestion.schoolPartner', function ($query) use ($foundationId) {
            $query->where('school_foundation_id', $foundationId);
        })->whereHas('SchReflQuestion', function ($qq) use ($year) {
            $qq->whereYear('created_at', $year);
        })->groupBy('emotion_status')->pluck('total', 'emotion_status');

        $totalAnswers = $emotionCounts->sum();

        $hasData = $totalAnswers > 0;

        $emotions = $emotionConfig->map(function ($emotion) use ($emotionCounts, $totalAnswers) {

            $total = $emotionCounts[$emotion['value']] ?? 0;

            return [
                'label' => $emotion['label'],
                'value' => $emotion['value'],
                'category' => $emotion['category'],
                'icon' => $emotion['icon'],
                'chart_color' => $emotion['chart_color'],
                'total' => $total,
                'percentage' => $totalAnswers > 0 ? round(($total / $totalAnswers) * 100, 1) : 0,
            ];
        });

        // Persentase emosi positif
        $positiveValues = $emotionConfig->where('category', 'positive')->pluck('value');
        $positiveCount = $emotions->whereIn('value', $positiveValues)->sum('total');
        $positivePercentage = $totalAnswers > 0 ? round(($positiveCount / $totalAnswers) * 100, 1) : 0;

        // Total siswa
        $totalStudents = StudentProfile::whereHas('SchoolPartner', function ($query) use ($foundationId) {
            $query->where('school_foundation_id', $foundationId);
        })->count();

        // Total refleksi
        $totalReflection = SchReflQuestion::whereHas('schoolPartner', function ($query) use ($foundationId) {
            $query->where('school_foundation_id', $foundationId);
        })->whereYear('created_at', $year)->count();

        // Total jawaban
        $totalPossibleAnswers = $totalStudents * $totalReflection;

        // Persentase pengisian refleksi
        $completionPercentage = $totalPossibleAnswers > 0 ? round(($totalAnswers / $totalPossibleAnswers) * 100, 1) : 0;

        // Emosi dominan
        $dominantEmotions = collect();

        if ($totalAnswers > 0) {
            $maxTotal = $emotions->max('total');

            $dominantEmotions = $emotions
                ->where('total', $maxTotal)
                ->values();
        }

        $insights = [];

        if ($hasData) {
            // Insight kondisi positif
            if ($positivePercentage >= 85) {
    
                $insights[] = [
                    'type' => 'success',
                    'icon' => 'fa-circle-check',
                    'title' => 'Kondisi Sangat Baik',
                    'message' => "Sebanyak {$positivePercentage}% jawaban menunjukkan emosi positif. Kondisi emosional siswa secara umum sangat baik."
                ];
    
            } elseif ($positivePercentage >= 70) {
    
                $insights[] = [
                    'type' => 'info',
                    'icon' => 'fa-face-smile',
                    'title' => 'Kondisi Baik',
                    'message' => "{$positivePercentage}% jawaban menunjukkan emosi positif. Tetap lakukan monitoring secara berkala."
                ];
    
            } else {
    
                $insights[] = [
                    'type' => 'warning',
                    'icon' => 'fa-triangle-exclamation',
                    'title' => 'Perlu Perhatian',
                    'message' => "Persentase emosi positif baru mencapai {$positivePercentage}%. Yayasan disarankan melakukan evaluasi lebih lanjut."
                ];
            }
    
            // Insight pengisian refleksi
            if ($completionPercentage >= 90) {
    
                $insights[] = [
                    'type' => 'success',
                    'icon' => 'fa-user-check',
                    'title' => 'Pengisian Sangat Tinggi',
                    'message' => "{$completionPercentage}% dari seluruh refleksi yang tersedia telah diisi sehingga data yang diperoleh sangat representatif."
                ];
    
            } elseif ($completionPercentage >= 60) {
    
                $insights[] = [
                    'type' => 'info',
                    'icon' => 'fa-users',
                    'title' => 'Pengisian Cukup',
                    'message' => "{$completionPercentage}% refleksi telah diisi. Tingkatkan pengisian agar analisis semakin akurat."
                ];
    
            } else {
    
                $insights[] = [
                    'type' => 'warning',
                    'icon' => 'fa-user-clock',
                    'title' => 'Pengisian Rendah',
                    'message' => "Baru {$completionPercentage}% refleksi yang telah diisi. Dorong sekolah meningkatkan tingkat pengisian refleksi."
                ];
    
            }
    
            // Insight emosi dominan / seimbang
            if ($dominantEmotions->count() === 1) {
    
                $emotion = $dominantEmotions->first();
    
                $insights[] = [
                    'type' => $emotion['category'],
                    'icon' => $emotion['icon'],
                    'title' => 'Emosi Dominan',
                    'message' => "{$emotion['label']} merupakan emosi yang paling banyak muncul ({$emotion['percentage']}% dari seluruh jawaban)."
                ];
    
            } elseif ($dominantEmotions->count() > 1) {
    
                $emotionLabels = $dominantEmotions->pluck('label')->implode(', ');
                $percentage = $dominantEmotions->first()['percentage'];
    
                $insights[] = [
                    'type' => 'info',
                    'icon' => 'fa-scale-balanced',
                    'title' => 'Emosi Seimbang',
                    'message' => "Tidak terdapat satu emosi yang benar-benar mendominasi. {$emotionLabels} memiliki proporsi yang sama ({$percentage}% masing-masing)."
                ];
            }
        }


        return response()->json([
            'labels' => $emotions,
            'has_data' => $hasData,

            'chart' => [
                'labels' => $emotions->pluck('label'),
                'data' => $emotions->pluck('total'),
                'colors' => $emotions->pluck('chart_color'),
            ],

            'insights' => $insights,
        ]);
    }

    public function loadReflectionTrend(Request $request, $role, $foundationId = null)
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

        if (!$foundationId) {
            return response()->json([
                'title' => null,
                'labels' => [],
                'data' => [],
            ]);
        }

        $query = SchReflAnswer::whereHas(
            'SchReflQuestion.schoolPartner',
            function ($query) use ($foundationId) {
                $query->where('school_foundation_id', $foundationId);
            }
        );

        switch ($period) {

            case 'daily':

                $rows = (clone $query)->selectRaw('DAY(created_at) as label')->selectRaw('COUNT(*) as total')->whereYear('created_at', $year)->whereMonth('created_at', $month)
                    ->groupBy('label')->orderBy('label')->get();

                $labels = collect(
                    range(1, Carbon::create($year, $month)->daysInMonth)
                )->map(function ($day) {
                    return [
                        'key' => $day,
                        'label' => $day,
                    ];
                });

                $title = "Trend Harian {$months[$month]} {$year}";

                break;

            case 'weekly':

                $rows = (clone $query)->selectRaw('CEIL(DAY(created_at) / 7) as label')->selectRaw('COUNT(*) as total')->whereYear('created_at', $year)
                    ->whereMonth('created_at', $month)->groupBy('label')->orderBy('label')->get();

                $labels = collect(
                    range(1, ceil(Carbon::create($year, $month)->daysInMonth / 7))
                )->map(function ($week) {
                    return [
                        'key' => $week,
                        'label' => "Minggu {$week}",
                    ];
                });

                $title = "Trend Mingguan {$months[$month]} {$year}";

                break;

            case 'monthly':

                $rows = (clone $query)
                    ->selectRaw('MONTH(created_at) as label')
                    ->selectRaw('COUNT(*) as total')
                    ->whereYear('created_at', $year)
                    ->groupBy('label')
                    ->orderBy('label')
                    ->get();

                $labels = collect(range(1, 12))
                    ->map(function ($monthNumber) use ($months) {
                        return [
                            'key' => $monthNumber,
                            'label' => $months[$monthNumber],
                        ];
                    });

                $title = "Trend Bulanan Tahun {$year}";

                break;

            default:

                $rows = (clone $query)
                    ->selectRaw('YEAR(created_at) as label')
                    ->selectRaw('COUNT(*) as total')
                    ->groupBy('label')
                    ->orderBy('label')
                    ->get();

                $labels = $rows
                    ->pluck('label')
                    ->unique()
                    ->values()
                    ->map(function ($year) {
                        return [
                            'key' => $year,
                            'label' => $year,
                        ];
                    });

                $title = "Trend Tahunan";

                break;
        }

        $rowMap = $rows->pluck('total', 'label');

        $data = $labels->map(function ($item) use ($rowMap) {
            return $rowMap[$item['key']] ?? 0;
        });

        return response()->json([
            'title' => $title,
            'labels' => $labels->pluck('label')->values(),
            'data' => $data->values(),
        ]);
    }

    public function loadEmotionTrend(Request $request, $role, $foundationId = null)
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
        $year   = $request->year ?? now()->year;
        $month  = $request->month ?? now()->month;

        if (!$foundationId) {
            return response()->json([
                'title' => null,
                'labels' => [],
                'data' => [],
            ]);
        }

        $emotionConfig = collect(config('reflection-management.emotion-status'));

        $query = SchReflAnswer::whereHas('UserAccount.StudentProfile.schoolPartner', function ($query) use ($foundationId) {
                $query->where('school_foundation_id', $foundationId);
            }
        );

        switch ($period) {

            case 'daily':

                $rows = (clone $query)->selectRaw('DAY(created_at) as label')->selectRaw('emotion_status')->selectRaw('COUNT(*) as total')
                ->whereYear('created_at', $year)->whereMonth('created_at', $month)->groupBy('label', 'emotion_status')->orderBy('label')->get();

                $labels = collect(range(1, Carbon::create($year, $month)->daysInMonth));

                $title = "Tren Harian {$months[$month]} {$year}";
                break;

            case 'weekly':

                $rows = (clone $query)->selectRaw('CEIL(DAY(created_at)/7) as label')->selectRaw('emotion_status')->selectRaw('COUNT(*) as total')
                ->whereYear('created_at', $year)->whereMonth('created_at', $month)->groupBy('label', 'emotion_status')->orderBy('label')->get();

                $labels = collect(range(1, ceil(Carbon::create($year, $month)->daysInMonth / 7)));

                $title = "Tren Mingguan {$months[$month]} {$year}";
                break;

            case 'monthly':

                $rows = (clone $query)->selectRaw('MONTH(created_at) as label')->selectRaw('emotion_status')->selectRaw('COUNT(*) as total')
                ->whereYear('created_at', $year)->groupBy('label', 'emotion_status')->orderBy('label')->get();

                $labels = collect(range(1, 12));

                $title = "Tren Bulanan Tahun {$year}";
                break;

            default:

                $rows = (clone $query)->selectRaw('YEAR(created_at) as label')->selectRaw('emotion_status')->selectRaw('COUNT(*) as total')
                ->groupBy('label', 'emotion_status')->orderBy('label')->get();

                $labels = $rows->pluck('label')->unique()->values();

                $title = "Tren Tahunan";
                break;
        }

        $indexedRows = $rows->keyBy(function ($row) {
            return "{$row->label}_{$row->emotion_status}";
        });

        $periodTotals = $rows->groupBy('label')->map(fn($items) => $items->sum('total'));

        $datasets = $emotionConfig->map(function ($emotion) use ($labels, $indexedRows, $periodTotals) {
            $percentages = [];
            $totals = [];

            foreach ($labels as $label) {

                $count = optional($indexedRows->get("{$label}_{$emotion['value']}"))->total ?? 0;

                $totalReflection = $periodTotals[$label] ?? 0;

                $totals[] = $count;

                $percentages[] = $totalReflection > 0 ? round(($count / $totalReflection) * 100, 1) : 0;
            }

            return [
                'label' => $emotion['label'],
                'data' => $percentages,
                'totals' => $totals,

                'borderColor' => $emotion['chart_color'],
                'backgroundColor' => $emotion['chart_color'],
                'pointBackgroundColor' => $emotion['chart_color'],

                'fill' => false,
                'tension' => 0.35,
                'borderWidth' => 3,
                'pointRadius' => 4,
                'pointHoverRadius' => 6,
            ];
        })->values();

        if ($period === 'weekly') {

            $labels = $labels->map(fn($week) => "Minggu {$week}");

        } elseif ($period === 'monthly') {

            $labels = $labels->map(fn($monthNumber) => $months[$monthNumber]);
        }

        return response()->json([
            'title' => $title,
            'labels' => $labels,
            'datasets' => $datasets,
        ]);
    }

    public function paginateSchoolReflectionSummary(Request $request, $role, $foundationId = null)
    {
        $academicYears = SchoolClass::whereHas('SchoolPartner', function ($query) use ($foundationId) {
            $query->where('school_foundation_id', $foundationId);
        })->select('tahun_ajaran')->distinct()->orderByDesc('tahun_ajaran')->pluck('tahun_ajaran');

        $selectedYear = $request->search_year ?? $academicYears->first();

        $positiveEmotions = collect(config('reflection-management.emotion-status'))->where('category', 'positive')->pluck('value')->toArray();

        // school list
        $schools = SchoolPartner::where('school_foundation_id', $foundationId)->get(['id', 'nama_sekolah']);

        // reflection count
        $reflectionCounts = SchReflQuestion::select('school_partner_id', DB::raw('COUNT(*) as total'))->where('tahun_ajaran', $selectedYear)
        ->groupBy('school_partner_id')->pluck('total', 'school_partner_id');

        // student count
        $studentCounts = StudentProfile::select('school_partner_id', DB::raw('COUNT(*) as total'))->groupBy('school_partner_id')->pluck('total', 'school_partner_id');

        // total answers
        $answerCounts = SchReflAnswer::select('sch_refl_questions.school_partner_id', DB::raw('COUNT(*) as total'))->join('sch_refl_questions', 
            'sch_refl_questions.id', '=', 'sch_refl_answers.sch_refl_question_id')->where('sch_refl_questions.tahun_ajaran', $selectedYear)
        ->groupBy('sch_refl_questions.school_partner_id')->pluck('total', 'school_partner_id');

        // positive answers
        $positiveCounts = SchReflAnswer::select('sch_refl_questions.school_partner_id', DB::raw('COUNT(*) as total'))->join('sch_refl_questions', 
            'sch_refl_questions.id', '=', 'sch_refl_answers.sch_refl_question_id')->where('sch_refl_questions.tahun_ajaran', $selectedYear)
        ->whereIn('sch_refl_answers.emotion_status', $positiveEmotions)->groupBy('sch_refl_questions.school_partner_id')->pluck('total', 'school_partner_id');

        $schools = $schools->map(function ($school) use ($reflectionCounts, $studentCounts, $answerCounts, $positiveCounts) {
            $reflectionCount = $reflectionCounts[$school->id] ?? 0;
            $totalStudents = $studentCounts[$school->id] ?? 0;
            $totalAnswers = $answerCounts[$school->id] ?? 0;
            $positiveCount = $positiveCounts[$school->id] ?? 0;
            $totalExpectedAnswers = $reflectionCount * $totalStudents;

            return [
                'nama_sekolah' => $school->nama_sekolah,
                'reflection_count' => $reflectionCount,
                'completion_percentage' => $totalExpectedAnswers > 0 ? round(($totalAnswers / $totalExpectedAnswers) * 100, 1) : 0,
                'positive_percentage' => $totalAnswers > 0 ? round(($positiveCount / $totalAnswers) * 100, 1) : 0,
            ];
        });

        $page = $request->get('page', 1);
        $perPage = 10;

        $paginated = new LengthAwarePaginator(
            $schools->forPage($page, $perPage)->values(),
            $schools->count(),
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );

        return response()->json([
            'data' => $paginated->items(),
            'academic_years' => $academicYears,
            'selected_year' => $selectedYear,
            'links' => (string) $paginated->links(),
            'current_page' => $paginated->currentPage(),
            'per_page' => $paginated->perPage(),
        ]);
    }

    public function paginateSchoolReflectionAttention(Request $request, $role, $foundationId = null)
    {
        $academicYears = SchoolClass::whereHas('SchoolPartner', function ($query) use ($foundationId) {
            $query->where('school_foundation_id', $foundationId);
        })->select('tahun_ajaran')->distinct()->orderByDesc('tahun_ajaran')->pluck('tahun_ajaran');

        $selectedYear = $request->search_year ?? $academicYears->first();

        $positiveEmotions = collect(config('reflection-management.emotion-status'))->where('category', 'positive')->pluck('value')->toArray();

        // school list
        $schools = SchoolPartner::where('school_foundation_id', $foundationId)->get(['id', 'nama_sekolah']);

        // reflection count
        $reflectionCounts = SchReflQuestion::select('school_partner_id', DB::raw('COUNT(*) as total'))->where('tahun_ajaran', $selectedYear)
        ->groupBy('school_partner_id')->pluck('total', 'school_partner_id');

        // student count
        $studentCounts = StudentProfile::select('school_partner_id', DB::raw('COUNT(*) as total'))->groupBy('school_partner_id')->pluck('total', 'school_partner_id');

        // Total Reflection Answer
        $answerCounts = SchReflAnswer::select('sch_refl_questions.school_partner_id', DB::raw('COUNT(*) as total'))->join('sch_refl_questions', 'sch_refl_questions.id',
            '=', 'sch_refl_answers.sch_refl_question_id' )->where('sch_refl_questions.tahun_ajaran', $selectedYear)->groupBy('sch_refl_questions.school_partner_id')
        ->pluck('total', 'school_partner_id');

        // Positive Answer Count
        $positiveCounts = SchReflAnswer::select('sch_refl_questions.school_partner_id', DB::raw('COUNT(*) as total'))->join('sch_refl_questions', 'sch_refl_questions.id',
            '=', 'sch_refl_answers.sch_refl_question_id')->where('sch_refl_questions.tahun_ajaran', $selectedYear)
        ->whereIn('sch_refl_answers.emotion_status', $positiveEmotions)->groupBy('sch_refl_questions.school_partner_id')->pluck('total', 'school_partner_id');

        $schools = $schools->map(function ($school) use ($reflectionCounts, $studentCounts, $answerCounts, $positiveCounts) {
            $reflectionCount = $reflectionCounts[$school->id] ?? 0;
            $totalStudents = $studentCounts[$school->id] ?? 0;
            $totalAnswers = $answerCounts[$school->id] ?? 0;
            $positiveCount = $positiveCounts[$school->id] ?? 0;
            $totalExpectedAnswers = $reflectionCount * $totalStudents;

            $positivePercentage = $totalAnswers > 0 ? round(($positiveCount / $totalAnswers) * 100, 1) : 0;

            $completionPercentage = $totalExpectedAnswers > 0 ? round(($totalAnswers / $totalExpectedAnswers) * 100, 1) : 0;

            if ($positivePercentage <= 50) {
                $level = 'high';
                $badge = 'Tinggi';
                $border = 'red';

            } elseif ($positivePercentage < 75) {
                $level = 'medium';
                $badge = 'Sedang';
                $border = 'orange';

            } else {
                return null;
            }

            return [
                'school_id' => $school->id,
                'nama_sekolah' => $school->nama_sekolah,
                'total_students' => $totalStudents,
                'reflection_count' => $reflectionCount,
                'positive_percentage' => $positivePercentage,
                'completion_percentage' => $completionPercentage,
                'badge' => $badge,
                'level' => $level,
                'border' => $border,
            ];
        })->filter()->sortBy('positive_percentage')->values();

        return response()->json([
            'total_attention' => $schools->count(),
            'data' => $schools,
        ]);
    }
}