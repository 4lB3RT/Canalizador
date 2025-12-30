<?php

declare(strict_types=1);

namespace Canalizador\Shared\Infrastructure\Services;

use Canalizador\Shared\Domain\Services\HttpResponse;
use Canalizador\Shared\Domain\Services\HttpResponseValidator as HttpResponseValidatorInterface;

final readonly class HttpResponseValidator implements HttpResponseValidatorInterface
{
    public function __construct(
        private HttpErrorExtractor $errorExtractor
    ) {
    }

    public function validateSuccess(HttpResponse $response, string $context): void
    {
        if ($response->isSuccessful()) {
            return;
        }

        $statusCode = $response->status();
        $errorMessage = $this->extractError($response);

        throw new \RuntimeException(
            sprintf('%s: HTTP %d - %s', $context, $statusCode, $errorMessage)
        );
    }

    public function validateJsonKey(HttpResponse $response, string $key, string $context): void
    {
        $data = $response->json();

        if ($data === null || !isset($data[$key])) {
            throw new \RuntimeException(
                sprintf('%s: Invalid response - missing key "%s"', $context, $key)
            );
        }
    }

    private function extractError(HttpResponse $response): string
    {
        $body = $response->body();

        try {
            $data = $response->json();
            if (isset($data['error']['message'])) {
                return $data['error']['message'];
            }
            if (isset($data['error'])) {
                return is_string($data['error']) ? $data['error'] : json_encode($data['error']);
            }
            if (isset($data['message'])) {
                return $data['message'];
            }
        } catch (\Exception $e) {
        }

        return strlen($body) > 200 ? substr($body, 0, 200) . '...' : $body;
    }
}
