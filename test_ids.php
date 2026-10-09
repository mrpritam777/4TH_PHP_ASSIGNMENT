<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

foreach (['bridge', 'road', 'building', 'hospital'] as $t) {
    $ids = App\Models\Infrastructure::whereHas('type', fn ($q) => $q->where('slug', $t))->pluck('id')->all();
    echo $t . ': ' . implode(',', $ids) . PHP_EOL;
}
echo 'maintenance ids: ' . App\Models\MaintenanceRequest::pluck('id')->implode(',') . PHP_EOL;
echo 'request numbers: ' . App\Models\MaintenanceRequest::pluck('request_number')->take(3)->implode(',') . PHP_EOL;
