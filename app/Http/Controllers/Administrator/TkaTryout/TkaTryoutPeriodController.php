<?php

namespace App\Http\Controllers\Administrator\TkaTryout;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\TkaTryoutPeriod;
use App\Models\TkaTryoutPeriodSchOverride;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TkaTryoutPeriodController extends Controller
{
    public function index($role)
    {
        $academicYears = SchoolClass::query()->select('tahun_ajaran')->distinct()->orderByDesc('tahun_ajaran')->pluck('tahun_ajaran');

        return view('features.lms.administrator.tka-tryout.tka-tryout-period.tka-tryout-period-management', compact('role', 'academicYears'));
    }

    public function loadFilter($role)
    {
        $academicYears = SchoolClass::query()->select('tahun_ajaran')->distinct()->orderByDesc('tahun_ajaran')->pluck('tahun_ajaran');

        return response()->json([
            'academicYears' => $academicYears
        ]);
    }

    public function loadKpi(Request $request, $role)
    {
        $academicYear = $request->academic_year;

        $totalPeriod = TkaTryoutPeriod::where('tahun_ajaran', $academicYear)->count();

        $totalPeriodDefault = $totalPeriod;

        $totalPeriodOverride = TkaTryoutPeriod::where('tahun_ajaran', $academicYear)->whereHas('TkaTryoutPeriodSchOverride')->count();

        $totalSchoolOverride = TkaTryoutPeriodSchOverride::whereHas('TkaTryoutPeriod', function ($query) use ($academicYear) {
            $query->where('tahun_ajaran', $academicYear);
        })->distinct('school_partner_id')->count('school_partner_id');

        return response()->json([
            'total_tryout_tka_period' => $totalPeriod,
            'total_tryout_tka_period_default' => $totalPeriodDefault,
            'total_tryout_tka_period_override' => $totalPeriodOverride,
            'total_tryout_tka_school_override' => $totalSchoolOverride,
        ]);
    }

    public function paginateTkaTryoutPeriod(Request $request, $role)
    {
        $academicYear = $request->academic_year;

        $periods = TkaTryoutPeriod::where('tahun_ajaran', $academicYear)
            ->withCount([
                'TkaTryoutPeriodSchOverride as total_school_override',
                'TkaTryoutSession as total_session' => function ($query) {
                    $query->whereNull('tka_tryout_period_sch_override_id');
                },
                'TkaTryoutSubject as total_subject',
            ])
            ->paginate(20);

        return response()->json([
            'data' => $periods->items(),
        ]);
    }

    public function paginateTkaTryoutPeriodSchoolOverride($role, $periodOverrideId)
    {
        $query = TkaTryoutPeriodSchOverride::with(['TkaTryoutPeriod', 'SchoolPartner'])->where('tka_tryout_period_id', $periodOverrideId);

        $schoolOverrideCount = $query->count();

        $periodSchoolOverride = $query->withCount([
            'TkaTryoutSession as total_session',
            'TkaTryoutSubject as total_subject',
        ])->get();

        return response()->json([
            'data' => $periodSchoolOverride,
            'total_school_override' => $schoolOverrideCount
        ]);
    }

    public function viewTkaTryoutForm($role)
    {
        $academicYears = SchoolClass::query()->select('tahun_ajaran')->distinct()->orderByDesc('tahun_ajaran')->pluck('tahun_ajaran');

        return view('features.lms.administrator.tka-tryout.tka-tryout-period.tka-tryout-period-form', compact('role', 'academicYears'));
    }
    
    public function tkaTryoutPeriodCreate(Request $request, $role)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'tahun_ajaran' => [
                    'required',
                    'string',
                ],
                'start_date' => [
                    'required',
                    'date',
                ],
                'end_date' => [
                    'required',
                    'date',
                ],
            ],
            [
                'tahun_ajaran.required' => 'Harap pilih tahun ajaran.',
                'start_date.required' => 'Harap pilih tanggal mulai.',
                'start_date.date' => 'Format tanggal mulai tidak valid.',
                'end_date.required' => 'Harap pilih tanggal selesai.',
                'end_date.date' => 'Format tanggal selesai tidak valid.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $period = DB::transaction(function () use ($request) {
                $tahunAjaran = $request->tahun_ajaran;

                $lastPeriod = TkaTryoutPeriod::query()->where('tahun_ajaran', $tahunAjaran)->lockForUpdate()
                ->orderByDesc('period_number')->first();

                $periodNumber = $lastPeriod ? $lastPeriod->period_number + 1 : 1;

                $periodExists = TkaTryoutPeriod::query()->where('tahun_ajaran', $tahunAjaran)->where('period_number', $periodNumber)
                ->exists();

                if ($periodExists) {
                    throw new \RuntimeException('Periode Tryout TKA untuk tahun ajaran tersebut sudah tersedia.');
                }

                return TkaTryoutPeriod::create([
                    'user_id' => Auth::id(),
                    'tahun_ajaran' => $tahunAjaran,
                    'period_number' => $periodNumber,
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                    'is_review' => $request->boolean('is_review'),
                ]);
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Periode Tryout TKA berhasil dibuat.',
                'data' => [
                    'id' => $period->id,
                    'tahun_ajaran' => $period->tahun_ajaran,
                    'period_number' => $period->period_number,
                ],
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'status' => 'error',
                'errors' => [
                    'tahun_ajaran' => [
                        $e->getMessage(),
                    ],
                ],
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat membuat periode Tryout TKA.',
            ], 500);
        }
    }

    public function tkaTryoutPeriodUpdate(Request $request, $role, $periodId) 
    {
        $validator = Validator::make(
            $request->all(),
            [
                'tahun_ajaran' => [
                    'required',
                    'string',
                ],
                'start_date' => [
                    'required',
                    'date',
                ],
                'end_date' => [
                    'required',
                    'date',
                ],
                'is_review' => [
                    'required',
                    'boolean',
                ],
            ],
            [
                'tahun_ajaran.required' => 'Harap isi tahun ajaran.',
                'tahun_ajaran.string' => 'Tahun ajaran harus berupa teks.',

                'start_date.required' => 'Harap pilih tanggal mulai.',
                'start_date.date' => 'Format tanggal mulai tidak valid.',

                'end_date.required' => 'Harap pilih tanggal selesai.',
                'end_date.date' => 'Format tanggal selesai tidak valid.',

                'is_review.required' => 'Pengaturan review wajib ditentukan.',
                'is_review.boolean' => 'Pengaturan review tidak valid.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $period = DB::transaction(function () use ($request, $periodId) {
                $period = TkaTryoutPeriod::query()->lockForUpdate()->find($periodId);

                if (!$period) {
                    throw new \RuntimeException(
                        'Periode Tryout TKA tidak ditemukan.'
                    );
                }

                $period->update([
                    'tahun_ajaran' => $request->tahun_ajaran,
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                    'is_review' => $request->boolean('is_review'),
                ]);

                return $period->fresh();
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Periode Tryout TKA berhasil diperbarui.',
                'data' => [
                    'id' => $period->id,
                    'period_number' => $period->period_number,
                    'tahun_ajaran' => $period->tahun_ajaran,
                    'start_date' => $period->start_date,
                    'end_date' => $period->end_date,
                    'is_review' => $period->is_review,
                ],
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'status' => 'error',
                'errors' => [
                    'period_id' => [
                        $e->getMessage(),
                    ],
                ],
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat memperbarui periode Tryout TKA.',
            ], 500);
        }
    }
}