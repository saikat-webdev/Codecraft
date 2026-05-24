<?php

use App\Services\AvatarStyleService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('activate:avatars {mode}', function (string $mode) {
    $service = app(AvatarStyleService::class);
    $service->setMode($mode);
    $this->info(sprintf('Avatar style mode set to "%s".', $mode));
})->purpose('Activate a named avatar mode: default or superb');

Artisan::command('activate:superb-avatars', function () {
    app(AvatarStyleService::class)->setMode('superb');
    $this->info('Superb avatar styles are now active.');
})->purpose('Activate the superb hero & cartoon avatar set');

Artisan::command('activate:default-avatars', function () {
    app(AvatarStyleService::class)->setMode('default');
    $this->info('Default avatar styles are now active.');
})->purpose('Restore the default avatar style set');

Artisan::command('avatar:mode', function () {
    $service = app(AvatarStyleService::class);
    $this->info('Current avatar mode: ' . $service->getMode());
})->purpose('Display the active avatar style mode');
