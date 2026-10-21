<?php

namespace App\Enums;

// RF06 — janela da aula controlada pelo instrutor
enum StatusAula: string
{
    case Agendada = 'agendada';
    case EmAndamento = 'em_andamento';
    case Encerrada = 'encerrada';
}
