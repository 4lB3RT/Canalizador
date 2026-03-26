<?php

declare(strict_types=1);

namespace Canalizador\Shared\Infrastructure\Services;

use Canalizador\Shared\Domain\Services\HttpResponse;
use Illuminate\Http\Client\Response;

final readonly class LaravelHttpResponse implements HttpResponse
{
    public function __construct(
        private Response $response
    ) {
    }

    public function isSuccessful(): bool
    {
        return $this->response->successful();
    }

    public function status(): int
    {
        return $this->response->status();
    }

    public function json(): ?array
    {
        return $this->response->json();
    }

    public function body(): string
    {
        return $this->response->body();
    }

    public function header(string $name): ?string
    {
        return $this->response->header($name);
    }
}
