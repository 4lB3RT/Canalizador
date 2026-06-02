<?php

declare(strict_types=1);

namespace Helmreel\Shared\HealthCheck\Infrastructure\Probes;

use Helmreel\Shared\HealthCheck\Domain\HealthProbe;
use Helmreel\Shared\HealthCheck\Domain\ServiceStatus;
use Illuminate\Contracts\Redis\Factory as RedisFactory;
use Throwable;

final readonly class RedisHealthProbe implements HealthProbe
{
    public function __construct(
        private RedisFactory $redis,
    ) {
    }

    public function check(): ServiceStatus
    {
        $start = hrtime(true);

        try {
            $response = $this->redis->connection()->command('ping');

            if ($response === false || $response === null || $response === 0 || $response === '') {
                return ServiceStatus::failing('redis', 'PING returned a falsy response');
            }

            return ServiceStatus::ok(
                name:      'redis',
                latencyMs: (int) ((hrtime(true) - $start) / 1_000_000),
            );
        } catch (Throwable $e) {
            return ServiceStatus::failing('redis', $e->getMessage());
        }
    }
}
