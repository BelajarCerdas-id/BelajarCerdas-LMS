<?php

namespace App\Http\Controllers;

use App\Models\UserAccount;
use App\Services\SchoolContract\SchoolContractService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    // function login view
    public function loginView()
    {
        return view('auth.login');
    }

    // function login
    public function login(Request $request)
    {
        // VALIDASI
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|regex:/^[a-zA-z0-9._%+-]+@belajarcerdas\.id$/',
            'password' => 'required',
        ], [
            'email.required' => 'Email harus diisi.',
            'email.email' => 'Format email harus @belajarcerdas.id.',
            'email.regex' => 'Format email harus @belajarcerdas.id.',
            'password.required' => 'Password harus diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Cari user berdasarkan email
        $user = UserAccount::where('email', $request->email)->first();

        // 3. Email atau password salah
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'invalidCredentials' => true,
                'message' => 'Email atau password salah.',
            ], 422);
        }

        // Cek status akun
        if ($user->status_akun !== 'aktif') {
            return response()->json([
                'status' => 'error',
                'isAccountInactive' => true,
                'message' => 'Akun kamu telah dinonaktifkan, silahkan hubungi pihak yang bertanggung jawab.',
            ], 422);
        }

        // Cek Kontrak Sekolah
        $result = app(SchoolContractService::class)->validate($user);

        if (!$result['success']) {
            return response()->json([
                'status' => 'error',
                'contractExpired' => true,
                'message' => $result['message'],
            ], 403);
        }

        // Login User
        Auth::login($user);

        // Regenerate session setelah login
        $request->session()->regenerate();

        // Redirect
        if (in_array($user->role, ['Administrator', 'Finance'])) {
            return response()->json([
                'status' => 'success',
                'redirect' => route('lms.office.dashboard.view', [
                    'role' => $user->role,
                ]),
            ]);
        }

        return response()->json([
            'status' => 'success',
            'redirect' => route('beranda', [
                'role' => $user->role,
            ]),
        ]);
    }

    // function logout
    public function logout(Request $request)
    {
        Auth::logout(); // Ini akan meng-logout user secara resmi dari sistem auth

        $request->session()->invalidate(); // menghapus semua sesi lama
        $request->session()->regenerateToken(); // mencegah CSRF reuse dari sesi lama

        return redirect('/');
    }
}
