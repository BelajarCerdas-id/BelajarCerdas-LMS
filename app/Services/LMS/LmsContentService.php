<?php

namespace App\Services\LMS;

use App\Models\Kelas;
use App\Models\LmsContent;
use App\Models\LmsContentItem;
use App\Models\SchoolLmsContent;
use App\Models\SchoolPartner;
use App\Models\ServiceRule;
use Illuminate\Support\Facades\DB;

class LmsContentService {
    
    // function store lms content
    public function store(array $data, int $userId, ?int $schoolId = null): LmsContent
    {
        return DB::transaction(function () use ($data, $userId, $schoolId) {

            $content = LmsContent::create([
                'user_id'      => $userId,
                'service_id'   => $data['service_id'],
                'school_partner_id' => $schoolId,
                'kurikulum_id' => $data['kurikulum_id'],
                'kelas_id'     => $data['kelas_id'],
                'mapel_id'     => $data['mapel_id'],
                'bab_id'       => $data['bab_id'],
                'sub_bab_id'   => $data['sub_bab_id'],
            ]);

            $this->syncItems($content, $data);

            $this->syncSchoolRelation($content, $schoolId);

            return $content;
        });
    }

    // function update lms content
    public function update(LmsContent $content, array $data, int $userId): LmsContent
    {
        return DB::transaction(function () use ($content, $data, $userId) {

            $content->update([
                'user_id'      => $userId,
                'kurikulum_id' => $data['kurikulum_id'],
                'kelas_id'     => $data['kelas_id'],
                'mapel_id'     => $data['mapel_id'],
                'bab_id'       => $data['bab_id'],
                'sub_bab_id'   => $data['sub_bab_id'],
                'service_id'   => $data['service_id'],
            ]);

            $this->syncItems($content, $data, true);

            return $content;
        });
    }

    protected function syncItems(LmsContent $content, array $data, bool $isUpdate = false)
    {
        $rules = ServiceRule::where('service_id', $data['service_id'])->get();

        foreach ($rules as $rule) {

            if ($rule->upload_type === 'text') {
                if ($isUpdate) {
                    LmsContentItem::where([
                        'lms_content_id' => $content->id,
                        'service_rule_id' => $rule->id,
                    ])->delete();
                }

                foreach ($data['text'][$rule->id] ?? [] as $text) {
                    if (!filled($text)) continue;

                    LmsContentItem::create([
                        'lms_content_id' => $content->id,
                        'service_rule_id' => $rule->id,
                        'value_text' => $text,
                    ]);
                }
            }

            if ($rule->upload_type === 'file' && request()->hasFile("files.{$rule->id}")) {
                $file = request()->file("files.{$rule->id}");

                $filename = uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('lms-contents'), $filename);

                LmsContentItem::updateOrCreate(
                    [
                        'lms_content_id' => $content->id,
                        'service_rule_id' => $rule->id,
                    ],
                    [
                        'value_file' => $filename,
                        'original_filename' => $file->getClientOriginalName(),
                    ]
                );
            }
        }
    }

    protected function syncSchoolRelation(LmsContent $content, ?int $schoolId)
    {
        if ($schoolId) {
            SchoolLmsContent::create([
                'lms_content_id' => $content->id,
                'school_partner_id' => $schoolId,
            ]);
        } else {
                $getKelas = Kelas::where('id', $content->kelas_id)->first();
    
                $kelasName = strtolower($getKelas->kelas);
    
                // Mapping jenjang → daftar kelas yang valid di jenjang tersebut
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
    
                // Ambil jenjang yang memang memiliki kelas tersebut (misal kelas 7 → SMP, MTS)
                $allowedJenjangs = collect($mappingClasses)->filter(fn ($kelasList) => in_array($kelasName, $kelasList))->keys();
            
                // Ambil semua sekolah dengan jenjang yang sesuai
                $schools = SchoolPartner::whereIn(DB::raw('UPPER(jenjang_sekolah)'), $allowedJenjangs)->get();
    
                // Hubungkan mapel global ke semua sekolah yang sesuai jenjangnya
                $schools->each(function ($school) use ($content) {
                    SchoolLmsContent::create([
                        'lms_content_id' => $content->id,
                        'school_partner_id' => $school->id,
                    ]);
                });
        }
        // logic global → multi school tetap di sini
    }

}