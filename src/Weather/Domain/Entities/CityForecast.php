<?php

declare(strict_types=1);

namespace Canalizador\Weather\Domain\Entities;

use Canalizador\Shared\Domain\ValueObjects\DateTime;
use Canalizador\Weather\Domain\ValueObjects\CityName;
use Canalizador\Weather\Domain\ValueObjects\MunicipalityCode;
use Canalizador\Weather\Domain\ValueObjects\Percentage;
use Canalizador\Weather\Domain\ValueObjects\SnowLevel;
use Canalizador\Weather\Domain\ValueObjects\Temperature;
use Canalizador\Weather\Domain\ValueObjects\UvIndex;
use Canalizador\Weather\Domain\ValueObjects\WeatherState;
use Canalizador\Weather\Domain\ValueObjects\WindDirection;
use Canalizador\Weather\Domain\ValueObjects\WindSpeed;

final readonly class CityForecast
{
    public function __construct(
        private CityName $cityName,
        private MunicipalityCode $municipalityCode,
        private DateTime $forecastDate,
        private Temperature $maxTemperature,
        private Temperature $minTemperature,
        private WeatherState $weatherState,
        private Percentage $precipitationProbability,
        private SnowLevel $snowLevel,
        private WindDirection $windDirection,
        private WindSpeed $windSpeed,
        private WindSpeed $windGust,
        private Temperature $maxThermalSensation,
        private Temperature $minThermalSensation,
        private Percentage $maxHumidity,
        private Percentage $minHumidity,
        private UvIndex $uvIndex,
        private ?string $summary = null,
    ) {
    }

    public function cityName(): CityName
    {
        return $this->cityName;
    }

    public function municipalityCode(): MunicipalityCode
    {
        return $this->municipalityCode;
    }

    public function forecastDate(): DateTime
    {
        return $this->forecastDate;
    }

    public function maxTemperature(): Temperature
    {
        return $this->maxTemperature;
    }

    public function minTemperature(): Temperature
    {
        return $this->minTemperature;
    }

    public function weatherState(): WeatherState
    {
        return $this->weatherState;
    }

    public function precipitationProbability(): Percentage
    {
        return $this->precipitationProbability;
    }

    public function snowLevel(): SnowLevel
    {
        return $this->snowLevel;
    }

    public function windDirection(): WindDirection
    {
        return $this->windDirection;
    }

    public function windSpeed(): WindSpeed
    {
        return $this->windSpeed;
    }

    public function windGust(): WindSpeed
    {
        return $this->windGust;
    }

    public function maxThermalSensation(): Temperature
    {
        return $this->maxThermalSensation;
    }

    public function minThermalSensation(): Temperature
    {
        return $this->minThermalSensation;
    }

    public function maxHumidity(): Percentage
    {
        return $this->maxHumidity;
    }

    public function minHumidity(): Percentage
    {
        return $this->minHumidity;
    }

    public function uvIndex(): UvIndex
    {
        return $this->uvIndex;
    }

    public function summary(): ?string
    {
        return $this->summary;
    }

    public function toArray(): array
    {
        return [
            'city_name' => $this->cityName->value(),
            'municipality_code' => $this->municipalityCode->value(),
            'forecast_date' => $this->forecastDate->value()->format('Y-m-d'),
            'max_temperature' => $this->maxTemperature->value(),
            'min_temperature' => $this->minTemperature->value(),
            'weather_state' => $this->weatherState->value(),
            'precipitation_probability' => $this->precipitationProbability->value(),
            'snow_level' => $this->snowLevel->value(),
            'wind_direction' => $this->windDirection->value(),
            'wind_speed' => $this->windSpeed->value(),
            'wind_gust' => $this->windGust->value(),
            'max_thermal_sensation' => $this->maxThermalSensation->value(),
            'min_thermal_sensation' => $this->minThermalSensation->value(),
            'max_humidity' => $this->maxHumidity->value(),
            'min_humidity' => $this->minHumidity->value(),
            'uv_index' => $this->uvIndex->value(),
            'summary' => $this->summary,
        ];
    }
}
