<?php

declare(strict_types=1);

namespace Canalizador\Shared\Infrastructure\Services;

use Canalizador\Shared\Domain\Services\HttpClient;
use Canalizador\Shared\Domain\Services\HttpResponse;
use Illuminate\Support\Facades\Http;

final readonly class LaravelHttpClient implements HttpClient
{
    public function post(string $url, array $headers, array $data, int $timeout = 30): HttpResponse
    {
        $response = Http::withHeaders($headers)
            ->timeout($timeout)
            ->post($url, $data);

        return new LaravelHttpResponse($response);
    }

    public function get(string $url, array $headers, int $timeout = 30): HttpResponse
    {
        $response = Http::withHeaders($headers)
            ->timeout($timeout)
            ->get($url);

        return new LaravelHttpResponse($response);
    }
}
