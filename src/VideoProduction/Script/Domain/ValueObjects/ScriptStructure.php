<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Script\Domain\ValueObjects;

final readonly class ScriptStructure
{
    public function __construct(
        private array $structure
    ) {
        if (!is_array($structure)) {
            throw new \InvalidArgumentException('Script structure must be an array');
        }
    }

    public function value(): array
    {
        return $this->structure;
    }

    public function toArray(): array
    {
        return $this->structure;
    }

    public static function empty(): self
    {
        return new self([]);
    }
}
