<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Weather\Domain\Repositories;

use Canalizador\VideoProduction\Weather\Domain\Entities\CityForecast;

interface WeatherProvider
{
    /**
     * @return CityForecast[]
     */
    public function fetchForCity(string $municipalityCode, string $cityName): array;
}
