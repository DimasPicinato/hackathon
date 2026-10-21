<?php

namespace App\Enums;

// RF21 — resultado da verificação pública
enum ResultadoVerificacao: string
{
    case Valido = 'valido';
    case Expirado = 'expirado';
    case NaoEncontrado = 'nao_encontrado';
}
