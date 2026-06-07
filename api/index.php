<?php

// 1. Load autoload bawaan composer
require __DIR__ . '/../vendor/autoload.php';

// 2. Load instansi bootstrap Laravel 11 asli
$app = require_once __DIR__ . '/../bootstrap/app.php';

// 3. Kunci path folder public secara paksa untuk Vercel Serverless
$app->usePublicPath(__DIR__ . '/../public');

// 4. Jalankan kernel HTTP untuk memproses request masuk
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$response->send();
$kernel->terminate($request, $response);