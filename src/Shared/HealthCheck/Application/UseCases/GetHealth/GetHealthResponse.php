<?php

declare(strict_types=1);

namespace Canalizador\Shared\HealthCheck\Application\UseCases\GetHealth;

use Canalizador\Shared\HealthCheck\Domain\ServiceStatus;

final readonly class GetHealthResponse
{
    /**
     * @param ServiceStatus[] $services
     */
    public function __construct(
        public bool $healthy,
        public array $services,
        public string $version,
        public string $timestamp,
    ) {
    }
}
