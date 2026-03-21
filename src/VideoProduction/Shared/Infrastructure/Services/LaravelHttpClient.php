<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Shared\Infrastructure\Services;

use Canalizador\VideoProduction\Shared\Domain\Services\HttpClient;
use Canalizador\VideoProduction\Shared\Domain\Services\HttpResponse;
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

    public function postMultipart(string $url, array $headers, array $data, array $files, int $timeout = 30): HttpResponse
    {
        // Remove Content-Type header - Laravel will set multipart/form-data automatically
        unset($headers['Content-Type']);

        $request = Http::withHeaders($headers)->timeout($timeout);

        foreach ($files as $fieldName => $filePath) {
            $request = $request->attach(
                $fieldName,
                file_get_contents($filePath),
                basename($filePath)
            );
        }

        $response = $request->post($url, $data);

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
