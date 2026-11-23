<?php

namespace App\Http\Controllers;

use App\Models\UserAccount;
use App\Services\Proctor\ProctorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

/**
 * Sudo: an administrator may open a session as any user, and return with one click.
 */
class SudoController extends Controller
{
    public const SESSION_KEY = 'sudo_original_id';

    public function start(Request $request, $role, int $userId)
    {
        if (!ProctorService::hasRole(Auth::user(), ProctorService::APPROVER_ADMIN_ROLES)) {
            abort(403);
        }
        if (Session::has(self::SESSION_KEY)) {
            return back()->with('error', 'Kamu sudah masuk sebagai pengguna lain.');
        }

        $target = UserAccount::findOrFail($userId);
        if ((int) $target->id === (int) Auth::id()) {
            return back()->with('error', 'Kamu sudah menggunakan akun itu.');
        }

        Session::put(self::SESSION_KEY, Auth::id());
        Auth::login($target);
        Log::info('sudo started', ['admin' => Session::get(self::SESSION_KEY), 'target' => $target->id, 'ip' => $request->ip()]);

        return redirect('/lms/'.$target->role);
    }

    public function leave()
    {
        $original = Session::pull(self::SESSION_KEY);
        if (!$original || !($admin = UserAccount::find($original))) {
            // session lost (e.g. expired): the current user is the only truth left
            return redirect('/login');
        }

        Log::info('sudo ended', ['admin' => $admin->id]);
        Auth::login($admin);

        return redirect('/lms/'.$admin->role);
    }
}
