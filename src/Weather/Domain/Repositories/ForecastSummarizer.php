<?php

declare(strict_types=1);

namespace Canalizador\Weather\Domain\Repositories;

use Canalizador\Weather\Domain\Entities\CityForecast;

interface ForecastSummarizer
{
    /**
     * @param CityForecast[] $forecasts
     * @return array<string, string> ['city_name' => 'resumen']
     */
    public function summarize(array $forecasts): array;
}
