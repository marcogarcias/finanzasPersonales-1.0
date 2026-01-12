<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckLicense
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->license_expires_at) {
            if (now()->greaterThan($user->license_expires_at)) {
                // Opcional: Podrías permitir el acceso a ciertas áreas, 
                // pero aquí restringimos todo según tu requerimiento.
                auth()->logout();
                
                return redirect()->route('login')->withErrors([
                    'license' => 'Tu licencia ha expirado el ' . $user->license_expires_at->format('d/m/Y') . '. Por favor renueva tu suscripción.',
                ]);
            }
        }

        return $next($request);
    }
}
