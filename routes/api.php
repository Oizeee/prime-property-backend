<?php

use App\Http\Controllers\Api\AgentAuthController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\PropertyController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Prime Property API Routes
|--------------------------------------------------------------------------
|
| Global throttle: 100 req/min/IP (registered as "api" in bootstrap/app.php)
| Auth endpoints: 10 req/min/IP ("auth" limiter)
| Contact form: 3 submits/hour/IP ("contact" limiter)
|
*/

Route::middleware('throttle:auth')->group(function () {
  Route::post('/agent/login', [AgentAuthController::class, 'login']);
});

Route::middleware('auth:sanctum')->group(function () {
  Route::get('/me', function (\Illuminate\Http\Request $request) {
    return response()->json([
      'user' => [
        'id' => $request->user()->id,
        'name' => $request->user()->name,
        'email' => $request->user()->email,
        'role' => $request->user()->role,
      ],
    ]);
  });
  Route::post('/agent/logout', [AgentAuthController::class, 'logout']);
});

Route::get('/properties', [PropertyController::class, 'index']);

Route::middleware(['auth:sanctum', 'superadmin'])->group(function () {
  Route::post('/properties', [PropertyController::class, 'store']);
  Route::put('/properties/{id}', [PropertyController::class, 'update']);
  Route::delete('/properties/{id}', [PropertyController::class, 'destroy']);
});

Route::post('/contact', [ContactController::class, 'store'])
  ->middleware('throttle:contact');
