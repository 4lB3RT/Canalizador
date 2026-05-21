<?php

declare(strict_types=1);

namespace Tests\Feature\Shared\HealthCheck;

use Canalizador\Shared\HealthCheck\Application\UseCases\GetHealth\GetHealth;
use Canalizador\Shared\HealthCheck\Application\UseCases\GetHealth\GetHealthResponse;
use Canalizador\Shared\HealthCheck\Domain\ServiceStatus;
use Tests\TestCase;

final class GetHealthControllerTest extends TestCase
{
    public function test_returns_200_and_ok_status_when_all_services_healthy(): void
    {
        $this->app->bind(GetHealth::class, function () {
            return new class extends GetHealth {
                public function __construct()
                {
                    parent::__construct(probes: [], version: 'test-1.0');
                }

                public function execute(): GetHealthResponse
                {
                    return new GetHealthResponse(
                        healthy: true,
                        services: [
                            ServiceStatus::ok('mysql',    3),
                            ServiceStatus::ok('redis',    1),
                            ServiceStatus::ok('rabbitmq', 5),
                        ],
                        version:   'test-1.0',
                        timestamp: '2026-05-21T20:00:00+00:00',
                    );
                }
            };
        });

        $this->getJson('/api/health-check')
            ->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('version', 'test-1.0')
            ->assertJsonPath('services.0.name', 'mysql')
            ->assertJsonPath('services.0.healthy', true)
            ->assertJsonPath('services.0.latency_ms', 3)
            ->assertJsonPath('services.1.name', 'redis')
            ->assertJsonPath('services.2.name', 'rabbitmq');
    }

    public function test_returns_503_and_degraded_status_when_a_service_fails(): void
    {
        $this->app->bind(GetHealth::class, function () {
            return new class extends GetHealth {
                public function __construct()
                {
                    parent::__construct(probes: [], version: 'test-1.0');
                }

                public function execute(): GetHealthResponse
                {
                    return new GetHealthResponse(
                        healthy: false,
                        services: [
                            ServiceStatus::ok('mysql',         2),
                            ServiceStatus::failing('redis',    'Connection refused'),
                            ServiceStatus::ok('rabbitmq',      4),
                        ],
                        version:   'test-1.0',
                        timestamp: '2026-05-21T20:00:00+00:00',
                    );
                }
            };
        });

        $this->getJson('/api/health-check')
            ->assertStatus(503)
            ->assertJsonPath('status', 'degraded')
            ->assertJsonPath('services.1.healthy', false)
            ->assertJsonPath('services.1.error', 'Connection refused');
    }

    public function test_does_not_require_api_token(): void
    {
        $this->app->bind(GetHealth::class, function () {
            return new class extends GetHealth {
                public function __construct()
                {
                    parent::__construct(probes: [], version: 'test-1.0');
                }

                public function execute(): GetHealthResponse
                {
                    return new GetHealthResponse(
                        healthy: true,
                        services: [],
                        version:   'test-1.0',
                        timestamp: '2026-05-21T20:00:00+00:00',
                    );
                }
            };
        });

        $this->getJson('/api/health-check')
            ->assertStatus(200);
    }
}
