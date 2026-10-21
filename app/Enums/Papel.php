<?php

namespace App\Enums;

// RF02 — papéis acumuláveis na mesma conta
enum Papel: string
{
    case Colaborador = 'colaborador';
    case Lider = 'lider';
    case Instrutor = 'instrutor';
    case Rh = 'rh';
    case Admin = 'admin';
}
