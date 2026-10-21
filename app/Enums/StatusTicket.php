<?php

namespace App\Enums;

// RF10/RF12
enum StatusTicket: string
{
    case Aberto = 'aberto';
    case Resolvido = 'resolvido';
}
