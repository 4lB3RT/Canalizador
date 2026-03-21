<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Weather\Infrastructure\Http\Api\Controllers;

use Canalizador\VideoProduction\Weather\Application\UseCases\GetForecasts\GetForecasts;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class GetForecastsController extends Controller
{
    public function __construct(
        private readonly GetForecasts $getForecasts,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $date = $request->query('date');
        $forecasts = $this->getForecasts->execute($date);

        return response()->json([
            'forecasts' => array_map(
                fn ($forecast) => $forecast->toArray(),
                $forecasts,
            ),
        ]);
    }
}
