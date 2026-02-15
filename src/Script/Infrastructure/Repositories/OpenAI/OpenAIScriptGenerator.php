<?php

declare(strict_types=1);

namespace Canalizador\Script\Infrastructure\Repositories\OpenAI;

use Canalizador\Channel\Domain\Entities\Channel;
use Canalizador\Script\Domain\Repositories\ScriptGenerator;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Facades\Prism;

final class OpenAIScriptGenerator implements ScriptGenerator
{
    public function generate(?string $prompt = null, Channel $channel): string
    {
        return $this->generateGaming($prompt, $channel);
    }

    public function generateGaming(?string $prompt = null, ?Channel $channel = null, int $totalClips = 5, int $clipDuration = 8): string
    {
        $systemPrompt = config('prompts.script.generator_gaming.system_prompt');

        $channelInfo = $this->extractChannelInfo($channel);
        $totalDuration = $clipDuration + ($totalClips - 1) * ($clipDuration - 1);
        $totalWordsMin = (int) ceil($totalDuration * 2.5);
        $totalWordsMax = (int) floor($totalDuration * 3.0);

        $systemPrompt = str_replace(
            ['{total_clips}', '{clip_duration}', '{total_duration}', '{total_words_min}', '{total_words_max}'],
            [(string) $totalClips, (string) $clipDuration, (string) $totalDuration, (string) $totalWordsMin, (string) $totalWordsMax],
            $systemPrompt
        );

        $userPrompt = $this->buildUserPrompt($prompt, $channel, includeThumbnail: true);

        return $this->executeGeneration($systemPrompt, $userPrompt);
    }

    public function generateAstrology(?string $prompt = null, ?Channel $channel = null, int $totalClips = 5, int $clipDuration = 8): string
    {
        return $this->generateGaming($prompt, $channel, $totalClips, $clipDuration);
    }

    private function executeGeneration(string $systemPrompt, string $userPrompt): string
    {
        $response = Prism::text()
            ->using(Provider::OpenAI, config('openai.model'))
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
            unset($jsonResponse['thinking']);

            return json_encode($jsonResponse, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        return $text;
    }

    private function buildUserPrompt(?string $prompt, ?Channel $channel, bool $includeThumbnail = true): string
    {
        $basePrompt = $prompt ?? 'Genera un guion creativo y atractivo para un vídeo. El guion debe ser claro, estructurado y fácil de seguir.';

        $channelInfo = $this->extractChannelInfo($channel);
        $promptWithChannelInfo = $basePrompt;

        $promptWithChannelInfo .= "\n\n=== INFORMACIÓN DEL CANAL ===";
        if ($includeThumbnail) {
            $promptWithChannelInfo .= "\nURL del Thumbnail del Canal: " . $channelInfo['thumbnail_url'];
        }
        $promptWithChannelInfo .= "\nNombre del Canal: " . $channelInfo['name'];
        $promptWithChannelInfo .= "\nDescripción del Canal: " . $channelInfo['description'];
        $promptWithChannelInfo .= "\nIdioma del Canal: " . $channelInfo['language'];

        return $promptWithChannelInfo . "\n\nResponde SOLO con el JSON solicitado, sin ningún texto adicional.";
    }

    private function extractChannelInfo(?Channel $channel): array
    {
        if ($channel === null) {
            return [
                'thumbnail_url' => '',
                'name' => '',
                'description' => '',
                'language' => 'es',
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
