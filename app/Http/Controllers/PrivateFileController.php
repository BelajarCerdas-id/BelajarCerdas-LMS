<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;

/**
 * Streams private uploaded files (student/teacher submissions) from the local disk.
 *
 * shortcut: any authenticated user can read any file in these zones, per-file
 * ACL (owner/teacher of that assessment) comes with the refactored demo.
 */
class PrivateFileController extends Controller
{
    private const ALLOWED_ZONES = [
        'lms-assessment-submission/assessment-test/',
        'assessment/assessment-file/',
        'assessment/assessment-file-submission/',
    ];

    public function show(string $zone, string $filename)
    {
        $path = $zone . '/' . $filename;

        if (!in_array($zone . '/', self::ALLOWED_ZONES, true)
            || $filename !== basename($filename)
            || str_contains($filename, '..')
            || !Storage::disk('local')->exists($path)) {
            abort(404);
        }

        return Storage::disk('local')->response($path);
    }
}
