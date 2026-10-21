<?php

namespace App\Enums;

// RF12 — status alterável a qualquer momento por RH/Líder
enum StatusJustificativa: string
{
    case Pendente = 'pendente';
    case Aprovado = 'aprovado';
    case Recusado = 'recusado';
}
