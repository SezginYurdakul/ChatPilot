<?php

namespace App\Providers;

use App\Models\Message;
use App\Observers\MessageObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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
        // Register model observers
        Message::observe(MessageObserver::class);

        // Protect admin login endpoint from brute-force attempts.
        RateLimiter::for('admin-login', function (Request $request) {
            $email = strtolower((string) $request->input('email'));
            $key = ($email !== '' ? $email : 'guest').'|'.$request->ip();

            return Limit::perMinute(5)->by($key);
        });
    }
}
