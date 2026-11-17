<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // App atrás do proxy (TLS termina no proxy) e do compose interno: confiar em
        // X-Forwarded-* APENAS de IPs de rede privada docker (172.16.0.0/12) faz o
        // Laravel gerar URLs https:// (QR RF21, e-mails) e cookies secure — e evita
        // que um cliente público forje X-Forwarded-For para poluir auditorias de IP.
        // Em local (artisan serve, sem proxy) nada muda; ajuste via TRUSTED_PROXIES.
        $middleware->trustProxies(at: (string) env('TRUSTED_PROXIES', '172.16.0.0/12'));

        $middleware->alias([
            'termo' => \App\Http\Middleware\ExigirAceiteTermo::class, // RF04
        ]);

        // RF29 — chat público anônimo: sem token CSRF (o widget aparece também em páginas
        // sem sessão). Proteção: validação de tamanho + zero acesso a dados do sistema
        // (RNF09: sem rate limit, decisão registrada do time).
        $middleware->validateCsrfTokens(except: [
            'assistente',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
