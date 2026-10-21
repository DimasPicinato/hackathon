<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// RF04 — sem aceite do termo LGPD ativo, nenhuma tela do portal abre
// (sem aceite: presença apenas por homologação manual (RF04).)
class ExigirAceiteTermo
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        if (auth()->user()->aceitouTermoVigente() || $request->routeIs('termo.*')) {
            return $next($request);
        }

        return redirect()->route('termo.mostrar');
    }
}
