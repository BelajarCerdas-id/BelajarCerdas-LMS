<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

/**
 * Cache headers for every exam-state JSON response (timer, attempt status,
 * exam form data). AuthMiddleware already sends no-store for logged-in
 * routes, but exam state must never rely on a middleware group staying
 * intact: these responses are per-user, served from URLs that are identical
 * for every student of the exam, and a stale copy (browser cache, service
 * worker, CDN cache rule) hands one student another student's countdown —
 * the "Waktu Habis" mid-exam reports of 2026-11-25.
 */
class ExamStateHeaders
{
    public static function noStore(JsonResponse $response): JsonResponse
    {
        return $response->withHeaders([
            'Cache-Control' => 'no-cache, no-store, max-age=0, must-revalidate, private',
            'Pragma' => 'no-cache',
            'Expires' => 'Sat, 01 Jan 1990 00:00:00 GMT',
        ]);
    }
}
