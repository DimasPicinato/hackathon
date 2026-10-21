<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AulaController;
use App\Http\Controllers\ChamadaController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\Rh\CertificadosController;
use App\Http\Controllers\Rh\ConfiguracoesController;
use App\Http\Controllers\Rh\JustificativasController;
use App\Http\Controllers\Rh\RelatoriosController;
use App\Http\Controllers\Rh\ReservasController;
use App\Http\Controllers\Rh\TreinamentosController;
use App\Http\Controllers\Rh\TicketsController;
use App\Http\Controllers\Rh\UsuariosController;
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

        // RF10 — abrir ticket (qualquer pessoa logada; RH resolve depois)
        Route::post('/portal/tickets', [PortalController::class, 'abrirTicket'])->name('portal.ticket.abrir');

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

        // ─── Gestão — RH / Líder / Admin (RF05, RF08–RF13, RF18–RF20, RF26, RF28) ─
        Route::prefix('portal/rh')->name('portal.rh.')->group(function () {
            Route::get('/reservas', [ReservasController::class, 'index'])->name('reservas');
            Route::post('/reservas', [ReservasController::class, 'reservar'])->name('reservas.reservar');
            Route::post('/reservas/{inscricao}/cancelar', [ReservasController::class, 'cancelar'])
                ->name('reservas.cancelar');
            Route::get('/tickets', [TicketsController::class, 'index'])->name('tickets');
            Route::post('/tickets/{ticket}/resolver', [TicketsController::class, 'resolver'])
                ->name('tickets.resolver');

            Route::get('/justificativas', [JustificativasController::class, 'index'])->name('justificativas');
            Route::post('/justificativas', [JustificativasController::class, 'registrar'])
                ->name('justificativas.registrar'); // RF13 — direta por líder/RH
            Route::post('/justificativas/{justificativa}/status', [JustificativasController::class, 'alterarStatus'])
                ->name('justificativas.status');

            Route::get('/certificados', [CertificadosController::class, 'index'])->name('certificados');
            Route::post('/certificados/emitir', [CertificadosController::class, 'emitir'])->name('certificados.emitir');
            Route::post('/certificados/{certificado}/reemitir', [CertificadosController::class, 'reemitir'])
                ->name('certificados.reemitir');

            Route::get('/relatorios', [RelatoriosController::class, 'index'])->name('relatorios');
            Route::get('/relatorios/pessoas/csv', [RelatoriosController::class, 'csvPessoas'])->name('relatorios.pessoas');
            Route::get('/relatorios/sessoes/{aula}/csv', [RelatoriosController::class, 'csvSessao'])->name('relatorios.sessao');

            // RF07 — catalogo de treinamentos e aulas (RH/Admin; líder no próprio setor)
            Route::get('/treinamentos', [TreinamentosController::class, 'index'])->name('treinamentos');
            Route::post('/treinamentos', [TreinamentosController::class, 'criar'])->name('treinamentos.criar');
            Route::patch('/treinamentos/{treinamento}', [TreinamentosController::class, 'atualizar'])->name('treinamentos.atualizar');
            Route::post('/treinamentos/{treinamento}/aulas', [TreinamentosController::class, 'criarAula'])->name('treinamentos.aula.criar');
            Route::patch('/aulas/{aula}', [TreinamentosController::class, 'atualizarAula'])->name('treinamentos.aula.atualizar');

            // RF05 — CRUD de usuários (RH/Admin; líder: só senha dos liderados)
            Route::get('/usuarios', [UsuariosController::class, 'index'])->name('usuarios');
            Route::post('/usuarios', [UsuariosController::class, 'criar'])->name('usuarios.criar');
            Route::patch('/usuarios/{usuario}', [UsuariosController::class, 'atualizar'])->name('usuarios.atualizar');
            Route::post('/usuarios/{usuario}/alternar-ativo', [UsuariosController::class, 'alternarAtivo'])->name('usuarios.alternar');
            Route::post('/usuarios/{usuario}/senha', [UsuariosController::class, 'redefinirSenha'])->name('usuarios.senha');

            // RF26 — configurações (Admin/RH)
            Route::get('/configuracoes', [ConfiguracoesController::class, 'index'])->name('configuracoes');
            Route::post('/configuracoes/motivos', [ConfiguracoesController::class, 'criarMotivo'])->name('configuracoes.motivo.criar');
            Route::post('/configuracoes/motivos/{motivo}/alternar', [ConfiguracoesController::class, 'alternarMotivo'])->name('configuracoes.motivo.alternar');
            Route::post('/configuracoes/tipos/{tipo}/alternar', [ConfiguracoesController::class, 'alternarTipo'])->name('configuracoes.tipo.alternar');
            Route::post('/configuracoes/termo/publicar', [ConfiguracoesController::class, 'publicarTermo'])->name('configuracoes.termo.publicar');
        });
    });
});

require __DIR__.'/auth.php';
