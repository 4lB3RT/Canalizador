<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Weather\Domain\Repositories;

use Helmreel\VideoProduction\Weather\Domain\Entities\CityForecast;

interface ForecastSummarizer
{
    /**
     * @param CityForecast[] $forecasts
     * @return array<string, string> ['city_name' => 'resumen']
     */
    public function summarize(array $forecasts): array;
}
