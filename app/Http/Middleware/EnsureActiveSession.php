<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveSession
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        $session = $request->session();
        $passwordHash = (string) $user->getAuthPassword();

        // A different user means the guard was switched, so the previous
        // fingerprint no longer belongs to this session.
        if ((int) $session->get('auth_user_id') !== (int) $user->id) {
            $session->put('auth_user_id', $user->id);
            $session->put('auth_password_hash', $passwordHash);

            return $next($request);
        }

        $isSessionValid = hash_equals((string) $session->get('auth_password_hash'), $passwordHash);

        if ($user->is_active && $isSessionValid) {
            return $next($request);
        }

        Auth::logout();
        $session->invalidate();
        $session->regenerateToken();

        return redirect()
            ->route('login')
            ->with('status', 'Sesi berakhir. Silakan masuk kembali.');
    }
}
