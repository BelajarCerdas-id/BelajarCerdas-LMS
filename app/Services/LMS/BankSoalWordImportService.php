<?php

namespace App\Services\LMS;

use App\Events\BankSoalLmsUploaded;
use App\Models\LmsQuestionBank;
use App\Models\LmsQuestionOption;
use App\Services\DocxExtractor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpWord\IOFactory;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class BankSoalWordImportService
{
    public function bankSoalImportService(Request $request)
    {
        $userId = Auth::id();
        Log::info("[BankSoalImport] Memulai proses import bank soal.", [
            'user_id' => $userId,
            'kurikulum_id' => $request->kurikulum_id,
            'mapel_id' => $request->mapel_id
        ]);

        $extractor = new DocxExtractor('lms');

        // Validasi input form dari frontend
        $validator = Validator::make($request->all(), [
            'bulkUpload-lms'    => 'required|file|mimes:docx|max:100000',
            'kurikulum_id'      => 'required',
            'kelas_id'          => 'required',
            'mapel_id'          => 'required',
            'question_category' => 'required',
        ], [
            'bulkUpload-lms.required'    => 'Harap upload soal.',
            'bulkUpload-lms.max'         => 'Ukuran file melebihi kapasitas yang ditentukan.',
            'kurikulum_id.required'      => 'Harap pilih kurikulum.',
            'kelas_id.required'          => 'Harap pilih kelas.',
            'mapel_id.required'          => 'Harap pilih mapel.',
            'question_category.required' => 'Harap pilih kategori soal.',
        ]);

        $formErrors = $validator->fails() ? $validator->errors()->toArray() : [];
        if (!empty($formErrors)) {
            Log::warning("[BankSoalImport] Validasi form gagal.", ['errors' => $formErrors]);
        }

        $allWordValidationErrors = [];
        $validSoalData = []; 
        $uploadedFile = $request->file('bulkUpload-lms');

        $docxPath = storage_path('app/tmp_soal.docx');
        $outputHtmlPath = storage_path('app/converted_soal.html');
        $mediaImages = [];

        if ($uploadedFile && empty($formErrors)) {
            Log::info("[BankSoalImport] File DOCX diterima. Memindahkan ke storage sementara.");
            $uploadedFile->move(storage_path('app'), 'tmp_soal.docx');

            Log::info("[BankSoalImport] Menjalankan Pandoc untuk konversi DOCX ke HTML.");
            $process = new Process(['pandoc', $docxPath, '-f', 'docx', '-t', 'html', '--mathml', '-o', $outputHtmlPath]);
            $process->run();

            if (!$process->isSuccessful()) {
                Log::error("[BankSoalImport] Pandoc gagal dieksekusi.", ['error_output' => $process->getErrorOutput()]);
                throw new ProcessFailedException($process);
            }
            Log::info("[BankSoalImport] Pandoc berhasil dieksekusi.");

            $styledData = [];
            $forcePandocMode = false;
            
            Log::info("[BankSoalImport] Mengekstrak gambar dari dokumen.");
            $extractor->extractImagesFromDocxFile($docxPath, $mediaImages);
            Log::debug("[BankSoalImport] Hasil ekstraksi gambar.", ['total_images' => count($mediaImages)]);
    
            if ($extractor->docxHasEquation($docxPath) || $extractor->docxHasList($docxPath)) {
                Log::info("[BankSoalImport] Deteksi equation/list. Menggunakan Pandoc sepenuhnya (force mode).");
                $forcePandocMode = true;
            } else {
                try {
                    Log::info("[BankSoalImport] Mencoba memuat dokumen dengan PhpWord untuk styling.");
                    $phpWord = IOFactory::load($docxPath);
                    $styledData = $extractor->extractStyledTableData($phpWord, $mediaImages);
                    Log::info("[BankSoalImport] PhpWord berhasil memuat dokumen.");
                } catch (\Throwable $e) {
                    Log::warning("[BankSoalImport] Gagal load PhpWord, akan menggunakan fallback Pandoc.", ['message' => $e->getMessage()]);
                }
            }
    
            Log::info("[BankSoalImport] Mem-parsing HTML hasil Pandoc menggunakan DOMDocument.");
            $htmlContent = file_get_contents($outputHtmlPath);
            $dom = new \DOMDocument();
            libxml_use_internal_errors(true);
            $dom->loadHTML(mb_convert_encoding($htmlContent, 'HTML-ENTITIES', 'UTF-8'));
            libxml_clear_errors();
    
            $tables = $dom->getElementsByTagName('table');
            Log::info("[BankSoalImport] Ditemukan " . $tables->length . " tabel/soal dalam dokumen.");

            $pandocTableValues = [];
            
            foreach ($tables as $tIndex => $t) {
                if (!$t instanceof \DOMElement) continue;
                $rowsForFallback = $t->getElementsByTagName('tr');
                foreach ($rowsForFallback as $row) {
                    if (!$row instanceof \DOMElement) continue;
    
                    $cells = [];
                    foreach ($row->childNodes as $child) {
                        if ($child instanceof \DOMElement && in_array(strtolower($child->nodeName), ['td', 'th'])) {
                            $cells[] = $child;
                        }
                    }
                    if (count($cells) < 2) continue;
    
                    $keyHtml = '';
                    foreach ($cells[0]->childNodes as $child) {
                        $keyHtml .= $dom->saveHTML($child);
                    }
                    $normalizedKey = strtoupper(trim($extractor->normalizeTextContent($keyHtml)));
                    $key = preg_replace('/[\s\xA0]+/u', '', $normalizedKey);
                    if ($key === '') $key = 'QUESTION';
    
                    $valueHtml = '';
                    foreach ($cells[1]->childNodes as $child) {
                        $valueHtml .= $dom->saveHTML($child);
                    }
    
                    $pandocTableValues[$tIndex][$key] = $valueHtml;
                }
            }
    
            Log::info("[BankSoalImport] Memulai proses validasi data per tabel.");
            foreach ($tables as $index => $table) {
                if (!$table instanceof \DOMElement) continue;
                $rows = $table->getElementsByTagName('tr');
                $dataSoal = [];
                $validationErrors = [];
                $soalNumber = $index + 1;
    
                if (!$forcePandocMode && isset($styledData[$index]) && is_array($styledData[$index]) && count($styledData[$index]) > 0) {
                    $dataSoal = $styledData[$index];
                    foreach ($dataSoal as $k => $htmlValue) {
                        $styledHtml = $htmlValue;
                        $pandocHtml = $pandocTableValues[$index][$k] ?? '';
                        $dataSoal[$k] = $extractor->combineStyledAndPandoc($styledHtml, $pandocHtml);
                    }
                } else {
                    foreach ($rows as $row) {
                        if (!$row instanceof \DOMElement) continue;
    
                        $cells = [];
                        foreach ($row->childNodes as $child) {
                            if ($child instanceof \DOMElement && in_array(strtolower($child->nodeName), ['td', 'th'])) {
                                $cells[] = $child;
                            }
                        }
                        if (count($cells) < 2) continue;
    
                        $innerHtml = '';
                        foreach ($cells[0]->childNodes as $child) {
                            $innerHtml .= $dom->saveHTML($child);
                        }
    
                        $normalizedText = $extractor->normalizeTextContent($innerHtml);
                        $rawHtmlKey = strtoupper(trim($normalizedText));
                        $key = preg_replace('/[\s\xA0]+/u', '', $rawHtmlKey);
                        if (empty($key) && empty($dataSoal['QUESTION'])) $key = 'QUESTION';
    
                        $rawHtmlValue = '';
                        foreach ($cells[1]->childNodes as $child) {
                            $rawHtmlValue .= $dom->saveHTML($child);
                        }
    
                        $styledValue = $styledData[$index][$key] ?? '';
                        $plainPandoc = $extractor->normalizeTextContent($rawHtmlValue);
                        $plainStyled = $extractor->normalizeTextContent($styledValue);
    
                        if (empty($styledValue)) {
                            $value = $rawHtmlValue;
                        } elseif ($plainPandoc === $plainStyled) {
                            $value = $styledValue;
                        } elseif (str_contains($rawHtmlValue, '<math') || str_contains($rawHtmlValue, '<img') || str_contains($rawHtmlValue, '<ul') || str_contains($rawHtmlValue, '<ol')) {
                            $value = $extractor->mergeStyledAndPandocHtml($rawHtmlValue, $styledValue, $mediaImages);
                        } else {
                            $value = $styledValue;
                        }
    
                        if (!str_contains($value, '<p>') && !str_contains($value, '<div>')) {
                            $value = "<p>$value</p>";
                        }
                        if (!empty($key)) $dataSoal[$key] = $value;
                    }
                }
    
                // Aliasing ITEM -> LEFT, CATEGORY -> RIGHT
                foreach ($dataSoal as $k => $v) {
                    if (str_starts_with($k, 'ITEM')) {
                        $dataSoal[str_replace('ITEM', 'LEFT', $k)] = $v;
                        unset($dataSoal[$k]);
                    }
                    if (str_starts_with($k, 'CATEGORY')) {
                        $dataSoal[str_replace('CATEGORY', 'RIGHT', $k)] = $v;
                        unset($dataSoal[$k]);
                    }
                }

                // Log isi mentah dataSoal sebelum divalidasi
                Log::debug("[BankSoalImport] Data mentah Soal ke-$soalNumber:", ['data' => array_keys($dataSoal)]);

                $requiredBaseFields = ['QUESTION', 'DIFFICULTY', 'BLOOM', 'TYPE'];
                foreach ($requiredBaseFields as $field) {
                    if (!isset($dataSoal[$field]) || $extractor->isMeaningfullyEmpty($dataSoal[$field])) {
                        $validationErrors[] = "Soal ke-$soalNumber: Field '$field' tidak boleh kosong.";
                    }
                }
    
                $normalizeAnswers = function (?string $raw): array {
                    if (!$raw) return [];
                    return array_values(array_filter(array_map(
                        fn($v) => strtoupper(trim($v)),
                        preg_split('/[,;]+/', strip_tags($raw))
                    )));
                };
    
                $isEmpty = fn($v) => !isset($v) || $extractor->isMeaningfullyEmpty($v);
                $type = strtolower(trim(strip_tags($dataSoal['TYPE'] ?? '')));
                $answers = $normalizeAnswers($dataSoal['ANSWER'] ?? null);
    
                $options = array_filter(
                    $dataSoal,
                    fn($v, $k) => str_starts_with($k, 'OPTION') && !$isEmpty($v),
                    ARRAY_FILTER_USE_BOTH
                );
    
                switch ($type) {
                    case 'mcq':
                        if (count($options) < 2) $validationErrors[] = "Soal ke-$soalNumber: MCQ minimal punya 2 OPTION.";
                        if (count($answers) !== 1) $validationErrors[] = "Soal ke-$soalNumber: MCQ harus tepat 1 jawaban.";
                        if (!isset($dataSoal[$answers[0] ?? '']) || $isEmpty($dataSoal[$answers[0]])) {
                            $validationErrors[] = "Soal ke-$soalNumber: ANSWER tidak cocok dengan OPTION.";
                        }
                        break;
    
                    case 'mcma':
                        if (count($options) < 2) $validationErrors[] = "Soal ke-$soalNumber: MCMA minimal punya 2 OPTION.";
                        if (count($answers) < 1) $validationErrors[] = "Soal ke-$soalNumber: MCMA minimal 1 jawaban.";
                        foreach ($answers as $a) {
                            if (!isset($dataSoal[$a]) || $isEmpty($dataSoal[$a])) {
                                $validationErrors[] = "Soal ke-$soalNumber: ANSWER '$a' tidak valid atau OPTION kosong.";
                            }
                        }
                        break;
    
                    case 'matching':
                    case 'pg_kompleks':
                        if ($isEmpty($dataSoal['ANSWER'] ?? null)) {
                            $validationErrors[] = "Soal ke-$soalNumber: $type wajib punya ANSWER.";
                            break;
                        }
    
                        $isMatrix = $type === 'pg_kompleks';
                        
                        $leftKeys = array_map('strtoupper', array_keys(array_filter($dataSoal, fn($v,$k) => str_starts_with($k,'LEFT') && !$isEmpty($v), ARRAY_FILTER_USE_BOTH)));
                        $rightKeys = array_map('strtoupper', array_keys(array_filter($dataSoal, fn($v,$k) => str_starts_with($k,'RIGHT') && !$isEmpty($v), ARRAY_FILTER_USE_BOTH)));
    
                        if (!$leftKeys || !$rightKeys) {
                            $validationErrors[] = "Soal ke-$soalNumber: $type wajib punya LEFT/ITEM & RIGHT/CATEGORY.";
                            break;
                        }
    
                        $pairMap = [];
                        $answerRaw = html_entity_decode(strip_tags($dataSoal['ANSWER']));
                        
                        foreach (preg_split('/\r\n|\r|\n/', $answerRaw) as $line) {
                            $parts = preg_split('/\s*,\s*/', trim($line));
                            if (count($parts) !== 2) {
                                $validationErrors[] = "Soal ke-$soalNumber: format ANSWER harus ITEM,CATEGORY atau LEFT,RIGHT.";
                                continue;
                            }
    
                            [$l, $r] = array_map(fn($v) => strtoupper(trim($v)), $parts);
                            
                            if (str_starts_with($l, 'ITEM')) $l = str_replace('ITEM', 'LEFT', $l);
                            if (str_starts_with($r, 'CATEGORY')) $r = str_replace('CATEGORY', 'RIGHT', $r);
    
                            if (!in_array($l, $leftKeys)) $validationErrors[] = "Soal ke-$soalNumber: Key LEFT/ITEM '$l' tidak valid.";
                            if (!in_array($r, $rightKeys)) $validationErrors[] = "Soal ke-$soalNumber: Key RIGHT/CATEGORY '$r' tidak valid.";
    
                            $pairMap[$l] = $r;
                        }
    
                        if (!$isMatrix && count($pairMap) !== count($leftKeys)) {
                            $validationErrors[] = "Soal ke-$soalNumber: semua LEFT harus punya pasangan.";
                        }
                        if ($isMatrix && count($pairMap) < 1) {
                            $validationErrors[] = "Soal ke-$soalNumber: minimal harus ada 1 pasangan jawaban.";
                        }
                        break;
    
                    case 'essay':
                    case 'tf':
                    case 'yn':
                        break;
    
                    default:
                        $validationErrors[] = "Soal ke-$soalNumber: TYPE '$type' tidak dikenali.";
                }
    
                if (!empty($validationErrors)) {
                    Log::warning("[BankSoalImport] Soal ke-$soalNumber gagal validasi konten.", ['errors' => $validationErrors]);
                    $allWordValidationErrors = array_merge($allWordValidationErrors, $validationErrors);
                    continue;
                }
    
                foreach ($dataSoal as $k => $v) {
                    $v = $extractor->replaceImageSrc($v, $mediaImages);
                    $v = $extractor->cleanHtml($v);
                    $dataSoal[$k] = $v;
                }
                $validSoalData[] = $dataSoal;
                Log::info("[BankSoalImport] Soal ke-$soalNumber lolos validasi.");
            }

            if (!empty($allWordValidationErrors)) {
                Log::info("[BankSoalImport] Menghapus temporary images karena terdapat error validasi dokumen.");
                foreach ($mediaImages as $img) {
                    if (!empty($img['public_url'] ?? '')) {
                        $imgPath = public_path($img['public_url']);
                        if (file_exists($imgPath)) @unlink($imgPath);
                    }
                }
            }
        }

        if (!empty($formErrors) || !empty($allWordValidationErrors)) {
            Log::error("[BankSoalImport] Proses import digagalkan karena terdapat error validasi.");
            @unlink($docxPath);
            @unlink($outputHtmlPath);
            return response()->json([
                'status' => 'validation-error',
                'errors' => [
                    'form_errors' => $formErrors,
                    'word_validation_errors' => $allWordValidationErrors,
                ],
            ], 422);
        }

        Log::info("[BankSoalImport] Memulai transaksi insert ke database. Total soal valid: " . count($validSoalData));
        $createBankSoal = null;
        
        foreach ($validSoalData as $index => $dataSoal) {
            $type = strtolower(trim(strip_tags($dataSoal['TYPE'] ?? '')));
            $answers = $normalizeAnswers($dataSoal['ANSWER'] ?? null);
            $schoolPartnerId = $request->school_partner_id;

            // 1. Ubah query exists() menjadi first(['id']) untuk mengambil data ID
            $existingQuestionQuery = $schoolPartnerId
                ? LmsQuestionBank::where('questions', $dataSoal['QUESTION'])->where('school_partner_id', $schoolPartnerId)
                : LmsQuestionBank::where('questions', $dataSoal['QUESTION']);

            $existingQuestion = $existingQuestionQuery->first(['id']);

            // 2. Tampilkan ID database dari variabel $existingQuestion->id
            if ($existingQuestion) {
                Log::info("[BankSoalImport] Soal urutan dokumen $index terdeteksi duplikat dengan data di database (DB ID: {$existingQuestion->id}), dilewati.");
                continue;
            }

            $queryStatus = LmsQuestionBank::where('tipe_soal', trim(strip_tags($dataSoal['TYPE'])))->where('status_bank_soal', 'Unpublish');
            if ($request->sub_bab_id) {
                $queryStatus->where('sub_bab_id', $request->sub_bab_id);
            } else {
                $queryStatus->whereNull('sub_bab_id');
            }
            $statusBankSoal = $queryStatus->exists() ? 'Unpublish' : 'Publish';

            try {
                $createBankSoal = LmsQuestionBank::create([
                    'user_id'           => $userId,
                    'school_partner_id' => $schoolPartnerId ?? null,
                    'kurikulum_id'      => $request->kurikulum_id,
                    'kelas_id'          => $request->kelas_id,
                    'mapel_id'          => $request->mapel_id,
                    'bab_id'            => $request->bab_id,
                    'sub_bab_id'        => $request->sub_bab_id,
                    'questions'         => $dataSoal['QUESTION'],
                    'header_item'       => trim(strip_tags($dataSoal['HEADER_ITEM'] ?? '')),
                    'difficulty'        => trim(strip_tags($dataSoal['DIFFICULTY'] ?? '')),
                    'bloom'             => trim(strip_tags($dataSoal['BLOOM'] ?? '')),
                    'explanation'       => $dataSoal['EXPLANATION'] ?? '',
                    'tipe_soal'         => trim(strip_tags($dataSoal['TYPE'] ?? '')),
                    'status_bank_soal'  => $statusBankSoal,
                    'question_source'   => $schoolPartnerId ? 'school' : 'default',
                    'question_category' => $request->question_category,
                ]);
                Log::info("[BankSoalImport] Soal insert sukses (ID: {$createBankSoal->id}, Tipe: {$type}).");
            } catch (\Exception $e) {
                Log::error("[BankSoalImport] Gagal insert soal utama ke database.", ['error' => $e->getMessage()]);
                continue;
            }

            // INSERT OPSI JAWABAN
            try {
                if (in_array($type, ['mcq', 'mcma'])) {
                    foreach ($dataSoal as $key => $value) {
                        if (!str_starts_with($key, 'OPTION')) continue;
                        LmsQuestionOption::create([
                            'question_id'   => $createBankSoal->id,
                            'options_key'   => $key,
                            'options_value' => $value,
                            'is_correct'    => in_array($key, $answers),
                        ]);
                    }
                } elseif (in_array($type, ['tf', 'yn'])) {
                    $choices = $type === 'tf' ? ['TRUE', 'FALSE'] : ['YES', 'NO'];
                    foreach ($choices as $choice) {
                        LmsQuestionOption::create([
                            'question_id'   => $createBankSoal->id,
                            'options_key'   => $choice,
                            'options_value' => $choice,
                            'is_correct'    => in_array($choice, $answers),
                        ]);
                    }
                } elseif (in_array($type, ['matching', 'pg_kompleks'])) {
                    $pairMap = [];
                    $answerRaw = html_entity_decode(strip_tags($dataSoal['ANSWER'] ?? ''));
                    foreach (preg_split('/\r\n|\r|\n/', $answerRaw) as $line) {
                        if (!trim($line)) continue;
                        [$l, $r] = array_map(fn($v) => strtoupper(trim($v)), preg_split('/\s*,\s*/', $line));
                        
                        if (str_starts_with($l, 'ITEM')) $l = str_replace('ITEM', 'LEFT', $l);
                        if (str_starts_with($r, 'CATEGORY')) $r = str_replace('CATEGORY', 'RIGHT', $r);
                        
                        if ($l && $r) $pairMap[$l] = $r;
                    }
    
                    foreach ($dataSoal as $key => $value) {
                        if (!is_string($key) || $extractor->isMeaningfullyEmpty($value)) continue;
    
                        if (str_starts_with($key, 'RIGHT')) {
                            LmsQuestionOption::create([
                                'question_id'   => $createBankSoal->id,
                                'options_key'   => strtoupper(trim($key)),
                                'options_value' => $value,
                                'is_correct'    => false,
                                'extra_data'    => ['side' => ($type === 'matching' ? 'right' : 'category')],
                            ]);
                        }
    
                        if (str_starts_with($key, 'LEFT')) {
                            $leftKey = strtoupper(trim($key));
                            LmsQuestionOption::create([
                                'question_id'   => $createBankSoal->id,
                                'options_key'   => $leftKey,
                                'options_value' => $value,
                                'is_correct'    => false,
                                'extra_data'    => [
                                    'side'      => ($type === 'matching' ? 'left' : 'item'),
                                    'pair_with' => $pairMap[$leftKey] ?? null,
                                    'answer'    => $pairMap[$leftKey] ?? null,
                                ],
                            ]);
                        }
                    }
                }
                Log::debug("[BankSoalImport] Opsi jawaban untuk soal ID {$createBankSoal->id} berhasil di-insert.");
            } catch (\Exception $e) {
                Log::error("[BankSoalImport] Gagal insert opsi jawaban untuk soal ID {$createBankSoal->id}.", ['error' => $e->getMessage()]);
            }
        }

        if ($createBankSoal) {
            Log::info("[BankSoalImport] Menjalankan broadcast event BankSoalLmsUploaded.");
            broadcast(new BankSoalLmsUploaded($createBankSoal))->toOthers();
        }

        Log::info("[BankSoalImport] Proses import selesai. Membersihkan file temporary.");
        @unlink($docxPath);
        @unlink($outputHtmlPath);

        return response()->json([
            'status' => 'success',
            'message' => 'Bank Soal berhasil diupload.',
        ]);
    }
}