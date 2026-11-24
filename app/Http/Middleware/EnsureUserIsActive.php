<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && ! $request->user()->is_active) {
            if ($request->is('api/*') || $request->expectsJson()) {
                $token = $request->user()->currentAccessToken();
                if ($token && method_exists($token, 'delete')) {
                    $token->delete();
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Akun Anda sedang dinonaktifkan. Hubungi Super Admin RW.',
                    'errors' => null,
                ], 403);
            }

            Auth::logout();

            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            $loginUrl = Route::has('login') ? route('login') : '/login';

            return redirect($loginUrl)->withErrors([
                'username' => 'Akun Anda sedang dinonaktifkan. Hubungi Super Admin RW.',
            ]);
        }

        return $next($request);
    }
}
