<?php

declare(strict_types=1);

namespace Canalizador\Shared\HealthCheck\Infrastructure\Probes;

use Canalizador\Shared\HealthCheck\Domain\HealthProbe;
use Canalizador\Shared\HealthCheck\Domain\ServiceStatus;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Throwable;

final readonly class RabbitMqHealthProbe implements HealthProbe
{
    public function __construct(
        private string $host,
        private int $port,
        private string $user,
        private string $password,
        private string $vhost,
    ) {
    }

    public function check(): ServiceStatus
    {
        $start = hrtime(true);

        try {
            $connection = new AMQPStreamConnection(
                host:     $this->host,
                port:     $this->port,
                user:     $this->user,
                password: $this->password,
                vhost:    $this->vhost,
                connection_timeout: 2.0,
                read_write_timeout: 2.0,
            );
            $connection->close();

            return ServiceStatus::ok(
                name:      'rabbitmq',
                latencyMs: (int) ((hrtime(true) - $start) / 1_000_000),
            );
        } catch (Throwable $e) {
            return ServiceStatus::failing('rabbitmq', $e->getMessage());
        }
    }
}
