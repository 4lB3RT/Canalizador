<?php

declare(strict_types=1);

namespace Canalizador\Weather\Infrastructure\Repositories\Aemet;

use Canalizador\Shared\Domain\Services\HttpClient;
use Canalizador\Shared\Domain\Services\HttpResponseValidator;
use Canalizador\Shared\Domain\ValueObjects\DateTime;
use Canalizador\Weather\Domain\Entities\CityForecast;
use Canalizador\Weather\Domain\Repositories\WeatherProvider;
use Canalizador\Weather\Domain\ValueObjects\CityName;
use Canalizador\Weather\Domain\ValueObjects\MunicipalityCode;
use Canalizador\Weather\Domain\ValueObjects\Percentage;
use Canalizador\Weather\Domain\ValueObjects\SnowLevel;
use Canalizador\Weather\Domain\ValueObjects\Temperature;
use Canalizador\Weather\Domain\ValueObjects\UvIndex;
use Canalizador\Weather\Domain\ValueObjects\WeatherState;
use Canalizador\Weather\Domain\ValueObjects\WindDirection;
use Canalizador\Weather\Domain\ValueObjects\WindSpeed;

final readonly class AemetWeatherProvider implements WeatherProvider
{
    private const string BASE_URL = 'https://opendata.aemet.es/opendata/api/prediccion/especifica/municipio/diaria';

    public function __construct(
        private string $apiKey,
        private HttpClient $httpClient,
        private HttpResponseValidator $responseValidator,
    ) {
    }

    /**
     * @return CityForecast[]
     */
    public function fetchForCity(string $municipalityCode, string $cityName): array
    {
        $dataUrl = $this->fetchDataUrl($municipalityCode);

        return $this->fetchForecasts($dataUrl, $municipalityCode, $cityName);
    }

    private function fetchDataUrl(string $municipalityCode): string
    {
        $url = sprintf('%s/%s?api_key=%s', self::BASE_URL, $municipalityCode, $this->apiKey);

        $response = $this->httpClient->get($url, [], 30);
        $this->responseValidator->validateSuccess($response, 'AEMET prediccion request');
        $this->responseValidator->validateJsonKey($response, 'datos', 'AEMET prediccion request');

        return $response->json()['datos'];
    }

    /**
     * @return CityForecast[]
     */
    private function fetchForecasts(string $dataUrl, string $municipalityCode, string $cityName): array
    {
        $response = $this->httpClient->get($dataUrl, [], 30);
        $this->responseValidator->validateSuccess($response, 'AEMET datos request');

        $data = $this->decodeBody($response->body());

        if (!is_array($data) || empty($data)) {
            throw new \RuntimeException('AEMET datos request: empty response');
        }

        $forecasts = [];
        $days = $data[0]['prediccion']['dia'] ?? [];

        foreach ($days as $day) {
            $forecast = $this->parseDayForecast($day, $municipalityCode, $cityName);
            if ($forecast !== null) {
                $forecasts[] = $forecast;
            }
        }

        return $forecasts;
    }

    private function parseDayForecast(array $day, string $municipalityCode, string $cityName): ?CityForecast
    {
        $fecha = $day['fecha'] ?? null;
        if ($fecha === null) {
            return null;
        }

        $maxTemp = $day['temperatura']['maxima'] ?? null;
        $minTemp = $day['temperatura']['minima'] ?? null;

        if ($maxTemp === null || $minTemp === null) {
            return null;
        }

        $weatherDescription = $this->extractWeatherState($day);
        $precipitationProbability = (int) $this->extractPeriodValue($day['probPrecipitacion'] ?? [], 'valor');
        $snowLevel = $this->extractPeriodValue($day['cotaNieveProv'] ?? [], 'valor');
        $windData = $this->extractWindData($day['viento'] ?? []);
        $windGust = (int) $this->extractPeriodValue($day['rachaMax'] ?? [], 'valor');

        return new CityForecast(
            cityName: CityName::fromString($cityName),
            municipalityCode: MunicipalityCode::fromString($municipalityCode),
            forecastDate: new DateTime(new \DateTimeImmutable($fecha)),
            maxTemperature: Temperature::fromInt((int) $maxTemp),
            minTemperature: Temperature::fromInt((int) $minTemp),
            weatherState: WeatherState::fromString($weatherDescription),
            precipitationProbability: Percentage::fromInt($precipitationProbability),
            snowLevel: SnowLevel::fromString($snowLevel),
            windDirection: WindDirection::fromString($windData['direccion']),
            windSpeed: WindSpeed::fromInt($windData['velocidad']),
            windGust: WindSpeed::fromInt($windGust),
            maxThermalSensation: Temperature::fromInt((int) ($day['sensTermica']['maxima'] ?? 0)),
            minThermalSensation: Temperature::fromInt((int) ($day['sensTermica']['minima'] ?? 0)),
            maxHumidity: Percentage::fromInt((int) ($day['humedadRelativa']['maxima'] ?? 0)),
            minHumidity: Percentage::fromInt((int) ($day['humedadRelativa']['minima'] ?? 0)),
            uvIndex: UvIndex::fromInt((int) ($day['uvMax'] ?? 0)),
        );
    }

    private function decodeBody(string $body): ?array
    {
        $data = json_decode($body, true);

        if ($data !== null) {
            return $data;
        }

        $utf8Body = mb_convert_encoding($body, 'UTF-8', 'ISO-8859-15');

        return json_decode($utf8Body, true);
    }

    private function extractWeatherState(array $day): string
    {
        $estadoCielo = $day['estadoCielo'] ?? [];

        foreach ($estadoCielo as $period) {
            $periodo = $period['periodo'] ?? '';
            if ($periodo === '00-24' && !empty($period['descripcion'])) {
                return $period['descripcion'];
            }
        }

        foreach ($estadoCielo as $period) {
            if (!empty($period['descripcion'])) {
                return $period['descripcion'];
            }
        }

        return '';
    }

    private function extractPeriodValue(array $periods, string $key): string
    {
        foreach ($periods as $period) {
            $periodo = $period['periodo'] ?? '';
            if ($periodo === '00-24' && isset($period[$key])) {
                return (string) $period[$key];
            }
        }

        foreach ($periods as $period) {
            if (isset($period[$key]) && $period[$key] !== '') {
                return (string) $period[$key];
            }
        }

        return '';
    }

    /**
     * @return array{direccion: string, velocidad: int}
     */
    private function extractWindData(array $periods): array
    {
        foreach ($periods as $period) {
            $periodo = $period['periodo'] ?? '';
            if ($periodo === '00-24' && isset($period['direccion'])) {
                return [
                    'direccion' => (string) $period['direccion'],
                    'velocidad' => (int) ($period['velocidad'] ?? 0),
                ];
            }
        }

        foreach ($periods as $period) {
            if (isset($period['direccion']) && $period['direccion'] !== '') {
                return [
                    'direccion' => (string) $period['direccion'],
                    'velocidad' => (int) ($period['velocidad'] ?? 0),
                ];
            }
        }

        return [
            'direccion' => '',
            'velocidad' => 0,
        ];
    }
}
