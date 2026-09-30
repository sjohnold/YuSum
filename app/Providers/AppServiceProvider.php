<?php

namespace App\Providers;

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

    public function boot(): void
    {
        if ($this->app->environment('production')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
        \Illuminate\Support\Facades\Event::listen(
            \Laravel\Paddle\Events\WebhookReceived::class,
            \App\Listeners\HandlePaddleWebhook::class
        );

        // Admin Gate for Horizon
        \Illuminate\Support\Facades\Gate::define('viewHorizon', function ($user) {
            return in_array($user->email, [
                'admin@example.com', // Замените на ваш email
            ]);
        });
    }
}
