<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$request = Illuminate\Http\Request::create('/', 'GET');
$response = $app->handle($request);

echo "Status: " . $response->getStatusCode() . PHP_EOL;
echo "Content: " . substr($response->getContent(), 0, 3000) . PHP_EOL;
