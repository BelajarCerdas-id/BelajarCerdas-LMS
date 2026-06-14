<?php

namespace App\Http\Controllers;

use App\Models\AcademicCalendar;
use App\Models\SchoolPartner;
use App\Models\YayasanProfile;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class YayasanController extends Controller
{
    private function getYayasan(?int $yayasanId = null)
    {
        $user = Auth::user();
        if (! $user || $user->role !== 'Yayasan') {
            abort(403, 'Akses hanya untuk role Yayasan.');
        }

        $profile = YayasanProfile::where('user_id', $user->id)->first();
        if (! $profile) {
            abort(403, 'Profil Yayasan tidak ditemukan.');
        }

        $yayasan = $profile->Yayasan;
        if (! $yayasan) {
            abort(403, 'Yayasan tidak ditemukan.');
        }

        if ($yayasanId !== null && (int) $yayasan->id !== (int) $yayasanId) {
            abort(403, 'Anda tidak memiliki akses ke yayasan ini.');
        }

        return $yayasan;
    }

    public function dashboard($yayasanId)
    {
        $yayasan = $this->getYayasan((int) $yayasanId);
        $sekolah = SchoolPartner::where('yayasan_id', $yayasan->id)
            ->with('UserAccount')
            ->orderBy('nama_sekolah')
            ->get();
        $schoolIds = $sekolah->pluck('id');

        $studentCounts = DB::table('student_profiles')
            ->join('user_accounts', 'student_profiles.user_id', '=', 'user_accounts.id')
            ->whereIn('student_profiles.school_partner_id', $schoolIds)
            ->where('user_accounts.status_akun', 'aktif')
            ->select('student_profiles.school_partner_id', DB::raw('count(*) as total'))
            ->groupBy('student_profiles.school_partner_id')
            ->pluck('total', 'school_partner_id');

        $staffCounts = DB::table('school_staff_profiles')
            ->join('user_accounts', 'school_staff_profiles.user_id', '=', 'user_accounts.id')
            ->whereIn('school_staff_profiles.school_partner_id', $schoolIds)
            ->where('user_accounts.status_akun', 'aktif')
            ->select('school_staff_profiles.school_partner_id', DB::raw('count(*) as total'))
            ->groupBy('school_staff_profiles.school_partner_id')
            ->pluck('total', 'school_partner_id');

        $revenueBySchool = DB::table('transactions')
            ->whereIn('school_partner_id', $schoolIds)
            ->where('transaction_status', 'Berhasil')
            ->select('school_partner_id', DB::raw('sum(price) as total'))
            ->groupBy('school_partner_id')
            ->pluck('total', 'school_partner_id');

        $totalSekolah = $sekolah->count();
        $totalSiswa = (int) $studentCounts->sum();
        $totalTenagaPendidik = (int) $staffCounts->sum();
        $totalPendapatan = (int) $revenueBySchool->sum();
        $sekolahDenganKepsek = $sekolah->whereNotNull('kepsek_id')->count();
        $sekolahTanpaKepsek = max(0, $totalSekolah - $sekolahDenganKepsek);

        $agendaBulanIni = 0;
        $totalPengumuman = 0;
        $pengumumanDibaca = 0;

        if ($schoolIds->isNotEmpty()) {
            $agendaBulanIni = AcademicCalendar::whereIn('school_partner_id', $schoolIds)
                ->whereBetween('date', [now()->startOfMonth(), now()->endOfMonth()])
                ->count();

            $announcements = DB::table('announcements')
                ->leftJoin('school_partners', 'announcements.school_partner_id', '=', 'school_partners.id')
                ->leftJoin('announcement_views', function ($join) {
                    $join->on('announcement_views.announcement_id', '=', 'announcements.id')
                        ->on('announcement_views.user_id', '=', 'school_partners.kepsek_id');
                })
                ->where('announcements.yayasan_id', $yayasan->id)
                ->whereIn('announcements.school_partner_id', $schoolIds)
                ->where('announcements.author_role', 'Yayasan')
                ->select('announcements.id', 'announcement_views.created_at as read_at')
                ->get();

            $totalPengumuman = $announcements->count();
            $pengumumanDibaca = $announcements->whereNotNull('read_at')->count();
        }

        $sekolah = $sekolah->map(function ($school) use ($studentCounts, $staffCounts, $revenueBySchool) {
            $school->total_siswa = (int) ($studentCounts[$school->id] ?? 0);
            $school->total_staff = (int) ($staffCounts[$school->id] ?? 0);
            $school->total_pendapatan = (int) ($revenueBySchool[$school->id] ?? 0);

            return $school;
        })->sortByDesc('total_siswa')->values();

        return view('features.lms.yayasan.dashboard', compact(
            'yayasan',
            'totalSekolah',
            'totalSiswa',
            'totalTenagaPendidik',
            'totalPendapatan',
            'sekolahDenganKepsek',
            'sekolahTanpaKepsek',
            'agendaBulanIni',
            'totalPengumuman',
            'pengumumanDibaca',
            'sekolah'
        ));
    }

    public function schools($yayasanId)
    {
        $yayasan = $this->getYayasan((int) $yayasanId);
        $sekolahList = SchoolPartner::where('yayasan_id', $yayasan->id)->get();

        return view('features.lms.yayasan.schools', compact('yayasan', 'sekolahList'));
    }

    public function calendar(Request $request, $yayasanId)
    {
        $yayasan = $this->getYayasan((int) $yayasanId);
        $sekolahList = SchoolPartner::where('yayasan_id', $yayasan->id)
            ->orderBy('nama_sekolah')
            ->get();

        $schoolIds = $sekolahList->pluck('id');
        $selectedSchoolId = $request->query('school_id');
        if ($selectedSchoolId && ! $schoolIds->contains((int) $selectedSchoolId)) {
            $selectedSchoolId = null;
        }

        try {
            $month = Carbon::createFromFormat('Y-m-d', ($request->query('month') ?: now()->format('Y-m')).'-01')->startOfMonth();
        } catch (\Throwable) {
            $month = now()->startOfMonth();
        }

        $events = collect();
        if ($schoolIds->isNotEmpty()) {
            $events = AcademicCalendar::query()
                ->leftJoin('school_partners', 'academic_calendars.school_partner_id', '=', 'school_partners.id')
                ->whereIn('academic_calendars.school_partner_id', $schoolIds)
                ->when($selectedSchoolId, fn ($query) => $query->where('academic_calendars.school_partner_id', $selectedSchoolId))
                ->whereBetween('academic_calendars.date', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                ->orderBy('academic_calendars.date')
                ->orderBy('school_partners.nama_sekolah')
                ->get([
                    'academic_calendars.id',
                    'academic_calendars.school_partner_id',
                    'academic_calendars.date',
                    'academic_calendars.title',
                    'academic_calendars.type',
                    'academic_calendars.color',
                    'academic_calendars.status',
                    'school_partners.nama_sekolah',
                    'school_partners.jenjang_sekolah',
                ]);
        }

        $eventsByDate = $events->groupBy(fn ($event) => Carbon::parse($event->date)->format('Y-m-d'));
        $summaryBySchool = $events->groupBy('school_partner_id')->map->count();
        $calendarStartPadding = $month->copy()->startOfMonth()->dayOfWeek;
        $daysInMonth = $month->daysInMonth;
        $prevMonth = $month->copy()->subMonth()->format('Y-m');
        $nextMonth = $month->copy()->addMonth()->format('Y-m');

        return view('features.lms.yayasan.calendar', compact(
            'yayasan',
            'sekolahList',
            'selectedSchoolId',
            'month',
            'events',
            'eventsByDate',
            'summaryBySchool',
            'calendarStartPadding',
            'daysInMonth',
            'prevMonth',
            'nextMonth'
        ));
    }

    public function announcements($yayasanId)
    {
        $yayasan = $this->getYayasan((int) $yayasanId);
        $sekolahList = SchoolPartner::where('yayasan_id', $yayasan->id)
            ->orderBy('nama_sekolah')
            ->get();

        $schoolIds = $sekolahList->pluck('id');
        $announcements = collect();
        if ($schoolIds->isNotEmpty()) {
            $announcements = DB::table('announcements')
                ->leftJoin('school_partners', 'announcements.school_partner_id', '=', 'school_partners.id')
                ->leftJoin('announcement_views', function ($join) {
                    $join->on('announcement_views.announcement_id', '=', 'announcements.id')
                        ->on('announcement_views.user_id', '=', 'school_partners.kepsek_id');
                })
                ->where('announcements.yayasan_id', $yayasan->id)
                ->whereIn('announcements.school_partner_id', $schoolIds)
                ->where('announcements.author_role', 'Yayasan')
                ->where('announcements.target', 'Kepala Sekolah')
                ->select(
                    'announcements.*',
                    'school_partners.nama_sekolah',
                    'school_partners.kepsek_id',
                    'announcement_views.created_at as read_at'
                )
                ->orderByDesc('announcements.created_at')
                ->get();
        }

        $totalSent = $announcements->count();
        $totalRead = $announcements->whereNotNull('read_at')->count();

        return view('features.lms.yayasan.announcements', compact(
            'yayasan',
            'sekolahList',
            'announcements',
            'totalSent',
            'totalRead'
        ));
    }

    public function storeAnnouncement(Request $request, $yayasanId)
    {
        $yayasan = $this->getYayasan((int) $yayasanId);

        $validated = $request->validate([
            'school_ids' => 'required|array|min:1',
            'school_ids.*' => 'integer',
            'title' => 'required|string|max:255',
            'type' => 'required|in:info,penting',
            'content' => 'required|string',
        ]);

        $schools = SchoolPartner::where('yayasan_id', $yayasan->id)
            ->whereIn('id', $validated['school_ids'])
            ->whereNotNull('kepsek_id')
            ->get();

        if ($schools->isEmpty()) {
            return back()->with('error', 'Tidak ada sekolah valid dengan kepala sekolah untuk dikirimi pengumuman.');
        }

        $payload = $schools->map(fn ($school) => [
            'yayasan_id' => $yayasan->id,
            'school_partner_id' => $school->id,
            'author_id' => Auth::id(),
            'author_role' => 'Yayasan',
            'target' => 'Kepala Sekolah',
            'title' => $validated['title'],
            'type' => $validated['type'],
            'content' => $validated['content'],
            'created_at' => now(),
            'updated_at' => now(),
        ])->all();

        DB::table('announcements')->insert($payload);

        return redirect()->route('yayasan.announcements', $yayasan->id)
            ->with('success', 'Pengumuman berhasil dikirim ke '.$schools->count().' kepala sekolah.');
    }

    public function people(Request $request, $yayasanId)
    {
        $yayasan = $this->getYayasan((int) $yayasanId);
        $sekolahList = SchoolPartner::where('yayasan_id', $yayasan->id)
            ->orderBy('nama_sekolah')
            ->get();

        $schoolIds = $sekolahList->pluck('id');
        $selectedSchoolId = $request->query('school_id');
        if ($selectedSchoolId && ! $schoolIds->contains((int) $selectedSchoolId)) {
            $selectedSchoolId = null;
        }

        $tahunAjaranList = DB::table('school_classes')
            ->whereIn('school_partner_id', $schoolIds)
            ->when($selectedSchoolId, fn ($query) => $query->where('school_partner_id', $selectedSchoolId))
            ->whereNotNull('tahun_ajaran')
            ->distinct()
            ->orderByDesc('tahun_ajaran')
            ->pluck('tahun_ajaran');

        $selectedYear = $request->query('tahun_ajaran');

        $classes = DB::table('school_classes')
            ->join('school_partners', 'school_classes.school_partner_id', '=', 'school_partners.id')
            ->whereIn('school_classes.school_partner_id', $schoolIds)
            ->when($selectedSchoolId, fn ($query) => $query->where('school_classes.school_partner_id', $selectedSchoolId))
            ->when($selectedYear, fn ($query) => $query->where('school_classes.tahun_ajaran', $selectedYear))
            ->select('school_classes.*', 'school_partners.nama_sekolah')
            ->orderBy('school_partners.nama_sekolah')
            ->orderBy('school_classes.class_name')
            ->get();

        $selectedClassId = $request->query('class_id');
        if ($selectedClassId && ! $classes->pluck('id')->contains((int) $selectedClassId)) {
            $selectedClassId = null;
        }

        $guru = DB::table('school_staff_profiles')
            ->join('user_accounts', 'school_staff_profiles.user_id', '=', 'user_accounts.id')
            ->join('school_partners', 'school_staff_profiles.school_partner_id', '=', 'school_partners.id')
            ->whereIn('school_staff_profiles.school_partner_id', $schoolIds)
            ->when($selectedSchoolId, fn ($query) => $query->where('school_staff_profiles.school_partner_id', $selectedSchoolId))
            ->where('user_accounts.role', 'Guru')
            ->select(
                'school_staff_profiles.nama_lengkap',
                'school_staff_profiles.nik',
                'school_staff_profiles.personal_email',
                'user_accounts.email',
                'user_accounts.status_akun',
                'school_partners.nama_sekolah'
            )
            ->orderBy('school_partners.nama_sekolah')
            ->orderBy('school_staff_profiles.nama_lengkap')
            ->get();

        $siswa = DB::table('student_profiles')
            ->join('user_accounts', 'student_profiles.user_id', '=', 'user_accounts.id')
            ->join('school_partners', 'student_profiles.school_partner_id', '=', 'school_partners.id')
            ->leftJoin('student_school_classes', function ($join) {
                $join->on('student_profiles.user_id', '=', 'student_school_classes.student_id')
                    ->where('student_school_classes.student_class_status', '=', 'active');
            })
            ->leftJoin('school_classes', 'student_school_classes.school_class_id', '=', 'school_classes.id')
            ->leftJoin('parent_profiles as parent_by_user', 'student_profiles.user_id', '=', 'parent_by_user.student_id')
            ->leftJoin('parent_profiles as parent_by_profile', 'student_profiles.id', '=', 'parent_by_profile.student_id')
            ->whereIn('student_profiles.school_partner_id', $schoolIds)
            ->when($selectedSchoolId, fn ($query) => $query->where('student_profiles.school_partner_id', $selectedSchoolId))
            ->when($selectedYear, fn ($query) => $query->where('school_classes.tahun_ajaran', $selectedYear))
            ->when($selectedClassId, fn ($query) => $query->where('school_classes.id', $selectedClassId))
            ->where('user_accounts.status_akun', 'aktif')
            ->select(
                'student_profiles.nama_lengkap as nama_siswa',
                'student_profiles.personal_email',
                'user_accounts.email as akun_siswa',
                'school_classes.class_name',
                'school_classes.tahun_ajaran',
                'school_partners.nama_sekolah',
                DB::raw('COALESCE(parent_by_user.nama_lengkap, parent_by_profile.nama_lengkap) as nama_orang_tua'),
                DB::raw('COALESCE(parent_by_user.pekerjaan, parent_by_profile.pekerjaan) as pekerjaan_orang_tua')
            )
            ->orderBy('school_partners.nama_sekolah')
            ->orderBy('school_classes.class_name')
            ->orderBy('student_profiles.nama_lengkap')
            ->get();

        return view('features.lms.yayasan.people', compact(
            'yayasan',
            'sekolahList',
            'tahunAjaranList',
            'classes',
            'guru',
            'siswa',
            'selectedSchoolId',
            'selectedYear',
            'selectedClassId'
        ));
    }

    public function schoolEdit($yayasanId, $schoolId)
    {
        $yayasan = $this->getYayasan((int) $yayasanId);
        $sekolah = SchoolPartner::where('yayasan_id', $yayasan->id)->findOrFail($schoolId);

        return view('features.lms.yayasan.school-edit', compact('yayasan', 'sekolah'));
    }

    public function schoolUpdate(Request $request, $yayasanId, $schoolId)
    {
        $yayasan = $this->getYayasan((int) $yayasanId);
        $sekolah = SchoolPartner::where('yayasan_id', $yayasan->id)->findOrFail($schoolId);

        $request->validate([
            'nama_sekolah' => 'required|string|max:255',
            'npsn' => 'required|string|max:20',
            'jenjang_sekolah' => 'required|string|max:10',
        ]);

        $sekolah->update($request->only(['nama_sekolah', 'npsn', 'jenjang_sekolah']));

        return redirect()->route('yayasan.schools', $yayasan->id)
            ->with('success', 'Data sekolah berhasil diperbarui.');
    }
}
