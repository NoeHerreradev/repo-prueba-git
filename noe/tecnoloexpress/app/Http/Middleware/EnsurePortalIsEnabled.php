<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Corta el acceso al portal público cuando está en mantenimiento, dejando
 * pasar al administrador para que pueda revisarlo antes de reabrirlo.
 */
class EnsurePortalIsEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Setting::bool('portal_enabled') || auth()->user()?->isAdmin()) {
            return $next($request);
        }

        return response()->view('portal.maintenance', [], 503);
    }
}
