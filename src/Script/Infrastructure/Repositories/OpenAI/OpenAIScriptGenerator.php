<?php

declare(strict_types=1);

namespace Canalizador\Script\Infrastructure\Repositories\OpenAI;

use Canalizador\Script\Domain\Repositories\ScriptGenerator;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;

final class OpenAIScriptGenerator implements ScriptGenerator
{
    private const string MODEL = 'gpt-4o-mini';

    public function generate(?string $prompt = null): string
    {
        $systemPrompt = config('promptScriptGenerator.system_prompt');

        $userPrompt = ($prompt ?? 'Generate a creative and engaging script for a video. The script must be clear, structured, and easy to follow.') . "\n\nRespond ONLY with the requested JSON, without any additional text.";

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
}
