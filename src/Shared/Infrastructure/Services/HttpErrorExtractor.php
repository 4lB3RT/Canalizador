<?php

declare(strict_types=1);

namespace Canalizador\Shared\Infrastructure\Services;

use Illuminate\Http\Client\Response;

final readonly class HttpErrorExtractor
{
    public function extract(Response $response): string
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
