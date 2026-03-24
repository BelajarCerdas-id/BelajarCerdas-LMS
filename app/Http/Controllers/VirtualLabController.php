<?php

namespace App\Http\Controllers;

use App\Models\VirtualLab;
use App\Models\VirtualLabView;
use App\Models\VirtualLabReview;
use App\Models\Kurikulum;
use App\Models\Kelas;
use App\Models\Mapel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class VirtualLabController extends Controller
{
    /**
     * Display a listing of virtual labs for students
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $student = $user->StudentProfile;
        
        $subject = $request->get('subject');
        $kelasId = $request->get('kelas_id');
        $mapelId = $request->get('mapel_id');
        $search = $request->get('search');
        
        $query = VirtualLab::published()
            ->with(['Kurikulum', 'Kelas', 'Mapel', 'UserAccount']);
        
        // Filter by subject
        if ($subject) {
            $query->where('subject', $subject);
        }
        
        // Filter by kelas
        if ($kelasId) {
            $query->where('kelas_id', $kelasId);
        }
        
        // Filter by mapel
        if ($mapelId) {
            $query->where('mapel_id', $mapelId);
        }
        
        // Search
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%")
                  ->orWhere('experiment_type', 'LIKE', "%{$search}%");
            });
        }
        
        $virtualLabs = $query->latest()->paginate(12);
        
        // Get view progress for each lab
        if ($student) {
            foreach ($virtualLabs as $lab) {
                $lab->user_view = VirtualLabView::where('virtual_lab_id', $lab->id)
                    ->where('student_id', $student->id)
                    ->first();
            }
        }
        
        $subjects = ['IPA', 'Fisika', 'Kimia', 'Biologi'];
        $kurikulums = Kurikulum::all();
        $kelasList = Kelas::all();
        $mapels = Mapel::all();
        
        return view('virtual-labs.index', compact('virtualLabs', 'subjects', 'subject', 'kelasId', 'mapelId', 'search', 'kurikulums', 'kelasList', 'mapels'));
    }

    /**
     * Show virtual lab details with video preview
     */
    public function show($id)
    {
        $user = Auth::user();
        $student = $user->StudentProfile;
        
        $virtualLab = VirtualLab::published()
            ->with(['Kurikulum', 'Kelas', 'Mapel', 'Bab', 'SubBab', 'UserAccount', 'Reviews.Student'])
            ->findOrFail($id);
        
        // Get user's view progress
        $userView = null;
        if ($student) {
            $userView = VirtualLabView::where('virtual_lab_id', $id)
                ->where('student_id', $student->id)
                ->first();
        }
        
        // Get related virtual labs
        $relatedLabs = VirtualLab::published()
            ->where('subject', $virtualLab->subject)
            ->where('id', '!=', $virtualLab->id)
            ->where('kelas_id', $virtualLab->kelas_id)
            ->limit(4)
            ->get();
        
        // Calculate average rating
        $averageRating = $virtualLab->averageRating();
        
        return view('virtual-labs.show', compact('virtualLab', 'userView', 'relatedLabs', 'averageRating'));
    }

    /**
     * Preview virtual lab video (short preview)
     */
    public function preview($id)
    {
        $virtualLab = VirtualLab::findOrFail($id);
        
        // Check authorization - only published resources or owner/admin can preview
        if ($virtualLab->status !== 'published' && $virtualLab->user_id !== Auth::id()) {
            abort(403, 'Resource not published');
        }
        
        $filePath = storage_path('app/' . $virtualLab->video_path);
        
        if (!file_exists($filePath)) {
            abort(404, 'File not found');
        }
        
        $file = file_get_contents($filePath);
        $mimeType = $virtualLab->video_mime ?: mime_content_type($filePath);
        
        // Return video with range support for preview
        return response($file, 200)
            ->header('Content-Type', $mimeType)
            ->header('Accept-Ranges', 'bytes');
    }

    /**
     * Track video viewing progress
     */
    public function trackProgress(Request $request, $id)
    {
        $user = Auth::user();
        $student = $user->StudentProfile;
        
        if (!$student) {
            return response()->json(['error' => 'Student profile required'], 403);
        }
        
        $virtualLab = VirtualLab::findOrFail($id);
        
        $watchedDuration = (int) $request->input('watched_duration', 0);
        $lastPosition = (int) $request->input('last_position', 0);
        $isCompleted = $request->input('is_completed', false);
        
        // Update or create view record
        $view = VirtualLabView::updateOrCreate(
            [
                'virtual_lab_id' => $virtualLab->id,
                'student_id' => $student->id,
            ],
            [
                'user_id' => $user->id,
                'watched_duration_seconds' => $watchedDuration,
                'last_position_seconds' => $lastPosition,
                'is_completed' => $isCompleted,
                'completed_at' => $isCompleted ? now() : null,
            ]
        );
        
        return response()->json(['success' => true, 'view' => $view]);
    }

    /**
     * Submit review for virtual lab
     */
    public function submitReview(Request $request, $id)
    {
        $user = Auth::user();
        $student = $user->StudentProfile;
        
        if (!$student) {
            return redirect()->back()->with('error', 'Student profile required');
        }
        
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);
        
        $virtualLab = VirtualLab::findOrFail($id);
        
        // Check if already reviewed
        $existingReview = VirtualLabReview::where('virtual_lab_id', $id)
            ->where('student_id', $student->id)
            ->first();
        
        if ($existingReview) {
            $existingReview->update([
                'rating' => $request->rating,
                'comment' => $request->comment,
            ]);
        } else {
            VirtualLabReview::create([
                'virtual_lab_id' => $id,
                'student_id' => $student->id,
                'user_id' => $user->id,
                'rating' => $request->rating,
                'comment' => $request->comment,
                'is_helpful' => true,
            ]);
        }
        
        return redirect()->back()->with('success', 'Review submitted successfully');
    }

    /**
     * Manage virtual labs (Admin only)
     */
    public function manage(Request $request)
    {
        $this->ensureAdministrator();

        $search = $request->get('search');
        $subject = $request->get('subject');

        $query = VirtualLab::with(['Kurikulum', 'Kelas', 'Mapel', 'UserAccount']);

        if ($search) {
            $query->where('title', 'LIKE', "%{$search}%");
        }

        if ($subject) {
            $query->where('subject', $subject);
        }

        $labs = $query->latest()->paginate(20);

        return view('virtual-labs.manage', compact('labs', 'search', 'subject'));
    }

    /**
     * Show form to create new virtual lab
     */
    public function create()
    {
        $this->ensureAdministrator();

        $kurikulums = Kurikulum::all();
        $kelasList = Kelas::all();
        $mapels = Mapel::all();

        return view('virtual-labs.create', compact('kurikulums', 'kelasList', 'mapels'));
    }

    /**
     * Store new virtual lab
     */
    public function store(Request $request)
    {
        $this->ensureAdministrator();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'subject' => 'required|in:Fisika,Kimia,Biologi',
            'kurikulum_id' => 'required|exists:kurikulums,id',
            'kelas_id' => 'required|exists:kelas,id',
            'mapel_id' => 'required|exists:mapels,id',
            'video_url' => 'nullable|url',
            'thumbnail' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $thumbnailPath = null;
        if ($request->hasFile('thumbnail')) {
            $thumbnail = $request->file('thumbnail');
            $thumbnailName = 'thumb_' . time() . '_' . $thumbnail->getClientOriginalName();
            $thumbnailPath = $thumbnail->storeAs('public/virtual-labs/thumbnails', $thumbnailName);
        }

        VirtualLab::create([
            'user_id' => Auth::id(),
            'school_partner_id' => null,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'subject' => $validated['subject'],
            'kurikulum_id' => $validated['kurikulum_id'],
            'kelas_id' => $validated['kelas_id'],
            'mapel_id' => $validated['mapel_id'],
            'video_url' => $validated['video_url'],
            'thumbnail_path' => $thumbnailPath,
            'status' => 'published',
            'is_active' => true,
        ]);

        return redirect()->route('virtual-labs.manage')
            ->with('success', 'Virtual Lab berhasil ditambahkan!');
    }

    /**
     * Show form to edit virtual lab
     */
    public function edit($id)
    {
        $this->ensureAdministrator();

        $lab = VirtualLab::findOrFail($id);
        $kurikulums = Kurikulum::all();
        $kelasList = Kelas::all();
        $mapels = Mapel::all();

        return view('virtual-labs.edit', compact('lab', 'kurikulums', 'kelasList', 'mapels'));
    }

    /**
     * Update virtual lab
     */
    public function update(Request $request, $id)
    {
        $this->ensureAdministrator();

        $lab = VirtualLab::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'subject' => 'required|in:Fisika,Kimia,Biologi',
            'kurikulum_id' => 'required|exists:kurikulums,id',
            'kelas_id' => 'required|exists:kelas,id',
            'mapel_id' => 'required|exists:mapels,id',
            'video_url' => 'nullable|url',
            'thumbnail' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('thumbnail')) {
            if ($lab->thumbnail_path && Storage::exists($lab->thumbnail_path)) {
                Storage::delete($lab->thumbnail_path);
            }

            $thumbnail = $request->file('thumbnail');
            $thumbnailName = 'thumb_' . time() . '_' . $thumbnail->getClientOriginalName();
            $lab->thumbnail_path = $thumbnail->storeAs('public/virtual-labs/thumbnails', $thumbnailName);
        }

        $lab->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'subject' => $validated['subject'],
            'kurikulum_id' => $validated['kurikulum_id'],
            'kelas_id' => $validated['kelas_id'],
            'mapel_id' => $validated['mapel_id'],
            'video_url' => $validated['video_url'],
        ]);

        return redirect()->route('virtual-labs.manage')
            ->with('success', 'Virtual Lab berhasil diupdate!');
    }

    /**
     * Delete virtual lab
     */
    public function destroy($id)
    {
        $this->ensureAdministrator();

        $lab = VirtualLab::findOrFail($id);

        if ($lab->thumbnail_path && Storage::exists($lab->thumbnail_path)) {
            Storage::delete($lab->thumbnail_path);
        }

        $lab->delete();

        return redirect()->route('virtual-labs.manage')
            ->with('success', 'Virtual Lab berhasil dihapus!');
    }

    private function ensureAdministrator()
    {
        if (!Auth::check() || Auth::user()->role !== 'Administrator') {
            abort(403, 'Unauthorized action.');
        }
    }
}
