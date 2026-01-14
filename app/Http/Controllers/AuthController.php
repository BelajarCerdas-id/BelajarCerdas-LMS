<?php

namespace App\Http\Controllers;

use App\Models\UserAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // 1. VALIDATOR (untuk AJAX)
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|regex:/^[a-zA-z0-9._%+-]+@belajarcerdas\.id$/',
            'password' => 'required',
        ], [
            'email.required' => 'Email harus diisi.',
            'email.email' => 'Format email harus @belajarcerdas.id.',
            'email.regex' => 'Format email harus @belajarcerdas.id.',
            'password' => 'Password harus diisi.',
        ]);

        // 2. JIKA VALIDASI GAGAL → RETURN JSON
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        // 3. CREDENTIALS HARUS ARRAY
        $credentials = [
            'email' => $request->email,
            'password' => $request->password,
        ];

        // 4. CARI USER
        $user = UserAccount::where('email', $request->email)->first();

        // 5. CEK STATUS AKUN
        if ($user && $user->status_akun !== 'aktif') {
            return response()->json([
                'status' => 'error',
                'isAccountInactive' => true,
                'message' => 'Akun kamu telah dinonaktifkan, silahkan hubungi pihak yang bertanggung jawab.'
            ], 422);
        }

        // 6. ATTEMPT LOGIN
        if (!Auth::attempt($credentials)) {
            return response()->json([
                'status' => 'error',
                'invalidCredentials' => true,
                'message' => 'Email atau password salah.'
            ], 422);
        }

        // 7. REGENERATE SESSION
        $request->session()->regenerate();

        // 8. SUCCESS
        return response()->json([
            'status' => true,
            'redirect' => url('/beranda')
        ]);
    }
}
