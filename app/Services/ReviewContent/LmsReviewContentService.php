<?php

namespace App\Services\ReviewContent;

use App\Models\LmsContentItem;

class LmsReviewContentService
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
    
    public function getByContentId($contentId)
    {
        $items = LmsContentItem::with('ServiceRule', 'LmsContent.Service')
            ->where('lms_content_id', $contentId)
            ->get();

        return $items->map(function ($item) {

            $serviceName = $item->LmsContent?->Service?->name;

            if (!$item->value_file) {
                return [
                    'service_name' => $serviceName,
                    'rule_id' => $item->service_rule_id,
                    'rule_name' => $item->ServiceRule?->name,
                    'value_text' => $item->value_text,
                    'type' => 'text'
                ];
            }

            $extension = pathinfo($item->value_file, PATHINFO_EXTENSION);

            return [
                'service_name' => $serviceName,
                'rule_id'   => $item->service_rule_id,
                'rule_name' => $item->ServiceRule?->name,
                'file_name'=> $item->original_filename,
                'file_url' => asset('lms-contents/' . $item->value_file),
                'mime'     => $this->guessMime($extension),
                'type'     => 'file'
            ];
        });
    }
}