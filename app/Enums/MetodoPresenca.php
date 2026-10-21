<?php

namespace App\Enums;

// RF14–RF17 — como a presença foi validada
enum MetodoPresenca: string
{
    case Facial = 'facial';
    case Manual = 'manual';
}
