<?php

namespace App\Http\Controllers\Administrator\TkaTryout;

use App\Http\Controllers\Controller;
use App\Models\SchoolPartner;
use App\Models\TkaTryoutPeriod;
use App\Models\TkaTryoutPeriodSchOverride;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TkaTryoutPeriodSchoolOverrideController extends Controller
{
    public function index($role, $periodId)
    {
        $schools = SchoolPartner::orderBy('nama_sekolah')->get();

        return view('features.lms.administrator.tka-tryout.tka-tryout-period.tka-tryout-period-school-override-form', compact('role', 'periodId', 'schools'));
    }

    public function tkaTryoutPeriodSchoolOverrideCreate(Request $request, $role, $periodId)
    {
        $validator = Validator::make($request->all(),
            [
                'school_partner_id' => [
                    'required',
                    'integer',
                    'exists:school_partners,id',
                    Rule::unique('tka_tryout_period_sch_overrides', 'school_partner_id')->where(function ($query) use ($periodId) {
                        $query->where('tka_tryout_period_id', $periodId);
                    }),
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
                'school_partner_id.required' => 'Harap pilih sekolah.',
                'school_partner_id.integer' => 'Sekolah yang dipilih tidak valid.',
                'school_partner_id.exists' => 'Sekolah yang dipilih tidak ditemukan.',
                'school_partner_id.unique' => 'Sekolah tersebut sudah memiliki periode khusus pada periode Tryout TKA ini.',
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
            $override = DB::transaction(function () use ($request, $periodId) {
                $period = TkaTryoutPeriod::query()->lockForUpdate()->find($periodId);

                if (!$period) {
                    throw new \RuntimeException(
                        'Periode Tryout TKA tidak ditemukan.'
                    );
                }

                return TkaTryoutPeriodSchOverride::create([
                    'user_id' => Auth::id(),
                    'tka_tryout_period_id' => $period->id,
                    'school_partner_id' => $request->school_partner_id,
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                    'is_review' => $request->boolean('is_review'),
                ]);
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Periode khusus sekolah berhasil dibuat.',
                'data' => [
                    'id' => $override->id,
                    'tka_tryout_period_id' => $override->tka_tryout_period_id,
                    'school_partner_id' => $override->school_partner_id,
                    'start_date' => $override->start_date,
                    'end_date' => $override->end_date,
                ],
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'status' => 'error',
                'errors' => [
                    'school_partner_id' => [
                        $e->getMessage(),
                    ],
                ],
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat membuat periode khusus sekolah.',
            ], 500);
        }
    }

    public function tkaTryoutPeriodSchoolOverrideUpdate(Request $request, $role, $periodId, $overrideId)
    {
        $validator = Validator::make(
            $request->all(),
            [
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
            $override = DB::transaction(function () use (
                $request,
                $periodId,
                $overrideId
            ) {
                $period = TkaTryoutPeriod::query()
                    ->lockForUpdate()
                    ->find($periodId);

                if (!$period) {
                    throw new \RuntimeException(
                        'Periode Tryout TKA tidak ditemukan.'
                    );
                }

                $override = TkaTryoutPeriodSchOverride::query()
                    ->where('tka_tryout_period_id', $period->id)
                    ->lockForUpdate()
                    ->find($overrideId);

                if (!$override) {
                    throw new \RuntimeException(
                        'Periode khusus sekolah tidak ditemukan.'
                    );
                }

                $override->update([
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                    'is_review' => $request->boolean('is_review'),
                ]);

                return $override->fresh();
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Periode khusus sekolah berhasil diperbarui.',
                'data' => [
                    'id' => $override->id,
                    'tka_tryout_period_id' => $override->tka_tryout_period_id,
                    'school_partner_id' => $override->school_partner_id,
                    'start_date' => $override->start_date,
                    'end_date' => $override->end_date,
                    'is_review' => $override->is_review,
                ],
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'status' => 'error',
                'errors' => [
                    'override_id' => [
                        $e->getMessage(),
                    ],
                ],
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat memperbarui periode khusus sekolah.',
            ], 500);
        }
    }
}