<?php

declare(strict_types = 1);

namespace Canalizador\Shared\Domain\Services;

interface HttpResponse
{
    public function isSuccessful(): bool;

    public function status(): int;

    public function json(): ?array;

    public function body(): string;

    public function header(string $name): ?string;
}
