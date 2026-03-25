<?php

use Canalizador\VideoProduction\VideoLegacy\Infrastructure\Commands\CanalizadorAgentCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('canalizador:agent', function () {
    $this->call(CanalizadorAgentCommand::class);
});
