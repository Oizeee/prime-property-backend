<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$app->usePublicPath(__DIR__ . '/../public');

$request = Illuminate\Http\Request::capture();
$request->overrideGlobals();

if (str_contains($request->getRequestUri(), '/api/')) {
    $_SERVER['REQUEST_URI'] = $request->getRequestUri();
}

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($request);

$response->send();
$kernel->terminate($request, $response);