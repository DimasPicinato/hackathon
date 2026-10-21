<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// RF25 — lembretes rodando por conta própria no serviço `scheduler` (homologação)
Schedule::command('presenza:lembretes')->hourly();
