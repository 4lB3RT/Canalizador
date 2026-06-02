<?php

declare(strict_types=1);

namespace Helmreel\Shared\HealthCheck\Application\UseCases\GetHealth;

use Helmreel\Shared\HealthCheck\Domain\ServiceStatus;

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
