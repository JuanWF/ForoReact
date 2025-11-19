<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/**
 * Programar tareas automáticas
 * 
 * EXPLICACIÓN:
 * - Schedule permite ejecutar comandos periódicamente
 * - En producción, necesitas configurar un cron job:
 *   * * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
 */
Schedule::command('trends:update')
    ->hourly() // Ejecutar cada hora
    ->withoutOverlapping() // No ejecutar si ya hay una instancia corriendo
    ->onSuccess(function () {
        // Log opcional cuando se complete
    });
