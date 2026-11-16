<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Super Admin tidak boleh mengelola master data, bank soal, manajemen ujian,
 * penilaian, maupun publikasi hasil. Route yang dilindungi middleware ini
 * akan mengembalikan 403 untuk role super_admin.
 */
class DenySuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->hasRole('super_admin')) {
            abort(403, 'Akses ditolak. Super Admin tidak memiliki akses ke fitur ini.');
        }

        return $next($request);
    }
}
