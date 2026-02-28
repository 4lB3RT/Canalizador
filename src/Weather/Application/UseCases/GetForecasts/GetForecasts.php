<?php

declare(strict_types=1);

namespace Canalizador\Weather\Application\UseCases\GetForecasts;

use Canalizador\Shared\Domain\Services\Clock;
use Canalizador\Weather\Domain\Entities\CityForecast;
use Canalizador\Weather\Domain\Repositories\ForecastRepository;
use Canalizador\Weather\Domain\Repositories\ForecastSummarizer;
use Canalizador\Weather\Domain\Repositories\WeatherProvider;

final readonly class GetForecasts
{
    public function __construct(
        private WeatherProvider $weatherProvider,
        private ForecastRepository $forecastRepository,
        private ForecastSummarizer $forecastSummarizer,
        private Clock $clock,
    ) {
    }

    /**
     * @return CityForecast[]
     */
    public function execute(?string $date = null): array
    {
        $date ??= $this->clock->now()->value()->format('Y-m-d');

        $cached = $this->forecastRepository->findByDate($date);

        if (!empty($cached)) {
            return $this->ensureSummaries($cached, $date);
        }

        $cities = config('weather.cities', []);
        $delayMs = config('weather.fetch_delay_ms', 200);
        $forecasts = [];

        foreach ($cities as $cityName => $municipalityCode) {
            $cityForecasts = $this->weatherProvider->fetchForCity($municipalityCode, $cityName);
            $forecasts = array_merge($forecasts, $cityForecasts);

            usleep($delayMs * 1000);
        }

        $filtered = array_values(array_filter(
            $forecasts,
            fn (CityForecast $forecast) => $forecast->forecastDate()->value()->format('Y-m-d') === $date,
        ));

        $this->forecastRepository->saveAll($filtered);

        return $this->ensureSummaries($filtered, $date);
    }

    /**
     * @param CityForecast[] $forecasts
     * @return CityForecast[]
     */
    private function ensureSummaries(array $forecasts, string $date): array
    {
        if (empty($forecasts)) {
            return $forecasts;
        }

        $allHaveSummary = array_reduce(
            $forecasts,
            fn (bool $carry, CityForecast $f) => $carry && $f->summary() !== null,
            true,
        );

        if ($allHaveSummary) {
            return $forecasts;
        }

        $summaries = $this->forecastSummarizer->summarize($forecasts);
        $this->forecastRepository->saveSummaries($date, $summaries);

        return $this->forecastRepository->findByDate($date);
    }
}
