<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Channel\Infrastructure\Repositories\OpenAI;

use Canalizador\YouTube\Channel\Domain\Entities\Channel;
use Canalizador\YouTube\Channel\Domain\Repositories\ChannelMetadataRepository;
use Canalizador\YouTube\Channel\Domain\ValueObjects\ChannelBrand;
use Canalizador\YouTube\Channel\Domain\ValueObjects\ChannelMetadata;
use Canalizador\YouTube\Channel\Domain\ValueObjects\Country;
use Canalizador\YouTube\Channel\Domain\ValueObjects\Description;
use Canalizador\YouTube\Channel\Domain\ValueObjects\Title;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Facades\Prism;

final class OpenAIChannelRepository implements ChannelMetadataRepository
{
    public function generateData(Channel $channel): ChannelMetadata
    {
        $systemPrompt = config('prompts.channel.metadata_generator.system_prompt');

        $response = Prism::text()
            ->using(Provider::OpenAI, config('openai.model_light'))
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

