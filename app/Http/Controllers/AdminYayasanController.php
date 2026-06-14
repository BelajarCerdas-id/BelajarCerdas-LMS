<?php

namespace App\Http\Controllers;

use App\Models\SchoolPartner;
use App\Models\UserAccount;
use App\Models\Yayasan;
use App\Models\YayasanProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminYayasanController extends Controller
{
    public function index()
    {
        $this->ensureAdministrator();

        $yayasans = Yayasan::with(['YayasanProfiles.UserAccount', 'SchoolPartners'])
            ->withCount('SchoolPartners')
            ->latest()
            ->get();

        return view('features.lms.administrator.yayasan.index', compact('yayasans'));
    }

    public function create()
    {
        $this->ensureAdministrator();

        $schools = SchoolPartner::with('Yayasan')->orderBy('nama_sekolah')->get();
        $headmasterAccounts = $this->headmasterAccounts();

        return view('features.lms.administrator.yayasan.form', [
            'yayasan' => null,
            'profile' => null,
            'userAccount' => null,
            'schools' => $schools,
            'headmasterAccounts' => $headmasterAccounts,
            'selectedSchoolIds' => [],
        ]);
    }

    public function store(Request $request)
    {
        $this->ensureAdministrator();

        $validated = $request->validate([
            'nama_yayasan' => ['required', 'string', 'max:255'],
            'npwp' => ['nullable', 'string', 'max:50'],
            'alamat' => ['nullable', 'string'],
            'kontak' => ['nullable', 'string', 'max:50'],
            'email_yayasan' => ['nullable', 'email', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:user_accounts,email'],
            'no_hp' => ['required', 'string', 'max:30', 'unique:user_accounts,no_hp'],
            'password' => ['required', 'string', 'min:8'],
            'status_akun' => ['required', Rule::in(['aktif', 'non-aktif'])],
            'school_ids' => ['nullable', 'array'],
            'school_ids.*' => ['integer', 'exists:school_partners,id'],
            'new_schools' => ['nullable', 'array'],
            'new_schools.*.nama_sekolah' => ['nullable', 'string', 'max:255'],
            'new_schools.*.npsn' => ['nullable', 'string', 'max:50'],
            'new_schools.*.jenjang_sekolah' => ['nullable', Rule::in(['SD', 'MI', 'SMP', 'MTS', 'SMA', 'SMK', 'MA', 'MAK'])],
            'new_schools.*.kepsek_id' => ['nullable', 'integer', 'exists:user_accounts,id'],
        ]);

        DB::transaction(function () use ($request, $validated) {
            $yayasan = Yayasan::create([
                'nama_yayasan' => $validated['nama_yayasan'],
                'npwp' => $validated['npwp'] ?? null,
                'alamat' => $validated['alamat'] ?? null,
                'kontak' => $validated['kontak'] ?? null,
                'email' => $validated['email_yayasan'] ?? null,
                'logo' => $this->storeLogo($request),
            ]);

            $user = UserAccount::create([
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'no_hp' => $validated['no_hp'],
                'role' => 'Yayasan',
                'status_akun' => $validated['status_akun'],
            ]);

            YayasanProfile::create([
                'user_id' => $user->id,
                'yayasan_id' => $yayasan->id,
                'nama_lengkap' => $validated['nama_lengkap'],
            ]);

            $this->syncSchools($yayasan, $validated['school_ids'] ?? []);
            $this->createNewSchools($yayasan, $validated['new_schools'] ?? []);
        });

        return redirect()->route('admin.yayasan.index')->with('success', 'Yayasan berhasil ditambahkan.');
    }

    public function edit($yayasanId)
    {
        $this->ensureAdministrator();

        $yayasan = Yayasan::with(['YayasanProfiles.UserAccount', 'SchoolPartners'])->findOrFail($yayasanId);
        $profile = $yayasan->YayasanProfiles->first();
        $userAccount = $profile?->UserAccount;
        $schools = SchoolPartner::with('Yayasan')->orderBy('nama_sekolah')->get();
        $headmasterAccounts = $this->headmasterAccounts();
        $selectedSchoolIds = $yayasan->SchoolPartners->pluck('id')->all();

        return view('features.lms.administrator.yayasan.form', compact(
            'yayasan',
            'profile',
            'userAccount',
            'schools',
            'headmasterAccounts',
            'selectedSchoolIds'
        ));
    }

    public function update(Request $request, $yayasanId)
    {
        $this->ensureAdministrator();

        $yayasan = Yayasan::with('YayasanProfiles.UserAccount')->findOrFail($yayasanId);
        $profile = $yayasan->YayasanProfiles->first();
        $userAccount = $profile?->UserAccount;

        $validated = $request->validate([
            'nama_yayasan' => ['required', 'string', 'max:255'],
            'npwp' => ['nullable', 'string', 'max:50'],
            'alamat' => ['nullable', 'string'],
            'kontak' => ['nullable', 'string', 'max:50'],
            'email_yayasan' => ['nullable', 'email', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('user_accounts', 'email')->ignore($userAccount?->id)],
            'no_hp' => ['required', 'string', 'max:30', Rule::unique('user_accounts', 'no_hp')->ignore($userAccount?->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'status_akun' => ['required', Rule::in(['aktif', 'non-aktif'])],
            'school_ids' => ['nullable', 'array'],
            'school_ids.*' => ['integer', 'exists:school_partners,id'],
            'new_schools' => ['nullable', 'array'],
            'new_schools.*.nama_sekolah' => ['nullable', 'string', 'max:255'],
            'new_schools.*.npsn' => ['nullable', 'string', 'max:50'],
            'new_schools.*.jenjang_sekolah' => ['nullable', Rule::in(['SD', 'MI', 'SMP', 'MTS', 'SMA', 'SMK', 'MA', 'MAK'])],
            'new_schools.*.kepsek_id' => ['nullable', 'integer', 'exists:user_accounts,id'],
        ]);

        DB::transaction(function () use ($request, $validated, $yayasan, $profile, $userAccount) {
            $logo = $this->storeLogo($request, $yayasan->logo);

            $yayasan->update([
                'nama_yayasan' => $validated['nama_yayasan'],
                'npwp' => $validated['npwp'] ?? null,
                'alamat' => $validated['alamat'] ?? null,
                'kontak' => $validated['kontak'] ?? null,
                'email' => $validated['email_yayasan'] ?? null,
                'logo' => $logo ?? $yayasan->logo,
            ]);

            if (! $userAccount) {
                $userAccount = UserAccount::create([
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password'] ?? 'password123'),
                    'no_hp' => $validated['no_hp'],
                    'role' => 'Yayasan',
                    'status_akun' => $validated['status_akun'],
                ]);
            } else {
                $userData = [
                    'email' => $validated['email'],
                    'no_hp' => $validated['no_hp'],
                    'role' => 'Yayasan',
                    'status_akun' => $validated['status_akun'],
                ];

                if (! empty($validated['password'])) {
                    $userData['password'] = Hash::make($validated['password']);
                }

                $userAccount->update($userData);
            }

            if ($profile) {
                $profile->update([
                    'user_id' => $userAccount->id,
                    'nama_lengkap' => $validated['nama_lengkap'],
                ]);
            } else {
                YayasanProfile::create([
                    'user_id' => $userAccount->id,
                    'yayasan_id' => $yayasan->id,
                    'nama_lengkap' => $validated['nama_lengkap'],
                ]);
            }

            $this->syncSchools($yayasan, $validated['school_ids'] ?? []);
            $this->createNewSchools($yayasan, $validated['new_schools'] ?? []);
        });

        return redirect()->route('admin.yayasan.index')->with('success', 'Yayasan berhasil diperbarui.');
    }

    public function destroy($yayasanId)
    {
        $this->ensureAdministrator();

        $yayasan = Yayasan::with('YayasanProfiles.UserAccount')->findOrFail($yayasanId);

        DB::transaction(function () use ($yayasan) {
            SchoolPartner::where('yayasan_id', $yayasan->id)->update(['yayasan_id' => null]);

            foreach ($yayasan->YayasanProfiles as $profile) {
                $profile->UserAccount?->delete();
                $profile->delete();
            }

            if (! empty($yayasan->logo) && file_exists(public_path($yayasan->logo))) {
                unlink(public_path($yayasan->logo));
            }

            $yayasan->delete();
        });

        return redirect()->route('admin.yayasan.index')->with('success', 'Yayasan berhasil dihapus.');
    }

    private function syncSchools(Yayasan $yayasan, array $schoolIds): void
    {
        SchoolPartner::where('yayasan_id', $yayasan->id)
            ->whereNotIn('id', $schoolIds)
            ->update(['yayasan_id' => null]);

        if (! empty($schoolIds)) {
            SchoolPartner::whereIn('id', $schoolIds)->update(['yayasan_id' => $yayasan->id]);
        }
    }

    private function createNewSchools(Yayasan $yayasan, array $schools): void
    {
        foreach ($schools as $school) {
            $hasInput = filled($school['nama_sekolah'] ?? null)
                || filled($school['npsn'] ?? null)
                || filled($school['jenjang_sekolah'] ?? null)
                || filled($school['kepsek_id'] ?? null);

            if (! $hasInput) {
                continue;
            }

            validator($school, [
                'nama_sekolah' => ['required', 'string', 'max:255'],
                'npsn' => ['required', 'string', 'max:50'],
                'jenjang_sekolah' => ['required', Rule::in(['SD', 'MI', 'SMP', 'MTS', 'SMA', 'SMK', 'MA', 'MAK'])],
                'kepsek_id' => ['required', 'integer', 'exists:user_accounts,id'],
            ])->validate();

            SchoolPartner::create([
                'yayasan_id' => $yayasan->id,
                'nama_sekolah' => $school['nama_sekolah'],
                'npsn' => $school['npsn'],
                'jenjang_sekolah' => $school['jenjang_sekolah'],
                'kepsek_id' => $school['kepsek_id'],
            ]);
        }
    }

    private function headmasterAccounts()
    {
        return UserAccount::whereIn('role', ['Kepala Sekolah', 'Wakil Kepala Sekolah', 'Guru'])
            ->orderBy('email')
            ->get();
    }

    private function ensureAdministrator(): void
    {
        if (Auth::user()?->role !== 'Administrator') {
            abort(403, 'Akses hanya untuk Administrator.');
        }
    }

    private function storeLogo(Request $request, ?string $oldLogo = null): ?string
    {
        if (! $request->hasFile('logo')) {
            return null;
        }

        $destinationPath = public_path('yayasan-logo');
        if (! file_exists($destinationPath)) {
            mkdir($destinationPath, 0775, true);
        }

        if (! empty($oldLogo) && file_exists(public_path($oldLogo))) {
            unlink(public_path($oldLogo));
        }

        $file = $request->file('logo');
        $filename = 'yayasan_'.time().'.'.$file->getClientOriginalExtension();
        $file->move($destinationPath, $filename);

        return 'yayasan-logo/'.$filename;
    }
}
