<?php

declare(strict_types = 1);

namespace Canalizador\Shared\Domain\Services;

interface HttpResponseValidator
{
    /**
     * @throws \RuntimeException
     */
    public function validateSuccess(HttpResponse $response, string $context): void;

    /**
     * @throws \RuntimeException
     */
    public function validateJsonKey(HttpResponse $response, string $key, string $context): void;
}
