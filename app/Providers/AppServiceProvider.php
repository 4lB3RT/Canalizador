<?php

declare(strict_types=1);

namespace App\Providers;

use Canalizador\Shared\Header\Application\UseCases\GetHeader\GetHeader;
use Canalizador\Shared\Header\Domain\UserHeaderRepository;
use Canalizador\Shared\Header\Infrastructure\Repositories\Eloquent\EloquentUserHeaderRepository;
use Canalizador\Shared\HealthCheck\Application\UseCases\GetHealth\GetHealth;
use Canalizador\Shared\HealthCheck\Infrastructure\Probes\MysqlHealthProbe;
use Canalizador\Shared\HealthCheck\Infrastructure\Probes\RabbitMqHealthProbe;
use Canalizador\Shared\HealthCheck\Infrastructure\Probes\RedisHealthProbe;
use Canalizador\Shared\Profile\Application\UseCases\UpdateProfile\UpdateProfile;
use Canalizador\Shared\Profile\Domain\ProfileRepository;
use Canalizador\Shared\Profile\Infrastructure\Repositories\Eloquent\EloquentProfileRepository;
use Canalizador\Shared\Shared\Domain\Events\EventBus;
use Canalizador\Shared\Shared\Domain\Services\Clock;
use Canalizador\Shared\Shared\Domain\Services\HttpClient;
use Canalizador\Shared\Shared\Domain\Services\HttpResponseValidator;
use Canalizador\Shared\Shared\Domain\Services\PasswordHasher;
use Canalizador\Shared\Shared\Infrastructure\Events\EventHandlerRegistry;
use Canalizador\Shared\Shared\Infrastructure\Events\LaravelQueueEventBus;
use Canalizador\Shared\Shared\Infrastructure\Services\HttpErrorExtractor;
use Canalizador\Shared\Shared\Infrastructure\Services\HttpResponseValidator as HttpResponseValidatorImpl;
use Canalizador\Shared\Shared\Infrastructure\Services\LaravelHttpClient;
use Canalizador\Shared\Shared\Infrastructure\Services\LaravelPasswordHasher;
use Canalizador\Shared\Shared\Infrastructure\Services\SystemClock;
use Canalizador\YouTube\Channel\Domain\Repositories\ChannelRepository;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        ResetPassword::createUrlUsing(function ($user, string $token): string {
            $base  = rtrim(config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:5173')), '/');
            $email = urlencode($user->getEmailForPasswordReset());

            return "{$base}/reset-password?token={$token}&email={$email}";
        });
    }

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

        $this->app->bind(UserHeaderRepository::class, EloquentUserHeaderRepository::class);

        $this->app->bind(GetHeader::class, function ($app) {
            return new GetHeader(
                userHeaderRepository: $app->make(UserHeaderRepository::class),
                channelRepository:    $app->make(ChannelRepository::class),
            );
        });

        $this->app->bind(PasswordHasher::class, function ($app) {
            return new LaravelPasswordHasher(
                hasher: $app->make(Hasher::class),
            );
        });

        $this->app->bind(ProfileRepository::class, EloquentProfileRepository::class);

        $this->app->bind(UpdateProfile::class, function ($app) {
            return new UpdateProfile(
                profileRepository: $app->make(ProfileRepository::class),
                passwordHasher:    $app->make(PasswordHasher::class),
            );
        });

        $this->app->bind(GetHealth::class, function ($app) {
            return new GetHealth(
                probes: [
                    new MysqlHealthProbe(
                        connection: $app->make('db')->connection(),
                    ),
                    new RedisHealthProbe(
                        redis: $app->make('redis'),
                    ),
                    new RabbitMqHealthProbe(
                        host:     (string) config('queue.connections.rabbitmq.hosts.0.host', env('RABBITMQ_HOST', 'rabbitmq')),
                        port:     (int) config('queue.connections.rabbitmq.hosts.0.port', env('RABBITMQ_PORT', 5672)),
                        user:     (string) config('queue.connections.rabbitmq.hosts.0.user', env('RABBITMQ_USER', 'guest')),
                        password: (string) config('queue.connections.rabbitmq.hosts.0.password', env('RABBITMQ_PASSWORD', 'guest')),
                        vhost:    (string) config('queue.connections.rabbitmq.hosts.0.vhost', env('RABBITMQ_VHOST', '/')),
                    ),
                ],
                version: (string) (env('APP_VERSION') ?: 'dev'),
            );
        });
    }
}
