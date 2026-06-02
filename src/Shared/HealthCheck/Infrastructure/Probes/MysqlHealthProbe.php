<?php

declare(strict_types=1);

namespace Helmreel\Shared\HealthCheck\Infrastructure\Probes;

use Helmreel\Shared\HealthCheck\Domain\HealthProbe;
use Helmreel\Shared\HealthCheck\Domain\ServiceStatus;
use Illuminate\Database\ConnectionInterface;
use Throwable;

final readonly class MysqlHealthProbe implements HealthProbe
{
    public function __construct(
        private ConnectionInterface $connection,
    ) {
    }

    public function check(): ServiceStatus
    {
        $start = hrtime(true);

        try {
            $this->connection->select('SELECT 1');

            return ServiceStatus::ok(
                name:      'mysql',
                latencyMs: (int) ((hrtime(true) - $start) / 1_000_000),
            );
        } catch (Throwable $e) {
            return ServiceStatus::failing('mysql', $e->getMessage());
        }
    }
}
