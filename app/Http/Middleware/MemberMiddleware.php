<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class MemberMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Cek apakah user sudah login
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // 2. Cek apakah role user adalah 'member'
        if (Auth::user()->role === 'member') {
            // Jika ya, lanjutkan ke request
            return $next($request);
        }

        // 3. Jika bukan 'member', tolak akses
        abort(403, 'AKSES DITOLAK. ANDA BUKAN MEMBER.');
    }
}
