<?php

declare(strict_types=1);

namespace Canalizador\Shared\HealthCheck\Domain;

final readonly class ServiceStatus
{
    public function __construct(
        public string $name,
        public bool $healthy,
        public ?string $error = null,
        public ?int $latencyMs = null,
    ) {
    }

    public static function ok(string $name, int $latencyMs): self
    {
        return new self(name: $name, healthy: true, latencyMs: $latencyMs);
    }

    public static function failing(string $name, string $error): self
    {
        return new self(name: $name, healthy: false, error: $error);
    }
}
