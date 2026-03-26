<?php

declare(strict_types=1);

namespace Canalizador\Shared\Domain\Services;

interface HttpClient
{
    /**
     * @param array<string, string> $headers
     * @param array<string, mixed> $data
     * @return HttpResponse
     */
    public function post(string $url, array $headers, array $data, int $timeout = 30): HttpResponse;

    /**
     * @param array<string, string> $headers
     * @param array<string, mixed> $data
     * @param array<string, string> $files Array of field name => file path
     * @return HttpResponse
     */
    public function postMultipart(string $url, array $headers, array $data, array $files, int $timeout = 30): HttpResponse;

    /**
     * @param array<string, string> $headers
     * @return HttpResponse
     */
    public function get(string $url, array $headers, int $timeout = 30): HttpResponse;
}
