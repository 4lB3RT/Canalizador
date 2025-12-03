<?php

declare(strict_types = 1);

namespace Canalizador\Script\Domain\ValueObjects;

final readonly class ScriptMetadata
{
    public function __construct(
        private array $metadata
    ) {
        if (!is_array($metadata)) {
            throw new \InvalidArgumentException('Script metadata must be an array');
        }
    }

    public function value(): array
    {
        return $this->metadata;
    }

    public function toArray(): array
    {
        return $this->metadata;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->metadata[$key] ?? $default;
    }

    public static function empty(): self
    {
        return new self([]);
    }
}
