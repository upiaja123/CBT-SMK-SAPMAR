<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tolak akses (403) untuk role tertentu. Pemakaian: ->middleware('deny.role:kurikulum')
 */
class DenyRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if ($request->user()?->hasAnyRole($roles)) {
            abort(403, 'Akses ditolak. Role Anda tidak memiliki akses ke fitur ini.');
        }

        return $next($request);
    }
}
