<?php

declare(strict_types=1);

namespace App\Providers;

use Canalizador\VideoProduction\Shared\Domain\Events\EventBus;
use Canalizador\VideoProduction\Shared\Domain\Services\Clock;
use Canalizador\VideoProduction\Shared\Domain\Services\HttpClient;
use Canalizador\VideoProduction\Shared\Domain\Services\HttpResponseValidator;
use Canalizador\VideoProduction\Shared\Infrastructure\Events\EventHandlerRegistry;
use Canalizador\VideoProduction\Shared\Infrastructure\Events\LaravelQueueEventBus;
use Canalizador\VideoProduction\Shared\Infrastructure\Services\HttpErrorExtractor;
use Canalizador\VideoProduction\Shared\Infrastructure\Services\HttpResponseValidator as HttpResponseValidatorImpl;
use Canalizador\VideoProduction\Shared\Infrastructure\Services\LaravelHttpClient;
use Canalizador\VideoProduction\Shared\Infrastructure\Services\SystemClock;
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
