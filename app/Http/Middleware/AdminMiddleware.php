<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
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

        // 2. Cek apakah role user adalah 'admin'
        // (Asumsi Anda memiliki kolom 'role' di tabel 'users' Anda)
        if (Auth::user()->role === 'admin') {
            // Jika ya, lanjutkan ke request berikutnya (Controller)
            return $next($request);
        }

        // 3. Jika bukan 'admin', tolak akses
        abort(403, 'AKSES DITOLAK. ANDA BUKAN ADMIN.');
    }
}