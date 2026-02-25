<?php

namespace App\Services\LMS\AssessmentManagement\Teacher;

use App\Models\SchoolAssessment;

class LmsReviewFileService
{
    private function guessMime($ext)
    {
        return match (strtolower($ext)) {
            'mp4', 'webm', 'ogg' => 'video/' . $ext,
            'pdf'               => 'application/pdf',
            'jpg', 'jpeg', 'png', 'webp' => 'image/' . $ext,
            default             => 'application/octet-stream',
        };
    }

    public function getByAssessmentId($assessmentId)
    {
        $items = SchoolAssessment::where('id', $assessmentId)->get();

        return $items->map(function ($item) {
            $extension = pathinfo($item->assessment_value_file, PATHINFO_EXTENSION);

            return [
                'file_name' => $item->assessment_original_filename,
                'file_url' => asset('assessment/assessment-file/' . $item->assessment_value_file),
                'mime' => $this->guessMime($extension),
                'type' => 'file'
            ];
        });
    }
}