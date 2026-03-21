<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Weather\Infrastructure\Repositories\Eloquent;

use Canalizador\VideoProduction\Shared\Domain\ValueObjects\DateTime;
use Canalizador\VideoProduction\Weather\Domain\Entities\CityForecast;
use Canalizador\VideoProduction\Weather\Domain\Repositories\ForecastRepository;
use Canalizador\VideoProduction\Weather\Domain\ValueObjects\CityName;
use Canalizador\VideoProduction\Weather\Domain\ValueObjects\MunicipalityCode;
use Canalizador\VideoProduction\Weather\Domain\ValueObjects\Percentage;
use Canalizador\VideoProduction\Weather\Domain\ValueObjects\SnowLevel;
use Canalizador\VideoProduction\Weather\Domain\ValueObjects\Temperature;
use Canalizador\VideoProduction\Weather\Domain\ValueObjects\UvIndex;
use Canalizador\VideoProduction\Weather\Domain\ValueObjects\WeatherState;
use Canalizador\VideoProduction\Weather\Domain\ValueObjects\WindDirection;
use Canalizador\VideoProduction\Weather\Domain\ValueObjects\WindSpeed;
use Canalizador\VideoProduction\Weather\Infrastructure\DAO\CityForecastDAO;

final class EloquentForecastRepository implements ForecastRepository
{
    public function save(CityForecast $forecast): void
    {
        CityForecastDAO::updateOrCreate(
            [
                'municipality_code' => $forecast->municipalityCode()->value(),
                'forecast_date' => $forecast->forecastDate()->value()->format('Y-m-d'),
            ],
            [
                'city_name' => $forecast->cityName()->value(),
                'max_temperature' => $forecast->maxTemperature()->value(),
                'min_temperature' => $forecast->minTemperature()->value(),
                'weather_state' => $forecast->weatherState()->value(),
                'precipitation_probability' => $forecast->precipitationProbability()->value(),
                'snow_level' => $forecast->snowLevel()->value(),
                'wind_direction' => $forecast->windDirection()->value(),
                'wind_speed' => $forecast->windSpeed()->value(),
                'wind_gust' => $forecast->windGust()->value(),
                'max_thermal_sensation' => $forecast->maxThermalSensation()->value(),
                'min_thermal_sensation' => $forecast->minThermalSensation()->value(),
                'max_humidity' => $forecast->maxHumidity()->value(),
                'min_humidity' => $forecast->minHumidity()->value(),
                'uv_index' => $forecast->uvIndex()->value(),
            ]
        );
    }

    /** @param CityForecast[] $forecasts */
    public function saveAll(array $forecasts): void
    {
        foreach ($forecasts as $forecast) {
            $this->save($forecast);
        }
    }

    /** @return CityForecast[] */
    public function findByDate(string $date): array
    {
        $models = CityForecastDAO::where('forecast_date', $date)->get();

        return $models->map(fn (CityForecastDAO $model) => $this->toEntity($model))->all();
    }

    /** @param array<string, string> $summaries */
    public function saveSummaries(string $date, array $summaries): void
    {
        foreach ($summaries as $cityName => $summary) {
            CityForecastDAO::where('forecast_date', $date)
                ->where('city_name', $cityName)
                ->update(['summary' => $summary]);
        }
    }

    private function toEntity(CityForecastDAO $model): CityForecast
    {
        return new CityForecast(
            cityName: CityName::fromString($model->city_name),
            municipalityCode: MunicipalityCode::fromString($model->municipality_code),
            forecastDate: new DateTime(new \DateTimeImmutable($model->forecast_date->format('Y-m-d'))),
            maxTemperature: Temperature::fromInt($model->max_temperature),
            minTemperature: Temperature::fromInt($model->min_temperature),
            weatherState: WeatherState::fromString($model->weather_state),
            precipitationProbability: Percentage::fromInt($model->precipitation_probability),
            snowLevel: SnowLevel::fromString($model->snow_level),
            windDirection: WindDirection::fromString($model->wind_direction),
            windSpeed: WindSpeed::fromInt($model->wind_speed),
            windGust: WindSpeed::fromInt($model->wind_gust),
            maxThermalSensation: Temperature::fromInt($model->max_thermal_sensation),
            minThermalSensation: Temperature::fromInt($model->min_thermal_sensation),
            maxHumidity: Percentage::fromInt($model->max_humidity),
            minHumidity: Percentage::fromInt($model->min_humidity),
            uvIndex: UvIndex::fromInt($model->uv_index),
            summary: $model->summary,
        );
    }
}
