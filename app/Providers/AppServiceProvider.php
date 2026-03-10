<?php

namespace App\Providers;

use App\Models\Message;
use App\Observers\MessageObserver;
use App\Services\AlertingService;
use App\Support\OperationalMetrics;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
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

        Queue::after(function (JobProcessed $event): void {
            OperationalMetrics::increment('queue_processed');
        });

        Queue::failing(function (JobFailed $event): void {
            OperationalMetrics::increment('queue_failed');

            $context = [
                'connection' => $event->connectionName,
                'queue' => $event->job->getQueue(),
                'job_name' => $event->job->resolveName(),
                'error' => $event->exception->getMessage(),
            ];

            Log::critical('Queue job failed', $context);

            if (app()->bound('sentry')) {
                app('sentry')->captureException($event->exception);
            }

            app(AlertingService::class)->send('Queue job failed', $context);
        });
    }
}
