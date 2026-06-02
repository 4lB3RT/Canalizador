<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Weather\Domain\Repositories;

use Helmreel\VideoProduction\Weather\Domain\Entities\CityForecast;

interface WeatherProvider
{
    /**
     * @return CityForecast[]
     */
    public function fetchForCity(string $municipalityCode, string $cityName): array;
}
