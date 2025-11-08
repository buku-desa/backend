<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::exec('cmd', ['/c', base_path('storage/scripts/backup_pg.bat')])
    ->dailyAt('12:00')
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping()
    ->onOneServer()
    ->evenInMaintenanceMode()
    ->skip(fn() => ! env('BACKUP_ENABLED', true))
    ->sendOutputTo(storage_path('logs/backup.log'));