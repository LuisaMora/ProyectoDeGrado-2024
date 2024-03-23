<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EsPropietario
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        $auth = Auth::guard('api')->check();
        $instance = $user instanceof \App\Models\Usuario;
        if ($auth && $user instanceof \App\Models\Usuario && $user->esPropietario()) {
            return $next($request);
        }
        return  response()->json(['error' => $auth], 403);
    }
}
