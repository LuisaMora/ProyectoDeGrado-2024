<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EsAdministrador
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        $user = auth()->user();
        if (auth()->check() && $user instanceof \App\Models\Usuario && $user->esAdministrador()) {
            return $next($request);
        }

        return response()->json(['error' => 'Acceso no autorizado'], 403);
    }
}
