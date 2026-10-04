<?php

namespace App\Http\Controllers\Administrator\TkaTryout;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\SchoolPartner;
use App\Models\StudentSchoolClass;
use App\Models\TkaTryoutPeriod;
use App\Models\TkaTryoutPeriodSchOverride;
use App\Models\TkaTryoutSession;
use App\Models\TkaTryoutSessionStudent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TkaTryoutPeriodSessionController extends Controller
{
    public function index($role, $periodId)
    {
        $getPeriod = TkaTryoutPeriod::find($periodId);

        return view('features.lms.administrator.tka-tryout.tka-tryout-period-session.tka-tryout-period-session-form', compact('role', 'periodId', 'getPeriod'));
    }

    public function indexOverride($role, $periodOverrideId)
    {
        $getPeriodOverride = TkaTryoutPeriodSchOverride::with(['TkaTryoutPeriod', 'SchoolPartner'])->find($periodOverrideId);

        return view('features.lms.administrator.tka-tryout.tka-tryout-period-session.tka-tryout-period-session-form', compact('role', 'periodOverrideId', 'getPeriodOverride'));
    }

    public function paginateTkaTryoutPeriodSession($role, $periodId)
    {
        $query = TkaTryoutSession::with(['TkaTryoutPeriod', 'TkaTryoutPeriodSchOverride'])->where('tka_tryout_period_id', $periodId);

        $sessionCount = $query->count();

        $getPeriodSession = $query->orderBy('session_date')->orderBy('session_number')->get();

        $groupedSessions = $getPeriodSession->groupBy(function ($session) {
            return $session->session_date?->format('Y-m-d') ?? 'tidak_diketahui';
        })->map(function ($sessions, $date) {
            return [
                'session_date' => $date,
                'sessions' => $sessions->values()
            ];
        })->values();

        return response()->json([
            'data' => $groupedSessions,
            'total_session' => $sessionCount
        ]);
    }

    public function paginateTkaTryoutPeriodSessionOverride($role, $periodOverrideId)
    {
        $query = TkaTryoutSession::with(['TkaTryoutPeriod', 'TkaTryoutPeriodSchOverride', 'TkaTryoutPeriodSchOverride.TkaTryoutPeriod', 
        'TkaTryoutPeriodSchOverride.SchoolPartner'])->where('tka_tryout_period_sch_override_id', $periodOverrideId);

        $sessionCount = $query->count();

        $getPeriodSession = $query->orderBy('session_date')->orderBy('session_number')->get();

        $groupedSessions = $getPeriodSession->groupBy(function ($session) {
            return $session->session_date?->format('Y-m-d') ?? 'tidak_diketahui';
        })->map(function ($sessions, $date) {
            return [
                'session_date' => $date,
                'sessions' => $sessions->values()
            ];
        })->values();

        return response()->json([
            'data' => $groupedSessions,
            'total_session' => $sessionCount
        ]);
    }

    private function createTkaTryoutSession(Request $request, $periodId, $periodOverrideId) 
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(),
            [
                'session_date' => [
                    'required',
                    'date',
                ],
                'start_time' => [
                    'required',
                    'date_format:H:i',
                ],
                'end_time' => [
                    'required',
                    'date_format:H:i',
                ],
            ],
            [
                'session_date.required' => 'Tanggal sesi wajib dipilih.',
                'session_date.date' => 'Format tanggal sesi tidak valid.',
                'start_time.required' => 'Jam mulai wajib dipilih.',
                'start_time.date_format' => 'Format jam mulai tidak valid.',
                'end_time.required' => 'Jam selesai wajib dipilih.',
                'end_time.date_format' => 'Format jam selesai tidak valid.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Terdapat kesalahan pada data sesi.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $session = DB::transaction(function () use ($user, $request, $periodId, $periodOverrideId) {
            $query = TkaTryoutSession::query();

            if ($periodId && !$periodOverrideId) {
                $query->where('tka_tryout_period_id', $periodId);
            } else {
                $query->where('tka_tryout_period_sch_override_id', $periodOverrideId);
            }

            $sessionNumber = $query->whereDate('session_date', $request->session_date)->lockForUpdate()->max('session_number');
            $sessionNumber = ($sessionNumber ?? 0) + 1;

            $data = [
                'user_id' => $user->id,
                'session_date' => $request->session_date,
                'session_number' => $sessionNumber,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
            ];

            if ($periodOverrideId) {
                $data['tka_tryout_period_id'] = null;
                $data['tka_tryout_period_sch_override_id'] = $periodOverrideId;
            } else {
                $data['tka_tryout_period_id'] = $periodId;
                $data['tka_tryout_period_sch_override_id'] = null;
            }

            return TkaTryoutSession::create($data);
        });

        return response()->json([
            'success' => true,
            'message' => 'Sesi Tryout TKA berhasil ditambahkan.',
            'data' => $session,
        ], 201);
    }

    public function tkaTryoutPeriodSessionCreate(Request $request, $role, $periodId) 
    {
        $period = TkaTryoutPeriod::find($periodId);

        if (!$period) {
            return response()->json([
                'success' => false,
                'message' => 'Periode Tryout TKA tidak ditemukan.',
            ], 404);
        }

        return $this->createTkaTryoutSession($request, $period->id, null);
    }

    public function tkaTryoutPeriodSessionOverrideCreate(Request $request, $role, $periodOverrideId) {
        $periodOverride = TkaTryoutPeriodSchOverride::find($periodOverrideId);

        if (!$periodOverride) {
            return response()->json([
                'success' => false,
                'message' => 'Override periode Tryout TKA tidak ditemukan.',
            ], 404);
        }

        return $this->createTkaTryoutSession($request, null, $periodOverride->id);
    }

    private function updateTkaTryoutSession(Request $request, $periodId, $periodOverrideId, $sessionId)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'session_date' => [
                    'required',
                    'date',
                ],
                'start_time' => [
                    'required',
                    'date_format:H:i',
                ],
                'end_time' => [
                    'required',
                    'date_format:H:i',
                ],
            ],
            [
                'session_date.required' => 'Tanggal sesi wajib dipilih.',
                'session_date.date' => 'Format tanggal sesi tidak valid.',

                'start_time.required' => 'Jam mulai wajib dipilih.',
                'start_time.date_format' => 'Format jam mulai tidak valid.',

                'end_time.required' => 'Jam selesai wajib dipilih.',
                'end_time.date_format' => 'Format jam selesai tidak valid.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Terdapat kesalahan pada data sesi.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $result = DB::transaction(function () use ($request, $periodId, $periodOverrideId, $sessionId) {

            $query = TkaTryoutSession::query()->where('id', $sessionId);

            if ($periodOverrideId) {
                $query->where('tka_tryout_period_sch_override_id', $periodOverrideId);
            } else {
                $query->where('tka_tryout_period_id', $periodId);
            }

            $session = $query->lockForUpdate()->first();

            if (!$session) {
                return [
                    'status' => 'not_found',
                    'session' => null,
                ];
            }

            $existsQuery = TkaTryoutSession::query()->where('id', '!=', $sessionId)->where('session_date', $request->session_date)
            ->where('session_number', $session->session_number);

            if ($periodOverrideId) {
                $existsQuery->where('tka_tryout_period_sch_override_id', $periodOverrideId);
            } else {
                $existsQuery->where('tka_tryout_period_id', $periodId);
            }

            if ($existsQuery->exists()) {
                return [
                    'status' => 'duplicate',
                    'session' => null,
                ];
            }

            $session->update([
                'session_date' => $request->session_date,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
            ]);

            return [
                'status' => 'success',
                'session' => $session->fresh(),
            ];
        });

        if ($result['status'] === 'not_found') {
            return response()->json([
                'success' => false,
                'message' => 'Sesi Tryout TKA tidak ditemukan.',
                'errors' => [
                    'session_date' => [
                        'Sesi Tryout TKA tidak ditemukan.'
                    ]
                ],
            ], 404);
        }

        if ($result['status'] === 'duplicate') {
            return response()->json([
                'success' => false,
                'message' => 'Tanggal sesi tersebut sudah memiliki sesi dengan nomor yang sama.',
                'errors' => [
                    'session_date' => [
                        'Tanggal tersebut sudah digunakan oleh sesi dengan nomor yang sama.'
                    ]
                ],
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Sesi Tryout TKA berhasil diperbarui.',
            'data' => $result['session'],
        ]);
    }

    public function tkaTryoutPeriodSessionUpdate(Request $request, $role, $periodId, $sessionId) 
    {
        $period = TkaTryoutPeriod::find($periodId);

        if (!$period) {
            return response()->json([
                'success' => false,
                'message' => 'Periode Tryout TKA tidak ditemukan.',
            ], 404);
        }

        return $this->updateTkaTryoutSession($request, $period->id, null, $sessionId);
    }

    public function tkaTryoutPeriodSessionOverrideUpdate(Request $request, $role, $periodOverrideId, $sessionId) 
    {
        $periodOverride = TkaTryoutPeriodSchOverride::find($periodOverrideId);

        if (!$periodOverride) {
            return response()->json([
                'success' => false,
                'message' => 'Periode khusus sekolah tidak ditemukan.',
            ], 404);
        }

        return $this->updateTkaTryoutSession($request, null, $periodOverride->id, $sessionId);
    }

    public function manageStudent($role, $periodId, $sessionId)
    {
        $session = TkaTryoutSession::find($sessionId);

        $schools = SchoolPartner::orderBy('nama_sekolah')->get();

        $schoolId = null;

        if ($session->tka_tryout_period_sch_override_id) {
            $schoolId = SchoolPartner::find($session->TkaTryoutPeriodSchOverride->school_partner_id)->id;
        }

        return view('features.lms.administrator.tka-tryout.tka-tryout-period-session.tka-tryout-period-session-manage-student', compact('role', 'periodId', 'sessionId', 'session', 
        'schools', 'schoolId'));
    }
    
    public function manageStudentForm($role, $periodId, $sessionId, $schoolId)
    {
        $search = request('search');

        $selectedStudentIds = TkaTryoutSessionStudent::where('tka_tryout_session_id', $sessionId)->where('status', true)->pluck('student_id')
        ->map(fn ($id) => (string) $id)->toArray();

        $classes = Kelas::whereIn('kelas', ['Kelas 6', 'Kelas 9', 'Kelas 12'])->whereHas('SchoolClass', function ($query) use ($schoolId) {
            $query->where('school_partner_id', $schoolId);
        })->with([
            'SchoolClass' => function ($query) use ($schoolId, $search) {
                $query->where('school_partner_id', $schoolId)->with(['StudentSchoolClass' => function ($query) use ($search) {
                    $query->when($search, function ($query) use ($search) {
                        $query->whereHas('UserAccount.StudentProfile', function ($query) use ($search) {
                            $query->where('nama_lengkap', 'like', "%{$search}%");
                        });
                    })->with(['SchoolClass', 'UserAccount.StudentProfile']);
                },
            ]);
        },
        ])->get(['id', 'kelas'])->map(function ($kelas) use ($selectedStudentIds) {
            $students = $kelas->SchoolClass->flatMap(function ($schoolClass) {
                return $schoolClass->StudentSchoolClass;
    
            })->values()->map(function ($student) use ($selectedStudentIds) {
                $student->is_selected = in_array((string) $student->student_id, $selectedStudentIds, true);
                return $student;
            });

            return [
                'id' => $kelas->id,
                'kelas' => $kelas->kelas,
                'students' => $students,
            ];
        })->filter(function ($kelas) {
            return $kelas['students']->isNotEmpty();
        })->values();

        return response()->json([
            'success' => true,
            'data' => $classes,
        ]);
    }

    public function manageStudentSubmitForm(Request $request, $role, $periodId, $sessionId)
    {
        $validator = Validator::make($request->all(), [
            'school_partner_id' => 'required',
            'kelas_id' => 'required',
            'student_ids' => 'nullable|array',
            'student_ids.*' => 'required|integer',
        ], [
            'school_partner_id.required' => 'Sekolah wajib dipilih.',
            'kelas_id.required' => 'Kelas wajib dipilih.',
            'student_ids.array' => 'Format siswa tidak valid.',
            'student_ids.*.required' => 'Siswa tidak valid.',
            'student_ids.*.integer' => 'ID siswa tidak valid.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // get session
        $session = TkaTryoutSession::query()->where('id', $sessionId)->where(function ($query) use ($periodId) {
            $query->where('tka_tryout_period_id', $periodId)->orWhereHas('TkaTryoutPeriodSchOverride', function ($query) use ($periodId) {
                $query->where('tka_tryout_period_sch_override_id', $periodId);
            });
        })->firstOrFail();

        // selected students
        $selectedStudentIds = collect(
            $request->input('student_ids', [])
        )->map(fn ($studentId) => (int) $studentId)->unique()->values();

        // validate students
        $validStudentIds = StudentSchoolClass::query()->whereHas('SchoolClass', function ($query) use ($request) {
            $query->where('school_partner_id', $request->school_partner_id)->where('kelas_id', $request->kelas_id);
        })->whereIn('student_id', $selectedStudentIds)->pluck('student_id')->map(fn ($id) => (int) $id)->unique()->values();

        if ($validStudentIds->count() !== $selectedStudentIds->count()) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'student_ids' => [
                        'Terdapat siswa yang tidak sesuai dengan sekolah atau kelas yang dipilih.',
                    ],
                ],
            ], 422);
        }

        // save students
        DB::transaction(function () use ($session, $selectedStudentIds) {

            $existingStudents = TkaTryoutSessionStudent::query()->where('tka_tryout_session_id', $session->id);

            // no selected student
            if ($selectedStudentIds->isEmpty()) {
                $existingStudents->update([
                    'status' => false,
                ]);

                return;
            }

            // deactivate removed students
            $existingStudents->whereNotIn('student_id', $selectedStudentIds)->update([
                'status' => false,
            ]);

            // activate / create selected students
            foreach ($selectedStudentIds as $studentId) {

                TkaTryoutSessionStudent::updateOrCreate(
                    [
                        'tka_tryout_session_id' => $session->id,
                        'student_id' => $studentId,
                    ],
                    [
                        'status' => true,
                    ]
                );
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Data peserta sesi berhasil disimpan.',
        ]);
    }
}