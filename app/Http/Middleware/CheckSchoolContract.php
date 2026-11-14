<?php

namespace App\Http\Middleware;

use App\Services\SchoolContract\SchoolContractService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckSchoolContract
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        $result = app(SchoolContractService::class)->validate($user);

        if (!$result['success']) {

            Auth::logout();

            return redirect()->route('login')->with('failed', $result['message']);
        }

        return $next($request);
    }
}