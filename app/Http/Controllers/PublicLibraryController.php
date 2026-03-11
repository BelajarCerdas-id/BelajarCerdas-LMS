<?php

namespace App\Http\Controllers;

use App\Models\PublicLibrary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PublicLibraryController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $items = PublicLibrary::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($filter) use ($search) {
                    $filter->where('title', 'like', "%{$search}%")
                        ->orWhere('publisher', 'like', "%{$search}%")
                        ->orWhere('subject', 'like', "%{$search}%")
                        ->orWhere('class_level', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('public-library.index', compact('items', 'search'));
    }

    public function download(int $id)
    {
        $item = PublicLibrary::findOrFail($id);

        $fullPath = public_path($item->file_path);

        if (!file_exists($fullPath)) {
            abort(404, 'File tidak ditemukan.');
        }

        return response()->download($fullPath, $item->original_file_name);
    }

    public function manage(Request $request)
    {
        $this->ensureAdministrator();

        $search = trim((string) $request->query('search', ''));

        $items = PublicLibrary::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($filter) use ($search) {
                    $filter->where('title', 'like', "%{$search}%")
                        ->orWhere('publisher', 'like', "%{$search}%")
                        ->orWhere('subject', 'like', "%{$search}%")
                        ->orWhere('class_level', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('public-library.manage', compact('items', 'search'));
    }

    public function store(Request $request)
    {
        $this->ensureAdministrator();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'class_level' => 'required|string|max:100',
            'description' => 'nullable|string|max:2000',
            'thumbnail' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
            'file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,mp4,mov,avi,mkv,webm,txt,zip,rar|max:102400',
        ], [
            'title.required' => 'Judul materi wajib diisi.',
            'subject.required' => 'Mata pelajaran wajib diisi.',
            'class_level.required' => 'Kelas wajib diisi.',
            'description.max' => 'Deskripsi maksimal 2000 karakter.',
            'thumbnail.required' => 'Thumbnail wajib diunggah.',
            'thumbnail.image' => 'Thumbnail harus berupa gambar.',
            'thumbnail.mimes' => 'Format thumbnail harus jpg, jpeg, png, atau webp.',
            'thumbnail.max' => 'Ukuran thumbnail maksimal 2MB.',
            'file.required' => 'File materi wajib diunggah.',
            'file.mimes' => 'Format file belum didukung.',
            'file.max' => 'Ukuran file maksimal 100MB.',
        ]);

        $uploadedFile = $request->file('file');
        $fileMeta = $this->extractUploadedFileMeta($uploadedFile);

        $thumbnailPath = $this->storeUploadedFile($request->file('thumbnail'), 'uploads/public-library/thumbnails');
        $filePath = $this->storeUploadedFile($uploadedFile, 'uploads/public-library/files');

        PublicLibrary::create([
            'user_id' => Auth::id(),
            'title' => $validated['title'],
            'publisher' => $this->resolveAuthorName(),
            'subject' => $validated['subject'],
            'class_level' => $validated['class_level'],
            'description' => $this->normalizeDescription($validated['description'] ?? null),
            'thumbnail_path' => $thumbnailPath,
            'file_path' => $filePath,
            'original_file_name' => $fileMeta['original_file_name'],
            'file_extension' => $fileMeta['file_extension'],
            'file_mime' => $fileMeta['file_mime'],
            'file_size' => $fileMeta['file_size'],
        ]);

        return redirect()->route('public-library.manage')->with('success', 'Materi berhasil ditambahkan.');
    }

    public function update(Request $request, int $id)
    {
        $this->ensureAdministrator();

        $item = PublicLibrary::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'class_level' => 'required|string|max:100',
            'description' => 'nullable|string|max:2000',
            'thumbnail' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'file' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,mp4,mov,avi,mkv,webm,txt,zip,rar|max:102400',
        ], [
            'title.required' => 'Judul materi wajib diisi.',
            'subject.required' => 'Mata pelajaran wajib diisi.',
            'class_level.required' => 'Kelas wajib diisi.',
            'description.max' => 'Deskripsi maksimal 2000 karakter.',
            'thumbnail.image' => 'Thumbnail harus berupa gambar.',
            'thumbnail.mimes' => 'Format thumbnail harus jpg, jpeg, png, atau webp.',
            'thumbnail.max' => 'Ukuran thumbnail maksimal 2MB.',
            'file.mimes' => 'Format file belum didukung.',
            'file.max' => 'Ukuran file maksimal 100MB.',
        ]);

        $payload = [
            'title' => $validated['title'],
            'publisher' => $this->resolveAuthorName(),
            'subject' => $validated['subject'],
            'class_level' => $validated['class_level'],
            'description' => $this->normalizeDescription($validated['description'] ?? null),
        ];

        if ($request->hasFile('thumbnail')) {
            $this->deleteFileIfExists($item->thumbnail_path);
            $payload['thumbnail_path'] = $this->storeUploadedFile($request->file('thumbnail'), 'uploads/public-library/thumbnails');
        }

        if ($request->hasFile('file')) {
            $uploadedFile = $request->file('file');
            $fileMeta = $this->extractUploadedFileMeta($uploadedFile);

            $this->deleteFileIfExists($item->file_path);
            $payload['file_path'] = $this->storeUploadedFile($uploadedFile, 'uploads/public-library/files');
            $payload['original_file_name'] = $fileMeta['original_file_name'];
            $payload['file_extension'] = $fileMeta['file_extension'];
            $payload['file_mime'] = $fileMeta['file_mime'];
            $payload['file_size'] = $fileMeta['file_size'];
        }

        $item->update($payload);

        return redirect()->route('public-library.manage')->with('success', 'Materi berhasil diperbarui.');
    }

    public function destroy(int $id)
    {
        $this->ensureAdministrator();

        $item = PublicLibrary::findOrFail($id);

        $this->deleteFileIfExists($item->thumbnail_path);
        $this->deleteFileIfExists($item->file_path);

        $item->delete();

        return redirect()->route('public-library.manage')->with('success', 'Materi berhasil dihapus.');
    }

    private function ensureAdministrator(): void
    {
        if (!Auth::check() || !$this->isAdminRole(Auth::user()->role ?? null)) {
            abort(403, 'Akses hanya untuk administrator.');
        }
    }

    private function isAdminRole(?string $role): bool
    {
        $normalizedRole = strtolower(trim((string) $role));

        return in_array($normalizedRole, ['administrator', 'admin'], true);
    }

    private function resolveAuthorName(): string
    {
        $user = Auth::user();

        if (!$user) {
            return 'Unknown Author';
        }

        $name = trim((string) (
            $user->OfficeProfile?->nama_lengkap
            ?? $user->SchoolStaffProfile?->nama_lengkap
            ?? $user->StudentProfile?->nama_lengkap
            ?? ''
        ));

        if ($name !== '') {
            return $name;
        }

        $email = trim((string) ($user->email ?? ''));

        if ($email !== '') {
            return $email;
        }

        return 'Unknown Author';
    }

    private function storeUploadedFile(UploadedFile $file, string $relativeDirectory): string
    {
        $relativeDirectory = trim($relativeDirectory, '/');
        $directory = public_path($relativeDirectory);

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $name = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $file->move($directory, $name);

        return $relativeDirectory . '/' . $name;
    }

    private function extractUploadedFileMeta(UploadedFile $file): array
    {
        return [
            'original_file_name' => basename((string) $file->getClientOriginalName()),
            'file_extension' => strtolower((string) $file->getClientOriginalExtension()),
            'file_mime' => $file->getClientMimeType(),
            'file_size' => (int) ($file->getSize() ?? 0),
        ];
    }

    private function deleteFileIfExists(?string $relativePath): void
    {
        if (!$relativePath) {
            return;
        }

        $fullPath = public_path($relativePath);

        if (file_exists($fullPath)) {
            @unlink($fullPath);
        }
    }

    private function normalizeDescription(?string $description): ?string
    {
        $normalized = trim((string) $description);

        return $normalized !== '' ? $normalized : null;
    }
}
