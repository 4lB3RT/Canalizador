<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Weather\Infrastructure\Repositories\OpenAI;

use Canalizador\VideoProduction\Weather\Domain\Entities\CityForecast;
use Canalizador\VideoProduction\Weather\Domain\Repositories\ForecastSummarizer;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Facades\Prism;

final class OpenAIForecastSummarizer implements ForecastSummarizer
{
    /**
     * @param CityForecast[] $forecasts
     * @return array<string, string>
     */
    public function summarize(array $forecasts): array
    {
        if (empty($forecasts)) {
            return [];
        }

        $systemPrompt = config('prompts.weather.forecast_summary.system_prompt');

        $forecastsData = array_map(
            fn (CityForecast $forecast) => $forecast->toArray(),
            $forecasts,
        );

        $response = Prism::text()
            ->using(Provider::OpenAI, config('openai.model_light'))
            ->withSystemPrompt($systemPrompt)
            ->withPrompt(json_encode($forecastsData, JSON_UNESCAPED_UNICODE))
            ->withProviderOptions([
                'response_format' => ['type' => 'json_object'],
            ])
            ->asText();

        $text = trim($response->text);

        $cleanedText = $text;
        if (str_starts_with($text, '```json')) {
            $cleanedText = preg_replace('/^```json\s*/', '', $text);
            $cleanedText = preg_replace('/\s*```$/', '', $cleanedText);
        } elseif (str_starts_with($text, '```')) {
            $cleanedText = preg_replace('/^```\s*/', '', $text);
            $cleanedText = preg_replace('/\s*```$/', '', $cleanedText);
        }

        $cleanedText = trim($cleanedText);

        $jsonResponse = json_decode($cleanedText, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Failed to generate forecast summaries: Invalid JSON response from OpenAI');
        }

        if (!isset($jsonResponse['summaries']) || !is_array($jsonResponse['summaries'])) {
            throw new \RuntimeException('Failed to generate forecast summaries: Missing "summaries" key in response');
        }

        return $jsonResponse['summaries'];
    }
}
