<?php

declare(strict_types=1);

namespace Canalizador\Shared\HealthCheck\Infrastructure\Http\Api\Controllers;

use Canalizador\Shared\HealthCheck\Application\UseCases\GetHealth\GetHealth;
use Canalizador\Shared\HealthCheck\Domain\ServiceStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

final class GetHealthController extends Controller
{
    public function __construct(
        private readonly GetHealth $getHealth,
    ) {
    }

    public function __invoke(): JsonResponse
    {
        $response = $this->getHealth->execute();

        return response()->json([
            'status'    => $response->healthy ? 'ok' : 'degraded',
            'version'   => $response->version,
            'timestamp' => $response->timestamp,
            'services'  => array_map(
                static fn (ServiceStatus $s): array => [
                    'name'       => $s->name,
                    'healthy'    => $s->healthy,
                    'latency_ms' => $s->latencyMs,
                    'error'      => $s->error,
                ],
                $response->services,
            ),
        ], $response->healthy ? 200 : 503);
    }
}
