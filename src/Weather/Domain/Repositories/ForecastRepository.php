<?php

declare(strict_types=1);

namespace Canalizador\Weather\Domain\Repositories;

use Canalizador\Weather\Domain\Entities\CityForecast;

interface ForecastRepository
{
    public function save(CityForecast $forecast): void;

    /** @param CityForecast[] $forecasts */
    public function saveAll(array $forecasts): void;

    /** @return CityForecast[] */
    public function findByDate(string $date): array;

    /** @param array<string, string> $summaries ['city_name' => 'summary text'] */
    public function saveSummaries(string $date, array $summaries): void;
}
