<?php

declare(strict_types=1);

namespace App\Providers;

use Helmreel\Shared\Header\Application\UseCases\GetHeader\GetHeader;
use Helmreel\Shared\Header\Domain\UserHeaderRepository;
use Helmreel\Shared\Header\Infrastructure\Repositories\Eloquent\EloquentUserHeaderRepository;
use Helmreel\Shared\HealthCheck\Application\UseCases\GetHealth\GetHealth;
use Helmreel\Shared\HealthCheck\Infrastructure\Probes\MysqlHealthProbe;
use Helmreel\Shared\HealthCheck\Infrastructure\Probes\RabbitMqHealthProbe;
use Helmreel\Shared\HealthCheck\Infrastructure\Probes\RedisHealthProbe;
use Helmreel\Shared\Profile\Application\UseCases\UpdateProfile\UpdateProfile;
use Helmreel\Shared\Profile\Domain\ProfileRepository;
use Helmreel\Shared\Profile\Infrastructure\Repositories\Eloquent\EloquentProfileRepository;
use Helmreel\Shared\Shared\Domain\Events\EventBus;
use Helmreel\Shared\Shared\Domain\Services\Clock;
use Helmreel\Shared\Shared\Domain\Services\HttpClient;
use Helmreel\Shared\Shared\Domain\Services\HttpResponseValidator;
use Helmreel\Shared\Shared\Domain\Services\PasswordHasher;
use Helmreel\Shared\Shared\Infrastructure\Events\EventHandlerRegistry;
use Helmreel\Shared\Shared\Infrastructure\Events\LaravelQueueEventBus;
use Helmreel\Shared\Shared\Infrastructure\Services\HttpErrorExtractor;
use Helmreel\Shared\Shared\Infrastructure\Services\HttpResponseValidator as HttpResponseValidatorImpl;
use Helmreel\Shared\Shared\Infrastructure\Services\LaravelHttpClient;
use Helmreel\Shared\Shared\Infrastructure\Services\LaravelPasswordHasher;
use Helmreel\Shared\Shared\Infrastructure\Services\SystemClock;
use Helmreel\YouTube\Channel\Domain\Repositories\ChannelRepository;
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

            return "{$base}/app/reset-password?token={$token}&email={$email}";
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
