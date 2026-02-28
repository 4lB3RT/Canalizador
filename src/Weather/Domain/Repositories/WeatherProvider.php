<?php

declare(strict_types=1);

namespace Canalizador\Weather\Domain\Repositories;

use Canalizador\Weather\Domain\Entities\CityForecast;

interface WeatherProvider
{
    /**
     * @return CityForecast[]
     */
    public function fetchForCity(string $municipalityCode, string $cityName): array;
}
