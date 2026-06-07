<?php

namespace App\Providers;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Serialize all JSON timestamps in Asia/Jakarta (WIB)
        $serialize = static function ($date): string {
            return Carbon::parse($date)
                ->timezone('Asia/Jakarta')
                ->format('Y-m-d\TH:i:sP');
        };

        Carbon::serializeUsing($serialize);
        Date::serializeUsing($serialize);
    }
}
