<?php

declare(strict_types=1);

namespace App\Providers;

use Canalizador\Shared\Shared\Domain\Events\EventBus;
use Canalizador\Shared\Shared\Domain\Services\Clock;
use Canalizador\Shared\Shared\Domain\Services\HttpClient;
use Canalizador\Shared\Shared\Domain\Services\HttpResponseValidator;
use Canalizador\Shared\Shared\Infrastructure\Events\EventHandlerRegistry;
use Canalizador\Shared\Shared\Infrastructure\Events\LaravelQueueEventBus;
use Canalizador\Shared\Shared\Infrastructure\Services\HttpErrorExtractor;
use Canalizador\Shared\Shared\Infrastructure\Services\HttpResponseValidator as HttpResponseValidatorImpl;
use Canalizador\Shared\Shared\Infrastructure\Services\LaravelHttpClient;
use Canalizador\Shared\Shared\Infrastructure\Services\SystemClock;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(Clock::class, SystemClock::class);
        $this->app->bind(HttpClient::class, LaravelHttpClient::class);

        $this->app->bind(HttpResponseValidator::class, function ($app) {
            return new HttpResponseValidatorImpl(
                errorExtractor: new HttpErrorExtractor()
            );
        });

        $this->app->bind(EventBus::class, LaravelQueueEventBus::class);
        $this->app->singleton(EventHandlerRegistry::class, function ($app) {
            return new EventHandlerRegistry($app);
        });
    }
}
