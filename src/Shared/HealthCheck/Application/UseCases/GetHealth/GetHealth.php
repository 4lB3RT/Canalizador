<?php

declare(strict_types=1);

namespace Helmreel\Shared\HealthCheck\Application\UseCases\GetHealth;

use Helmreel\Shared\HealthCheck\Domain\HealthProbe;
use Helmreel\Shared\HealthCheck\Domain\ServiceStatus;

final readonly class GetHealth
{
    /**
     * @param HealthProbe[] $probes
     */
    public function __construct(
        private array $probes,
        private string $version,
    ) {
    }

    public function execute(): GetHealthResponse
    {
        $services = array_map(
            static fn (HealthProbe $probe): ServiceStatus => $probe->check(),
            $this->probes,
        );

        $healthy = array_reduce(
            $services,
            static fn (bool $carry, ServiceStatus $status): bool => $carry && $status->healthy,
            true,
        );

        return new GetHealthResponse(
            healthy:   $healthy,
            services:  $services,
            version:   $this->version,
            timestamp: (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
        );
    }
}
