<?php

declare(strict_types=1);

namespace Canalizador\Script\Infrastructure\Repositories\OpenAI;

use Canalizador\Channel\Domain\Entities\Channel;
use Canalizador\Script\Domain\Repositories\ScriptGenerator;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;

final class OpenAIScriptGenerator implements ScriptGenerator
{
    private const string MODEL = 'gpt-4o';

    public function generate(?string $prompt = null, Channel $channel): string
    {
        return $this->generateAstrology($prompt, $channel);
    }

    public function generateGaming(?string $prompt = null, ?Channel $channel = null): string
    {
        $systemPrompt = config('prompts.script.generator_gaming.system_prompt');

        $channelInfo = $this->extractChannelInfo($channel);
        $systemPrompt = str_replace(
            ['{channel_thumbnail_url}', '{channel_name}', '{channel_description}'],
            [$channelInfo['thumbnail_url'], $channelInfo['name'], $channelInfo['description']],
            $systemPrompt
        );

        $userPrompt = $this->buildUserPrompt($prompt, $channel, includeThumbnail: true);

        return $this->executeGeneration($systemPrompt, $userPrompt);
    }

    public function generateAstrology(?string $prompt = null, ?Channel $channel = null): string
    {
        $systemPrompt = config('prompts.script.generator_astrology.system_prompt');

        $channelInfo = $this->extractChannelInfo($channel);
        $systemPrompt = str_replace(
            ['{channel_name}', '{channel_description}'],
            [$channelInfo['name'], $channelInfo['description']],
            $systemPrompt
        );

        $userPrompt = $this->buildUserPrompt($prompt, $channel, includeThumbnail: false);

        return $this->executeGeneration($systemPrompt, $userPrompt);
    }

    private function executeGeneration(string $systemPrompt, string $userPrompt): string
    {
        $response = Prism::text()
            ->using(Provider::OpenAI, self::MODEL)
            ->withSystemPrompt($systemPrompt)
            ->withPrompt($userPrompt)
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

        if (json_last_error() === JSON_ERROR_NONE && isset($jsonResponse['full_script'])) {
            return json_encode($jsonResponse, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        return $text;
    }

    private function buildUserPrompt(?string $prompt, ?Channel $channel, bool $includeThumbnail = true): string
    {
        $basePrompt = $prompt ?? 'Generate a creative and engaging script for a video. The script must be clear, structured, and easy to follow.';

        $channelInfo = $this->extractChannelInfo($channel);
        $promptWithChannelInfo = $basePrompt;

        $promptWithChannelInfo .= "\n\n=== CHANNEL INFORMATION ===";
        if ($includeThumbnail) {
            $promptWithChannelInfo .= "\nChannel Thumbnail URL: " . $channelInfo['thumbnail_url'];
        }
        $promptWithChannelInfo .= "\nChannel Name: " . $channelInfo['name'];
        $promptWithChannelInfo .= "\nChannel Description: " . $channelInfo['description'];
        $promptWithChannelInfo .= "\nChannel Language: " . $channelInfo['language'];

        return $promptWithChannelInfo . "\n\nRespond ONLY with the requested JSON, without any additional text.";
    }

    private function extractChannelInfo(?Channel $channel): array
    {
        if ($channel === null) {
            return [
                'thumbnail_url' => '',
                'name' => '',
                'description' => '',
                'language' => 'en',
            ];
        }

        return [
            'thumbnail_url' => $channel->thumbnailUrl()?->value() ?? '',
            'name' => $channel->title()->value(),
            'description' => $channel->description()->value(),
            'language' => $channel->country()->toLanguageCode(),
        ];
    }
}
