<?php

namespace App\Http\Controllers\Lms\QuestionBank;

use App\Events\ActivateQuestionBankPG;
use App\Events\BankSoalLmsEditPG;
use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Kurikulum;
use App\Models\LmsQuestionBank;
use App\Models\LmsQuestionOption;
use App\Models\SchoolPartner;
use App\Models\SchoolQuestionBank;
use App\Models\UserAccount;
use App\Services\LMS\BankSoalWordImportService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class QuestionBankController extends Controller
{
    // function question bank management view
    public function lmsQuestionBankManagementView($schoolName = null, $schoolId = null)
    {
        $getCurriculum = Kurikulum::all();

        return view('features.lms.administrator.question-bank-management.lms-question-bank-management', compact('schoolName', 'schoolId', 'getCurriculum'));
    }

    // function paginate bank soal
    public function paginateLmsQuestionBankManagement(Request $request, $schoolName = null, $schoolId = null)
    {
        $users = UserAccount::with(['StudentProfile', 'SchoolStaffProfile'])->where(function ($query) use ($schoolId) {
            $query->whereHas('StudentProfile', function ($q) use ($schoolId) {
                $q->where('school_partner_id', $schoolId);
            })->orWhereHas('SchoolStaffProfile', function ($q) use ($schoolId) {
                $q->where('school_partner_id', $schoolId);
            });
        })->get();

        // jika ada schoolId maka ambil content dari sekolah tersebut dan dari global
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
    
            $allowedKelas = $mappingClasses[$jenjang] ?? [];
    
            // ambil kelas sesuai dengan jenjang sekolahnya, lalu ambil id nya saja
            $kelasIds = Kelas::whereIn(DB::raw('LOWER(kelas)'), $allowedKelas)->pluck('id');
        }

        $getQuestions = LmsQuestionBank::with(['UserAccount', 'UserAccount.OfficeProfile', 'UserAccount.SchoolStaffProfile','Kurikulum', 'Kelas', 'Mapel', 'Bab', 'SubBab',
            'SchoolPartner',
            'SchoolQuestionBank' => function ($q) use ($schoolId) {

            if ($schoolId) {
                $q->where('school_partner_id', $schoolId);
            }
            
        }])->orderBy('created_at', 'desc');

        if ($schoolId) {
            $getQuestions->where(function ($q1) use ($schoolId, $kelasIds) {
                $q1->where('school_partner_id', $schoolId)
                ->orWhere(function ($q2) use ($kelasIds) {
                    $q2->whereNull('school_partner_id')->whereIn('kelas_id', $kelasIds);
                });
            });
        } else {
            $getQuestions->whereNull('school_partner_id');
        }

        $rows = $getQuestions->get()->groupBy(fn ($q) => $q->sub_bab_id.'-'.$q->tipe_soal.'-'.$q->school_partner_id)->values();

        // Pagination manual
        $page = $request->get('page', 1);
        $perPage = 20;

        $paged = $rows->slice(
            ($page - 1) * $perPage,
            $perPage
        )->values();

        $paginated = new LengthAwarePaginator(
            $paged,
            $rows->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $getSchool = SchoolPartner::with('UserAccount.SchoolStaffProfile')->where('id', $schoolId)->first();

        $countUsers = $users->count();

        return response()->json([
            'data' => $paginated->values(),
            'links' => (string) $paginated->links(),
            'current_page' => $paginated->currentPage(),
            'per_page' => $paginated->perPage(),
            'schoolIdentity' => $getSchool,
            'countUsers' => $countUsers,
            'source' => $source ?? null,
            'lmsReviewQuestion' => '/lms/question-bank-management/source/:source/review/question-type/:questionType/:subBabId',
            'lmsReviewQuestionBySchool' => '/lms/school-subscription/question-bank-management/source/:source/review/question-type/:questionType/:subBabId/:schoolName/:schoolId',
        ]);
    }

    // function bank soal store UH, ASTS, ASAS
    public function lmsQuestionBankManagementStore(Request $request)
    {
        return app(BankSoalWordImportService::class)->bankSoalImportService($request);
    }

    // function activate bank soal
    public function lmsActivateQuestionBank(Request $request, $subBabId, $source, $questionType, $schoolName = null, $schoolId = null) 
    {
        $isEnable = $request->action === 'enable';

        // Ambil semua soal target (TANPA gate global)
        $questions = LmsQuestionBank::where('sub_bab_id', $subBabId)->where('question_source', $source)
        ->where('tipe_soal', $questionType)->get();

        if ($schoolId) {

            // MODE SEKOLAH (OVERRIDE)
            foreach ($questions as $question) {
                SchoolQuestionBank::updateOrCreate(
                    [
                        'question_id' => $question->id,
                        'school_partner_id' => $schoolId,
                    ],
                    [
                        'is_active' => $isEnable,
                    ]
                );
            }
        } else {
            // MODE GLOBAL
            $status = $isEnable ? 'Publish' : 'Unpublish';

            $affected = LmsQuestionBank::where('sub_bab_id', $subBabId)->where('question_source', $source)
            ->where('tipe_soal', $questionType)->update([
                'status_bank_soal' => $status,
            ]);
        }

        broadcast(new ActivateQuestionBankPG($subBabId,$source,$request->action,$questions->count()))->toOthers();

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil mengubah status bank soal',
        ]);
    }


    // function bank soal detail view
    public function lmsQuestionBankManagementDetailView($source, $questionType, $subBabId, $schoolName = null, $schoolId = null)
    {
        return view('features.lms.administrator.question-bank-management.administrator-question-bank-management-detail', compact('source', 'questionType', 
        'subBabId', 'schoolName', 'schoolId'));
    }

    // function paginate bank soal detail
    public function paginateReviewQuestionBank($source, $questionType, $subBabId, $schoolName = null, $schoolId = null) 
    {
        $user = Auth::user();

        $questions = LmsQuestionBank::with('LmsQuestionOption')
            ->where('sub_bab_id', $subBabId)
            ->where('question_source', $source)
            ->where('tipe_soal', $questionType)
            ->get();

        $videoIds = $questions->map(function ($q) {
            if (preg_match(
                '/youtu\.be\/([a-zA-Z0-9_-]{11})|youtube\.com\/.*v=([a-zA-Z0-9_-]{11})/',
                $q->explanation,
                $matches
            )) {
                return $matches[1] ?? $matches[2];
            }
            return null;
        });

        $response = [
            'data' => $questions,
            'videoIds' => $videoIds,
        ];

        if ($user->role === 'Administrator' || $user->role === 'Admin Sekolah') {
            $response['lmsEditQuestion'] = '/lms/question-bank-management/source/:source/review/question-type/:questionType/:subBabId/:questionId/edit';
            $response['lmsEditQuestionBySchool'] = '/lms/school-subscription/question-bank-management/source/:source/review/question-type/:questionType/:subBabId/:questionId/:schoolName/:schoolId/edit';
        } else if ($user->role === 'Guru') {
            $response['lmsEditQuestion'] = '/lms/:role/:schoolName/:schoolId/teacher-question-bank-management/source/:source/review/question-type/:questionType/:subBabId/:questionId/edit';
        }

        return response()->json($response);
    }

    // function edit question view
    public function lmsQuestionBankManagementEditView($source, $questionType, $subBabId, $questionId, $schoolName = null, $schoolId = null)
    {
        // Mengambil data soal berdasarkan ID
        $editQuestion = LmsQuestionBank::find($questionId);

        if (!$editQuestion) {
            if ($schoolId) {
                return redirect()->route('lms.questionBankManagementDetail.view.schoolPartner', [$source, $questionType, $subBabId, $schoolName, $schoolId]);
            } else {
                return redirect()->route('lms.questionBankManagementDetail.view.noSchoolPartner', [$source, $questionType, $subBabId]);
            }
        }

        // Mengambil data soal yang punya pertanyaan (questions) yang sama, lalu dikelompokkan berdasarkan isi questions-nya
        $dataSoal = LmsQuestionBank::where('questions', $editQuestion->questions)->get()->groupBy('questions');

        // Simpan hasil pengelompokan ke variabel baru
        $groupedSoal = $dataSoal;

        return view('features.lms.administrator.question-bank-management.administrator-question-bank-management-edit', compact('source', 'subBabId', 'questionId', 
        'schoolName', 'schoolId', 'questionType'));
    }

    // form edit question
    public function formEditQuestion($source, $questionType, $subBabId, $questionId, $schoolName = null, $schoolId = null)
    {
        $editQuestion = LmsQuestionBank::with('LmsQuestionOption')->findOrFail($questionId);

        if (!$editQuestion) {
            if ($schoolId) {
                return redirect()->route('lms.questionBankManagementDetail.view.schoolPartner', [$source, $questionType, $subBabId, $schoolName, $schoolId]);
            } else {
                return redirect()->route('lms.questionBankManagementDetail.view.noSchoolPartner', [$source, $questionType, $subBabId]);
            }
        }

        $options = $editQuestion->LmsQuestionOption;

        // Buat mapping LEFT -> RIGHT dari extra_data['pair_with']
        $matching = [];
        foreach ($options as $opt) {
            if (($opt->extra_data['side'] ?? null) === 'left') {
                $matching[$opt->options_key] = $opt->extra_data['pair_with'] ?? null;
            }
        }

        // Mengambil data soal yang punya pertanyaan (questions) yang sama, lalu dikelompokkan berdasarkan isi questions-nya
        $dataSoal = LmsQuestionBank::where('questions', $editQuestion->questions)->get()->groupBy('questions');

        // Simpan hasil pengelompokan ke variabel baru
        $groupedSoal = $dataSoal;

        // cek apakah soal suda dipake untuk assessment atau belum
        $isUsed = $editQuestion->SchoolAssessmentQuestion()->whereHas('StudentAssessmentAnswer')->exists();

        return response()->json([
            'status' => 'success',
            'data' => $groupedSoal,
            'editQuestion' => $editQuestion,
            'options' => $editQuestion->LmsQuestionOption,
            'matching' => $matching,
            'type' => strtoupper($editQuestion->tipe_soal),
            'isUsed' => $isUsed
        ]);
    }

    // function bankSoal edit question
    public function lmsQuestionBankManagementEdit(Request $request, $questionId)
    {
        $user = Auth::user();

        $question = LmsQuestionBank::findOrFail($questionId);
        $questionType = strtoupper($question->tipe_soal);

        // GENERAL VALIDATION
        $rules = [
            'questions'   => 'required|string',
            'difficulty'  => 'required|in:Mudah,Sedang,Sukar',
            'bloom'       => 'required',
            'explanation' => 'required|string',
        ];

        $messages = [
            'questions.required'   => 'Pertanyaan wajib diisi.',
            'difficulty.required'  => 'Difficulty wajib dipilih.',
            'difficulty.in'        => 'Difficulty tidak valid.',
            'bloom.required'       => 'Bloom wajib diisi.',
            'explanation.required' => 'Pembahasan wajib diisi.',
        ];

        if ($questionType === 'MCQ') {
            $rules += [
                'options'    => 'required',
                'answer_key' => 'required|string',
            ];

            $messages += [
                'options.*.required' => 'Harap isi jawaban soal.',
                'answer_key.required' => 'Pilih jawaban benar.',
            ];
        }

        if ($questionType === 'MCMA') {
            $rules += [
                'options.*'      => 'required',
                'answer_key'   => 'required|array|min:1',
                'answer_key.*' => 'string',
            ];

            $messages += [
                'options.*.required' => 'Harap isi jawaban soal.',
                'answer_key.required' => 'Pilih minimal satu jawaban benar.',
                'answer_key.min' => 'Pilih minimal satu jawaban benar.',
            ];
        }

        if ($questionType === 'MATCHING') {
            $rules += [
                'left.*'     => 'required',
                'right.*'    => 'required',
                'pair_with.*' => 'required',
            ];

            $messages += [
                'left.*.required' => 'Harap isi jawaban soal.',
                'right.*.required' => 'Harap isi jawaban soal.',
                'pair_with.*.required' => 'Harap pilih pasangan.',
            ];
        }

        if ($questionType === 'PG_KOMPLEKS') {
            $rules += [
                'header_item' => 'required',
                'item.*' => 'required',
                'category.*' => 'required',
                'answer.*' => 'required',
            ];

            $messages += [
                'header_item.required' => 'Harap isi header item soal.',
                'item.*.required' => 'Harap isi item soal.',
                'category.*.required' => 'Harap isi kategori soal.',
                'answer.*.required' => 'Harap pilih jawaban kategori soal.',
            ];
        }

        $isUsed = $question->SchoolAssessmentQuestion()->whereHas('StudentAssessmentAnswer')->exists();

        if ($questionType === 'MATCHING') {
            // Ambil semua pasangan lama (LEFT) dari DB
            $existingPairs = LmsQuestionOption::where('question_id', $question->id)->where('options_key', 'like', 'LEFT%')->pluck('extra_data', 'id');
    
            $isPairChanged = false;
    
            // Loop semua input pair dari request
            foreach ($request->pair_with as $id => $newPair) {
    
                // Ambil data lama berdasarkan id
                $oldExtra = $existingPairs[$id] ?? null;
    
                // Ambil value pair_with dari extra_data
                $oldPair = is_array($oldExtra) ? ($oldExtra['pair_with'] ?? null) : (json_decode($oldExtra, true)['pair_with'] ?? null);
    
                // Bandingkan pair lama vs baru
                if ($oldPair != $newPair) {
                    $isPairChanged = true;
                    break;
                }
            }
    
            if ($isUsed && $questionType === 'MATCHING' && $isPairChanged) {
                return response()->json([
                    'status' => 'error',
                    'isUsed' => true,
                    'message' => 'Soal sudah digunakan, pasangan tidak bisa diubah.'
                ], 422);
            }
        }

        if ($questionType === 'PG_KOMPLEKS') {
            // Ambil semua option yang merupakan ITEM (bukan category)
            $existingAnswers = LmsQuestionOption::where('question_id', $question->id)->get()->filter(fn($opt) => ($opt->extra_data['side'] ?? null) === 'item')->pluck('extra_data', 'id');
    
            $isAnswerChanged = false;
    
            // Loop semua jawaban (mapping item -> category)
            foreach ($request->input('answer', []) as $itemId => $newCategory) {
    
                // Ambil extra_data lama dari DB
                $oldExtra = $existingAnswers[$itemId] ?? [];
    
                // Ambil category lama (answer)
                $oldCategory = $oldExtra['answer'] ?? null;
    
                // Bandingkan category lama vs baru
                if ($oldCategory != $newCategory) {
                    $isAnswerChanged = true;
                    break;
                }
            }
    
            if ($isUsed && $isAnswerChanged) {
                return response()->json([
                    'status' => 'error',
                    'isUsed' => true,
                    'message' => 'Soal sudah digunakan, pasangan category tidak dapat diubah.'
                ], 422);
            }
        }

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $question->update([
            'user_id' => $user->id,
            'questions'   => $request->questions,
            'difficulty'  => $request->difficulty,
            'bloom'       => $request->bloom,
            'explanation' => $request->explanation,
        ]);

        switch ($questionType) {

            // MCQ
            case 'MCQ':

                $options = LmsQuestionOption::whereIn('id', array_keys($request->options))->get()->keyBy('id');

                foreach ($request->options as $optionId => $value) {
                    $isCorrect = $request->answer_key === $options[$optionId]->options_key;

                    LmsQuestionOption::where('id', $optionId)->update([
                        'options_value' => $value,
                        'is_correct'    => $isCorrect,
                    ]);
                }

            break;

            // MCMA
            case 'MCMA':

                $options = LmsQuestionOption::whereIn('id', array_keys($request->options))->get()->keyBy('id');

                foreach ($request->options as $optionId => $value) {
                    $option = $options[$optionId];

                    LmsQuestionOption::where('id', $optionId)->update([
                        'options_value' => $value,
                        'is_correct'    => in_array($option->options_key, $request->answer_key),
                    ]);
                }

            break;

            // MATCHING
            case 'MATCHING':

                // Update LEFT
                foreach ($request->left as $id => $value) {
                    LmsQuestionOption::where('id', $id)->update([
                        'options_value' => $value,
                    ]);
                }

                // Update RIGHT
                foreach ($request->right as $id => $value) {
                    LmsQuestionOption::where('id', $id)->update([
                        'options_value' => $value,
                    ]);
                }

                // Update PAIR WITH
                foreach ($request->pair_with as $id => $value) {
                    $option = LmsQuestionOption::find($id);

                    // ambil data lama
                    $extra = $option->extra_data ?? [];

                    // update hanya field answer
                    $extra['pair_with'] = $value;

                    $option->update([
                        'extra_data' => $extra
                    ]);
                }

            break;

            case 'PG_KOMPLEKS':

                lmsQuestionBank::where('id', $questionId)->update([
                    'header_item' => $request->header_item
                ]);

                // Update ITEM
                foreach ($request->item as $id => $value) {
                    LmsQuestionOption::where('id', $id)->update([
                        'options_value' => $value,
                    ]);
                }

                // Update CATEGORY
                foreach ($request->category as $id => $value) {
                    LmsQuestionOption::where('id', $id)->update([
                        'options_value' => $value,
                    ]);
                }

                // Update ANSWER katgori pada item
                foreach ($request->answer as $id => $value) {
                    $option = LmsQuestionOption::find($id);

                    // ambil data lama
                    $extra = $option->extra_data ?? [];

                    // update hanya field answer
                    $extra['answer'] = $value;

                    $option->update([
                        'extra_data' => $extra
                    ]);
                }

            break;
        }

        broadcast(new BankSoalLmsEditPG($question, $questionId))->toOthers();

        return response()->json([
            'status' => 'success',
            'message' => 'Soal berhasil diupdate',
        ]);
    }
    
    // function edit image bank soal (for ckeditor)
    public function editImageBankSoal(Request $request) {
        if ($request->hasFile('upload')) {
            $originName = $request->file('upload')->getClientOriginalName();
            $fileName = pathInfo($originName, PATHINFO_FILENAME);
            $extension = $request->file('upload')->getClientOriginalExtension();
            $fileName = $fileName . '_' . time() . '.' . $extension;

            $request->file('upload')->move(public_path('lms-docx-image'), $fileName);

            $url = "/lms-docx-image/$fileName";
            return response()->json(['fileName' => $fileName, 'uploaded' => 1, 'url' => $url]);
        }
    }

    // function delete image bank soal (for ckeditor)
    public function deleteImageBankSoal(Request $request) {
        $request->validate([
            'imageUrl' => 'required|url',
        ]);

        $imagePath = str_replace(asset(''), '', $request->imageUrl); // Hapus base URL
        $fullImagePath = public_path($imagePath);

        if (file_exists($fullImagePath)) {
            unlink($fullImagePath); // Hapus gambar
            return response()->json(['message' => 'Gambar berhasil dihapus']);
        }

        return response()->json(['message' => 'Gambar tidak ditemukan'], 404);
    }
}