<?php

declare(strict_types=1);

namespace Canalizador\Channel\Infrastructure\Repositories\OpenAI;

use Canalizador\Channel\Domain\Entities\Channel;
use Canalizador\Channel\Domain\Repositories\ChannelMetadataRepository;
use Canalizador\Channel\Domain\ValueObjects\ChannelBrand;
use Canalizador\Channel\Domain\ValueObjects\ChannelMetadata;
use Canalizador\Channel\Domain\ValueObjects\Country;
use Canalizador\Channel\Domain\ValueObjects\Description;
use Canalizador\Channel\Domain\ValueObjects\Title;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;

final class OpenAIChannelRepository implements ChannelMetadataRepository
{
    private const string MODEL = 'gpt-4o-mini';

    public function generateData(Channel $channel): ChannelMetadata
    {
        $systemPrompt = config('promptChannelMetadataGenerator.system_prompt');

        $response = Prism::text()
            ->using(Provider::OpenAI, self::MODEL)
            ->withSystemPrompt($systemPrompt)
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
            throw new \RuntimeException('Failed to generate channel metadata: Invalid JSON response from OpenAI');
        }

        $country = Country::fromString($jsonResponse['country']);
        $channelBrand = ChannelBrand::fromString($jsonResponse['channelBrand']);
        $description = Description::fromString($jsonResponse['description']);
        $title = Title::fromString($jsonResponse['title']);

        return new ChannelMetadata(
            country: $country,
            channelBrand: $channelBrand,
            description: $description,
            title: $title,
        );
    }
}

