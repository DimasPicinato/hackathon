<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AulaController;
use App\Http\Controllers\ChamadaController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\TermoController;
use App\Http\Controllers\VerificacaoPublicaController;
use App\Models\Verificacao;
use Illuminate\Support\Facades\Route;

// ─── Público ───────────────────────────────────────────────────────────────────
Route::get('/', fn () => view('welcome'));

// RF21 — verificação pública (sem login): código/QR/link
Route::get('/verificar', [VerificacaoPublicaController::class, 'busca'])->name('verificar.busca');
Route::get('/verificar/{codigo}', [VerificacaoPublicaController::class, 'mostrar'])->name('verificar.mostrar');
Route::get('/verificar/{codigo}/pdf', [VerificacaoPublicaController::class, 'pdf'])->name('verificar.pdf');

// ─── Autenticado: termo LGPD (RF04) antes de qualquer portal ──────────────────
Route::middleware('auth')->group(function () {
    Route::get('/portal/termo', [TermoController::class, 'mostrar'])->name('termo.mostrar');
    Route::post('/portal/termo', [TermoController::class, 'aceitar'])->name('termo.aceitar');

    Route::middleware('termo')->group(function () {
        Route::get('/portal', [PortalController::class, 'home'])->name('portal');
        Route::get('/dashboard', fn () => redirect()->route('portal'))->name('dashboard'); // compat Breeze/reset

        // RF14.a — presença facial autônoma do colaborador
        Route::post('/portal/presenca/facial', [ChamadaController::class, 'facialAutonoma'])
            ->name('portal.presenca.facial');

        // RF06/RF14.b/RF15 — chamada conduzida pelo instrutor
        Route::get('/portal/aulas/{aula}', [ChamadaController::class, 'aula'])->name('portal.aula.mostrar');
        Route::post('/portal/aulas/{aula}/iniciar', [AulaController::class, 'iniciar'])->name('portal.aula.iniciar');
        Route::post('/portal/aulas/{aula}/encerrar', [AulaController::class, 'encerrar'])->name('portal.aula.encerrar');
        Route::post('/portal/aulas/{aula}/presenca/facial', [ChamadaController::class, 'facialViaInstrutor'])
            ->name('portal.aula.presenca.facial');
        Route::post('/portal/aulas/{aula}/presenca/manual', [ChamadaController::class, 'manual'])
            ->name('portal.aula.presenca.manual');

        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });
});

require __DIR__.'/auth.php';
