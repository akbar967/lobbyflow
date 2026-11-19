<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return redirect()->route('login');
        }

        if (! $request->user()->isAdmin()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Akses Terbatas: Hanya Administrator yang diizinkan mengakses halaman Laporan.'], 403);
            }

            return redirect()->route('visitors.index')->with('error', 'Akses Ditolak! Akun Resepsionis hanya memiliki hak akses operasional check-in. Halaman Laporan & Analisis AI khusus untuk Administrator.');
        }

        return $next($request);
    }
}
