<?php

declare(strict_types=1);

namespace Canalizador\Shared\HealthCheck\Infrastructure\Probes;

use Canalizador\Shared\HealthCheck\Domain\HealthProbe;
use Canalizador\Shared\HealthCheck\Domain\ServiceStatus;
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
