<?php

namespace App\Enums;

// RF08/RF09 — vaga nominal com fila de espera automática
enum StatusInscricao: string
{
    case Reservada = 'reservada';
    case ListaEspera = 'lista_espera';
    case Cancelada = 'cancelada';
}
