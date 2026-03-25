<?php

namespace App\Http\Controllers;

use App\Models\LearningResource;
use App\Models\Kurikulum;
use App\Models\Kelas;
use App\Models\Mapel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class LearningResourceController extends Controller
{
    /**
     * Display library for students
     */
    public function index(Request $request)
    {
        $resourceType = $request->get('type', 'all');
        $kelasId = $request->get('kelas_id');
        $search = $request->get('search');

        $query = LearningResource::where('status', 'published')->latest();

        if ($resourceType !== 'all') {
            $query->where('resource_type', $resourceType);
        }

        if ($kelasId) {
            $query->where('kelas_id', $kelasId);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%")
                  ->orWhere('subject', 'LIKE', "%{$search}%");
            });
        }

        $resources = $query->paginate(12);

        $kurikulums = Kurikulum::all();
        $kelasList = Kelas::all();
        $mapels = Mapel::all();

        return view('learning-resources.index', compact(
            'resources', 'resourceType', 'kelasId', 'search', 'kurikulums', 'kelasList', 'mapels'
        ));
    }

    /**
     * Manage library resources (Admin only)
     */
    public function manage(Request $request)
    {
        $this->ensureAdministrator();

        $search = $request->get('search');
        $resourceType = $request->get('type', 'all');

        $query = LearningResource::latest();

        if ($resourceType !== 'all') {
            $query->where('resource_type', $resourceType);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        $resources = $query->paginate(20);

        $resourceTypes = ['library_series', 'ppt', 'lkpd'];

        return view('library.manage', compact('resources', 'search', 'resourceType', 'resourceTypes'));
    }

    /**
     * Show form to create new resource
     */
    public function create()
    {
        $this->ensureAdministrator();

        $kurikulums = Kurikulum::all();
        $kelasList = Kelas::all();
        $mapels = Mapel::all();

        return view('library.create', compact('kurikulums', 'kelasList', 'mapels'));
    }

    /**
     * Store new resource
     */
    public function store(Request $request)
    {
        $this->ensureAdministrator();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'resource_type' => 'required|in:library_series,ppt,lkpd',
            'kelas_id' => 'required|exists:kelas,id',
            'subject' => 'required|string',
            'author' => 'nullable|string',
            'publisher' => 'nullable|string',
            'file' => 'required|file|mimes:pdf,ppt,pptx,doc,docx|max:10240',
            'thumbnail' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Upload file
        $file = $request->file('file');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $filePath = $file->storeAs('public/learning-resources', $fileName);

        // Upload thumbnail if exists
        $thumbnailPath = null;
        if ($request->hasFile('thumbnail')) {
            $thumbnail = $request->file('thumbnail');
            $thumbnailName = 'thumb_' . time() . '_' . $thumbnail->getClientOriginalName();
            $thumbnailPath = $thumbnail->storeAs('public/learning-resources/thumbnails', $thumbnailName);
        }

        LearningResource::create([
            'user_id' => Auth::id(),
            'title' => $validated['title'],
            'description' => $validated['description'],
            'resource_type' => $validated['resource_type'],
            'kelas_id' => $validated['kelas_id'],
            'subject' => $validated['subject'],
            'author' => $validated['author'],
            'publisher' => $validated['publisher'],
            'file_path' => $filePath,
            'file_name' => $fileName,
            'file_mime' => $file->getMimeType(),
            'thumbnail_path' => $thumbnailPath,
            'status' => 'published',
            'is_active' => true,
        ]);

        return redirect()->route('library.manage')
            ->with('success', 'Resource berhasil ditambahkan!');
    }

    /**
     * Show form to edit resource
     */
    public function edit($id)
    {
        $this->ensureAdministrator();

        $resource = LearningResource::findOrFail($id);
        $kurikulums = Kurikulum::all();
        $kelasList = Kelas::all();
        $mapels = Mapel::all();

        return view('library.edit', compact('resource', 'kurikulums', 'kelasList', 'mapels'));
    }

    /**
     * Update resource
     */
    public function update(Request $request, $id)
    {
        $this->ensureAdministrator();

        $resource = LearningResource::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'resource_type' => 'required|in:library_series,ppt,lkpd',
            'kelas_id' => 'required|exists:kelas,id',
            'subject' => 'required|string',
            'author' => 'nullable|string',
            'publisher' => 'nullable|string',
            'file' => 'nullable|file|mimes:pdf,ppt,pptx,doc,docx|max:10240',
            'thumbnail' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Upload new file if exists
        if ($request->hasFile('file')) {
            // Delete old file
            if ($resource->file_path && Storage::exists($resource->file_path)) {
                Storage::delete($resource->file_path);
            }

            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('public/learning-resources', $fileName);

            $resource->file_path = $filePath;
            $resource->file_name = $fileName;
            $resource->file_mime = $file->getMimeType();
        }

        // Upload new thumbnail if exists
        if ($request->hasFile('thumbnail')) {
            // Delete old thumbnail
            if ($resource->thumbnail_path && Storage::exists($resource->thumbnail_path)) {
                Storage::delete($resource->thumbnail_path);
            }

            $thumbnail = $request->file('thumbnail');
            $thumbnailName = 'thumb_' . time() . '_' . $thumbnail->getClientOriginalName();
            $thumbnailPath = $thumbnail->storeAs('public/learning-resources/thumbnails', $thumbnailName);
            $resource->thumbnail_path = $thumbnailPath;
        }

        $resource->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'resource_type' => $validated['resource_type'],
            'kelas_id' => $validated['kelas_id'],
            'subject' => $validated['subject'],
            'author' => $validated['author'],
            'publisher' => $validated['publisher'],
        ]);

        return redirect()->route('library.manage')
            ->with('success', 'Resource berhasil diupdate!');
    }

    /**
     * Delete resource
     */
    public function destroy($id)
    {
        $this->ensureAdministrator();

        $resource = LearningResource::findOrFail($id);

        // Delete files
        if ($resource->file_path && Storage::exists($resource->file_path)) {
            Storage::delete($resource->file_path);
        }
        if ($resource->thumbnail_path && Storage::exists($resource->thumbnail_path)) {
            Storage::delete($resource->thumbnail_path);
        }

        $resource->delete();

        return redirect()->route('library.manage')
            ->with('success', 'Resource berhasil dihapus!');
    }

    private function ensureAdministrator()
    {
        if (!Auth::check() || Auth::user()->role !== 'Administrator') {
            abort(403, 'Unauthorized action.');
        }
    }

    public function show($id)
    {
        $resource = LearningResource::findOrFail($id);

        $relatedResources = LearningResource::where('status', 'published')
            ->where('id', '!=', $id)
            ->where('subject', $resource->subject)
            ->limit(4)
            ->get();

        return view('learning-resources.show', compact('resource', 'relatedResources'));
    }

    public function preview($id)
    {
        $resource = LearningResource::findOrFail($id);

        return view('learning-resources.preview', compact('resource'));
    }

    public function previewFile($id)
    {
        $resource = LearningResource::findOrFail($id);

        $filePath = storage_path('app/' . $resource->file_path);

        if (!file_exists($filePath)) {
            // Generate dummy PDF for preview
            $pdfContent = $this->createSimplePdfContent($resource);
            return response($pdfContent, 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type');
        }

        $file = file_get_contents($filePath);
        $mimeType = $resource->file_mime ?: mime_content_type($filePath);

        return response($file, 200)
            ->header('Content-Type', $mimeType)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type');
    }

    public function download($id)
    {
        $resource = LearningResource::findOrFail($id);

        $filePath = storage_path('app/' . $resource->file_path);

        if (!file_exists($filePath)) {
            abort(404, 'File not found');
        }

        return response()->download($filePath, $resource->file_name);
    }

    private function createSimplePdfContent($resource)
    {
        $fontSize = 14;
        $lineHeight = 24;
        $startX = 60;
        $startY = 780;

        $lines = [
            $resource->title,
            '',
            'Subject: ' . ($resource->subject ?? 'N/A'),
            'Class Level: ' . ($resource->class_level ?? 'N/A'),
            'Author: ' . ($resource->author ?? $resource->publisher ?? 'N/A'),
            '',
            'Description:',
            $resource->description ?? 'No description available.',
            '',
            'Generated by BelajarCerdas LMS',
            'Downloaded: ' . now()->format('d M Y H:i:s'),
        ];

        $contentCommands = ["BT /F1 {$fontSize} Tf"];
        foreach ($lines as $line) {
            $safeLine = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $line);
            $y = $startY - (array_search($line, $lines) * $lineHeight);
            $contentCommands[] = "{$startX} {$y} Td ({$safeLine}) Tj";
        }
        $contentCommands[] = 'ET';

        $stream = implode("\n", $contentCommands);

        $objects = [
            1 => '<< /Type /Catalog /Pages 2 0 R >>',
            2 => '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            3 => '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>',
            4 => "<< /Length " . strlen($stream) . " >>\nstream\n{$stream}\nendstream",
            5 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [];

        foreach ($objects as $number => $content) {
            $offsets[$number] = strlen($pdf);
            $pdf .= "{$number} 0 obj\n{$content}\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";

        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= str_pad((string) $offsets[$i], 10, '0', STR_PAD_LEFT) . " 00000 n \n";
        }

        $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\n";
        $pdf .= "startxref\n{$xrefOffset}\n%%EOF";

        return $pdf;
    }
}
