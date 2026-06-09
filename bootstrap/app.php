<?php

use App\Http\Middleware\EnsureSuperadmin;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // $middleware->api(prepend: [
        //     EnsureFrontendRequestsAreStateful::class,
        // ]);

        $middleware->alias([
            'superadmin' => EnsureSuperadmin::class,
        ]);

        $middleware->throttleApi();
    })
    ->booting(function (): void {
        // Set storage path SEBELUM app booted (lebih aman di sini)
        if (env('VERCEL_JOB_ID') || env('NOW_REGION')) {
            app()->useStoragePath('/tmp/storage');
        }

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(100)->by($request->ip());
        });
        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });
        RateLimiter::for('contact', function (Request $request) {
            return Limit::perHour(3)->by($request->ip());
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();