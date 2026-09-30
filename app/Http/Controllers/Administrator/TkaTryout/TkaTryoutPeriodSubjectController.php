<?php

namespace App\Http\Controllers\Administrator\TkaTryout;

use App\Http\Controllers\Controller;
use App\Models\Mapel;
use App\Models\TkaTryoutPeriod;
use App\Models\TkaTryoutPeriodSchOverride;
use App\Models\TkaTryoutSubject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TkaTryoutPeriodSubjectController extends Controller
{
    public function index($role, $periodId)
    {
        $period = TkaTryoutPeriod::findOrFail($periodId);
        $periodDateList = \Carbon\CarbonPeriod::create($period->start_date, $period->end_date);

        return view('features.lms.administrator.tka-tryout.tka-tryout-period-subject.tka-tryout-period-subject-form', [
            'role' => $role,
            'periodId' => $period->id,
            'periodOverrideId' => null,
            'periodDateList' => $periodDateList,
            'periodOverrideDateList' => [],
            'isOverride' => false,
        ]);
    }

    public function indexOverride($role, $periodOverrideId)
    {
        $periodOverride = TkaTryoutPeriodSchOverride::findOrFail($periodOverrideId);
        $periodOverrideDateList = \Carbon\CarbonPeriod::create($periodOverride->start_date, $periodOverride->end_date);

        return view('features.lms.administrator.tka-tryout.tka-tryout-period-subject.tka-tryout-period-subject-form', [
            'role' => $role,
            'periodId' => $periodOverride->tka_tryout_period_id,
            'periodOverrideId' => $periodOverride->id,
            'periodDateList' => [],
            'periodOverrideDateList' => $periodOverrideDateList,
            'isOverride' => true,
        ]);
    }

    public function paginateTkaTryoutPeriodSubject($role, $periodId)
    {
        $period = TkaTryoutPeriod::find($periodId);

        $subjectList = TkaTryoutSubject::with(['Mapel', 'Mapel.Kelas'])->where('tka_tryout_period_id', $periodId)->whereNull('tka_tryout_period_sch_override_id')
        ->orderBy('subject_date')->orderBy('subject_id')->get()->map(function ($subject) {
            return [
                'id' => $subject->id,
                'user_id' => $subject->user_id,
                'tka_tryout_period_id' => $subject->tka_tryout_period_id,
                'tka_tryout_period_sch_override_id' => $subject->tka_tryout_period_sch_override_id,
                'subject_date' => $subject->subject_date ? date('Y-m-d', strtotime($subject->subject_date)) : null,
                'subject_id' => $subject->subject_id,
                'total_question' => $subject->total_question,
                'duration' => $subject->duration,
                'is_active' => $subject->is_active,
                'mapel' => $subject->Mapel,
            ];
        });

        return response()->json([
            'data' => $subjectList,
            'period' => [
                'id' => $period?->id,
                'period_number' => $period?->period_number,
                'tahun_ajaran' => $period?->tahun_ajaran,
                'start_date' => $period?->start_date ? date('Y-m-d', strtotime($period->start_date)) : null,
                'end_date' => $period?->end_date ? date('Y-m-d', strtotime($period->end_date)) : null,
            ],
        ]);
    }

    public function paginateTkaTryoutPeriodOverrideSubject($role, $periodOverrideId)
    {
        $periodOverride = TkaTryoutPeriodSchOverride::with('TkaTryoutPeriod')
            ->find($periodOverrideId);

        $subjectList = TkaTryoutSubject::with(['Mapel', 'Mapel.Kelas'])
            ->where('tka_tryout_period_sch_override_id', $periodOverrideId)
            ->orderBy('subject_date')
            ->orderBy('subject_id')
            ->get()
            ->map(function ($subject) {
                return [
                    'id' => $subject->id,
                    'user_id' => $subject->user_id,
                    'tka_tryout_period_id' => $subject->tka_tryout_period_id,
                    'tka_tryout_period_sch_override_id' => $subject->tka_tryout_period_sch_override_id,
                    'subject_date' => $subject->subject_date
                        ? date('Y-m-d', strtotime($subject->subject_date))
                        : null,
                    'subject_id' => $subject->subject_id,
                    'total_question' => $subject->total_question,
                    'duration' => $subject->duration,
                    'is_active' => $subject->is_active,
                    'mapel' => $subject->Mapel,
                ];
            });

        $period = $periodOverride?->tkaTryoutPeriod;

        return response()->json([
            'data' => $subjectList,
            'period' => [
                'id' => $period?->id,
                'period_number' => $period?->period_number,
                'tahun_ajaran' => $period?->tahun_ajaran,

                // PENTING: gunakan tanggal override
                'start_date' => $periodOverride?->start_date
                    ? date('Y-m-d', strtotime($periodOverride->start_date))
                    : null,

                'end_date' => $periodOverride?->end_date
                    ? date('Y-m-d', strtotime($periodOverride->end_date))
                    : null,
            ],
        ]);
    }

    public function tkaTryoutPeriodSubjectForm(Request $request, $role, $periodId)
    {
        $overrideId = $request->get('override_id');

        $classList = [
            'Kelas 6',
            'Kelas 9',
            'Kelas 12',
        ];

        if ($overrideId) {
            $periodOverride = TkaTryoutPeriodSchOverride::with(['TkaTryoutPeriod', 'SchoolPartner'])->find($overrideId);

            if (!$periodOverride) {
                return response()->json([
                    'message' => 'Periode khusus sekolah tidak ditemukan.',
                ], 404);
            }

            $basePeriod = $periodOverride->TkaTryoutPeriod;
            $school = $periodOverride->SchoolPartner;

            $classList = match ($school?->jenjang_sekolah) {
                'SD', 'MI' => ['Kelas 6'],
                'SMP', 'MTs' => ['Kelas 9'],
                'SMA', 'SMK', 'MA', 'MAK' => ['Kelas 12'],
                default => [],
            };

            $period = [
                'id' => $basePeriod->id,
                'period_number' => $basePeriod->period_number,
                'tahun_ajaran' => $basePeriod->tahun_ajaran,
                'start_date' => $periodOverride->start_date,
                'end_date' => $periodOverride->end_date,
                'is_override' => true,
                'school' => $school ? [
                    'id' => $school->id,
                    'nama_sekolah' => $school->nama_sekolah,
                    'npsn' => $school->npsn,
                    'jenjang_sekolah' => $school->jenjang_sekolah,
                    'logo' => $school->logo,
                ] : null,
            ];

            $dateList = \Carbon\CarbonPeriod::create(
                $periodOverride->start_date,
                $periodOverride->end_date
            );
        } else {
            $period = TkaTryoutPeriod::find($periodId);

            if (!$period) {
                return response()->json([
                    'message' => 'Periode Tryout TKA tidak ditemukan.',
                ], 404);
            }

            $period->is_override = false;
            $period->school = null;

            $dateList = \Carbon\CarbonPeriod::create(
                $period->start_date,
                $period->end_date
            );
        }

        $subjectList = Mapel::query()->whereHas('Kelas', function ($query) use ($classList) {
            $query->whereIn('kelas', $classList);
        })->whereHas('LmsQuestionBank', function ($query) {
                $query->where('question_category', 'TKA');
        })->with('Kelas')->join('kelas', 'mapels.kelas_id', '=', 'kelas.id')
        ->orderByRaw("CASE kelas.kelas WHEN 'Kelas 6' THEN 1 WHEN 'Kelas 9' THEN 2 WHEN 'Kelas 12' THEN 3 ELSE 4 END")->orderBy('mapels.mata_pelajaran')->select('mapels.*')->get();

        return response()->json([
            'period' => $period,
            'override' => $overrideId ? $periodOverride : null,

            'date_list' => collect($dateList)->map(function ($date) {
                return $date->format('Y-m-d');
            })->values(),

            'data' => $subjectList,
        ]);
    }

    public function tkaTryoutPeriodSubjectSubmitForm(Request $request, $role, $periodId)
    {
        $validator = Validator::make($request->all(), [
            'override_id' => ['nullable', 'integer', 'exists:tka_tryout_period_sch_overrides,id'],
            'subject_date' => ['required', 'date'],
            'subject_ids' => ['required', 'array', 'min:1'],
            'subject_ids.*' => ['required', 'integer', 'exists:mapels,id'],
            'total_question' => ['required', 'array'],
            'total_question.*' => ['required', 'integer', 'min:1'],
            'duration' => ['required', 'array'],
            'duration.*' => ['required', 'integer', 'min:1'],
        ], [
            'override_id.integer' => 'Periode khusus sekolah tidak valid.',
            'override_id.exists' => 'Periode khusus sekolah tidak ditemukan.',

            'subject_date.required' => 'Tanggal pelaksanaan wajib dipilih.',
            'subject_date.date' => 'Tanggal pelaksanaan tidak valid.',

            'subject_ids.required' => 'Minimal pilih satu mata pelajaran.',
            'subject_ids.array' => 'Data mata pelajaran tidak valid.',
            'subject_ids.min' => 'Minimal pilih satu mata pelajaran.',
            'subject_ids.*.required' => 'Mata pelajaran tidak valid.',
            'subject_ids.*.integer' => 'Mata pelajaran tidak valid.',
            'subject_ids.*.exists' => 'Mata pelajaran tidak ditemukan.',

            'total_question.required' => 'Jumlah soal wajib diisi untuk setiap mata pelajaran.',
            'total_question.array' => 'Data jumlah soal tidak valid.',
            'total_question.*.required' => 'Jumlah soal wajib diisi.',
            'total_question.*.integer' => 'Jumlah soal harus berupa angka.',
            'total_question.*.min' => 'Jumlah soal minimal 1.',

            'duration.required' => 'Durasi wajib diisi untuk setiap mata pelajaran.',
            'duration.array' => 'Data durasi tidak valid.',
            'duration.*.required' => 'Durasi wajib diisi.',
            'duration.*.integer' => 'Durasi harus berupa angka.',
            'duration.*.min' => 'Durasi minimal 1 menit.',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();

            if (isset($errors['subject_ids.*'])) {
                $errors['subject_ids'] = [$errors['subject_ids.*'][0]];
                unset($errors['subject_ids.*']);
            }

            $hasTotalQuestionError = false;
            $hasDurationError = false;

            foreach ($errors as $key => $messages) {
                if (str_starts_with($key, 'total_question.')) {
                    $hasTotalQuestionError = true;
                    unset($errors[$key]);
                }

                if (str_starts_with($key, 'duration.')) {
                    $hasDurationError = true;
                    unset($errors[$key]);
                }
            }

            if ($hasTotalQuestionError || $hasDurationError) {
                $errors['total_question_and_duration'] = [
                    'Jumlah soal dan durasi harus diisi pada setiap mata pelajaran.'
                ];
            }

            return response()->json([
                'message' => 'Terdapat data yang belum valid.',
                'errors' => $errors,
            ], 422);
        }

        $period = TkaTryoutPeriod::find($periodId);

        if (!$period) {
            return response()->json([
                'message' => 'Periode Tryout TKA tidak ditemukan.',
            ], 404);
        }

        $overrideId = $request->get('override_id');
        $subjectDate = $request->subject_date;
        $subjectIds = array_unique($request->subject_ids);
        $totalQuestions = $request->total_question;
        $durations = $request->duration;

        if ($overrideId) {
            $periodOverride = TkaTryoutPeriodSchOverride::where('id', $overrideId)
                ->where('tka_tryout_period_id', $periodId)
                ->first();

            if (!$periodOverride) {
                return response()->json([
                    'message' => 'Periode khusus sekolah tidak sesuai dengan periode Tryout TKA.',
                ], 404);
            }
        }

        foreach ($subjectIds as $subjectId) {
            if (!isset($totalQuestions[$subjectId]) || !isset($durations[$subjectId])) {
                return response()->json([
                    'message' => 'Terdapat konfigurasi mata pelajaran yang belum lengkap.',
                    'errors' => [
                        'subject_ids' => [
                            'Jumlah soal dan durasi wajib diisi untuk setiap mata pelajaran.',
                        ],
                    ],
                ], 422);
            }
        }

        $existingSubjectsQuery = TkaTryoutSubject::whereDate('subject_date', $subjectDate)
            ->whereIn('subject_id', $subjectIds)
            ->with('Mapel');

        if ($overrideId) {
            $existingSubjectsQuery->where('tka_tryout_period_sch_override_id', $overrideId);
        } else {
            $existingSubjectsQuery->where('tka_tryout_period_id', $periodId)
                ->whereNull('tka_tryout_period_sch_override_id');
        }

        $existingSubjects = $existingSubjectsQuery->get();

        if ($existingSubjects->isNotEmpty()) {
            $existingSubjectNames = $existingSubjects->pluck('Mapel.mata_pelajaran')->filter()->implode(', ');

            return response()->json([
                'message' => 'Mata pelajaran ' . $existingSubjectNames . ' sudah terdaftar pada tanggal tersebut.',
                'errors' => [
                    'subject_ids' => [
                        'Mata pelajaran ' . $existingSubjectNames . ' sudah terdaftar pada tanggal tersebut.',
                    ],
                ],
            ], 422);
        }

        DB::transaction(function () use ($overrideId, $periodId, $subjectDate, $subjectIds, $totalQuestions, $durations) {
            $data = [];

            foreach ($subjectIds as $subjectId) {
                $data[] = [
                    'user_id' => Auth::id(),
                    'tka_tryout_period_id' => $overrideId ? null : $periodId,
                    'tka_tryout_period_sch_override_id' => $overrideId ?: null,
                    'subject_date' => $subjectDate,
                    'subject_id' => $subjectId,
                    'total_question' => $totalQuestions[$subjectId],
                    'duration' => $durations[$subjectId],
                    'is_active' => true,
                ];
            }

            TkaTryoutSubject::insert($data);
        });

        return response()->json([
            'message' => count($subjectIds) . ' mata pelajaran berhasil ditambahkan pada tanggal ' . \Carbon\Carbon::parse($subjectDate)->locale('id')->translatedFormat('d F Y') . '.',
        ]);
    }

    public function tkaTryoutPeriodSubjectEdit(Request $request, $role, $periodId, $tkaTryoutSubjectId)
    {
        $isOverride = (int) $request->input('is_override') === 1;
        $periodOverrideId = $request->input('period_override_id');

        $validated = $request->validate([
            'subject_date' => ['required', 'date'],
            'total_question' => ['required', 'integer', 'min:1'],
            'duration' => ['required', 'integer', 'min:1'],
        ], [
            'subject_date.required' => 'Tanggal pelaksanaan harus diisi.',
            'subject_date.date' => 'Tanggal pelaksanaan tidak valid.',
            'total_question.required' => 'Jumlah soal harus diisi.',
            'total_question.integer' => 'Jumlah soal harus berupa angka.',
            'total_question.min' => 'Jumlah soal minimal 1.',
            'duration.required' => 'Durasi harus diisi.',
            'duration.integer' => 'Durasi harus berupa angka.',
            'duration.min' => 'Durasi minimal 1 menit.',
        ]);

        $period = TkaTryoutPeriod::find($periodId);

        if (!$period) {
            return response()->json([
                'message' => 'Periode tryout tidak ditemukan.',
            ], 404);
        }

        $subjectDate = date('Y-m-d', strtotime($validated['subject_date']));

        if ($isOverride) {
            if (!$periodOverrideId) {
                return response()->json([
                    'message' => 'Periode override tidak ditemukan.',
                ], 422);
            }

            $periodOverride = TkaTryoutPeriodSchOverride::where('id', $periodOverrideId)->where('tka_tryout_period_id', $periodId)->first();

            if (!$periodOverride) {
                return response()->json([
                    'message' => 'Periode override tidak ditemukan.',
                ], 404);
            }

            $periodStartDate = $periodOverride->start_date ? date('Y-m-d', strtotime($periodOverride->start_date)) : null;
            $periodEndDate = $periodOverride->end_date ? date('Y-m-d', strtotime($periodOverride->end_date)) : null;
        } else {
            $periodStartDate = $period->start_date ? date('Y-m-d', strtotime($period->start_date)) : null;
            $periodEndDate = $period->end_date ? date('Y-m-d', strtotime($period->end_date)) : null;
        }

        if ($periodStartDate && $subjectDate < $periodStartDate) {
            return response()->json([
                'errors' => [
                    'subject_date' => [
                        'Tanggal pelaksanaan tidak boleh sebelum tanggal mulai periode.'
                    ],
                ],
            ], 422);
        }

        if ($periodEndDate && $subjectDate > $periodEndDate) {
            return response()->json([
                'errors' => [
                    'subject_date' => [
                        'Tanggal pelaksanaan tidak boleh setelah tanggal berakhir periode.'
                    ],
                ],
            ], 422);
        }

        $subjectQuery = TkaTryoutSubject::where('id', $tkaTryoutSubjectId);

        if ($isOverride) {
            if (!$periodOverrideId) {
                return response()->json([
                    'message' => 'Periode override tidak ditemukan.',
                ], 422);
            }

            $subjectQuery->where('tka_tryout_period_sch_override_id', $periodOverrideId);
        } else {
            $subjectQuery->where('tka_tryout_period_id', $periodId)->whereNull('tka_tryout_period_sch_override_id');
        }

        $subject = $subjectQuery->first();

        if (!$subject) {
            return response()->json([
                'message' => 'Mata pelajaran tryout tidak ditemukan.',
            ], 404);
        }

        $duplicateQuery = TkaTryoutSubject::where('id', '!=', $subject->id)->where('subject_date', $subjectDate)->where('subject_id', $subject->subject_id);

        if ($isOverride) {
            $duplicateQuery->where('tka_tryout_period_sch_override_id', $periodOverrideId);
        } else {
            $duplicateQuery->where('tka_tryout_period_id', $periodId)->whereNull('tka_tryout_period_sch_override_id');
        }

        if ($duplicateQuery->exists()) {
            return response()->json([
                'errors' => [
                    'subject_date' => [
                        'Mata pelajaran ini sudah memiliki jadwal pada tanggal tersebut.'
                    ],
                ],
            ], 422);
        }

        $subject->update([
            'subject_date' => $subjectDate,
            'total_question' => $validated['total_question'],
            'duration' => $validated['duration'],
        ]);

        return response()->json([
            'message' => 'Mata pelajaran berhasil diperbarui.',
            'data' => [
                'id' => $subject->id,
                'subject_date' => $subject->subject_date ? date('Y-m-d', strtotime($subject->subject_date)) : null,
                'total_question' => $subject->total_question,
                'duration' => $subject->duration,
                'is_override' => $isOverride,
            ],
        ]);
    }

    public function tkaTryoutPeriodSubjectOverrideActivate(Request $request, $role, $periodOverrideId, $tkaTryoutSubjectId) 
    {
        $tkaTryoutSubject = TkaTryoutSubject::find($tkaTryoutSubjectId);

        if (!$tkaTryoutSubject) {
            return response()->json([
                'success' => false,
                'message' => 'Mata pelajaran tidak ditemukan.',
            ], 404);
        }

        $tkaTryoutSubject->update([
            'is_active' => $request->is_active,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status mata pelajaran berhasil diubah.',
        ]);
    }
}