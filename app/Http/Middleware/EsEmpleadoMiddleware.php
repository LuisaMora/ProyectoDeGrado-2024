<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EsEmpleadoMiddleware
{
    /** 
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = auth()->user();
        // parse roles a int ['cocinero', 'cajero', 'mesero'] donde mesro seria rol 1, cajero 2 y cocinero 3
        $roles = array_map(function ($rol) {
            $rolMinusc = strtolower($rol);
            return $rolMinusc === 'mesero' ? 1 : ($rolMinusc === 'cajero' ? 2 : 3);
        }, $roles); 

        if ($user instanceof \App\Models\User && $user->esEmpleado()) {
            if (!empty($roles)) {
                $tipoEmpleado = (int) $user->getTipoEmpleado();
                if(in_array($tipoEmpleado, $roles))
                    return $next($request);
            }
        }
        return response()->json([
            'message' => 'No tienes permiso para acceder a esta ruta',
        ], 403);
    }
}
