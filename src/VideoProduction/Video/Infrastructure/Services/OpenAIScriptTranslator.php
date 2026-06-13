<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Video\Infrastructure\Services;

use Helmreel\Shared\Shared\Domain\ValueObjects\Language;
use Helmreel\VideoProduction\Video\Domain\Services\ScriptTranslator;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Facades\Prism;

final class OpenAIScriptTranslator implements ScriptTranslator
{
    public function translate(string $scriptContent, Language $targetLanguage): string
    {
        $systemPrompt = sprintf(
            'You are a professional translator. You receive a JSON object with the keys "full_script" and "clip_prompts" (an array of strings). '
            . 'Translate ALL the textual content into %s, keeping the JSON structure, the same keys and the same number of array items. '
            . 'Translate naturally and fluently as a native speaker. Do NOT add, remove or reorder keys or items. Do NOT add explanations. '
            . 'Respond ONLY with the translated JSON object.',
            $targetLanguage->promptLabel(),
        );

        $response = Prism::text()
            ->using(Provider::OpenAI, config('openai.model'))
            ->withSystemPrompt($systemPrompt)
            ->withPrompt($scriptContent)
            ->withProviderOptions([
                'response_format' => ['type' => 'json_object'],
            ])
            ->asText();

        $text = trim($response->text);

        $cleaned = $text;
        if (str_starts_with($text, '```json')) {
            $cleaned = preg_replace('/^```json\s*/', '', $text);
            $cleaned = preg_replace('/\s*```$/', '', (string) $cleaned);
        } elseif (str_starts_with($text, '```')) {
            $cleaned = preg_replace('/^```\s*/', '', $text);
            $cleaned = preg_replace('/\s*```$/', '', (string) $cleaned);
        }

        $cleaned = trim((string) $cleaned);

        $decoded = json_decode($cleaned, true);

        if (json_last_error() === JSON_ERROR_NONE && isset($decoded['full_script'])) {
            return json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        return $scriptContent;
    }

    public function translateText(string $text, Language $targetLanguage): string
    {
        $systemPrompt = sprintf(
            'You are a professional translator for AI video generation prompts. You receive a clip prompt that mixes visual directions and the words the presenter speaks. '
            . 'Translate ALL of it into %s, keeping the same meaning, structure and any section labels. The spoken/dialogue parts MUST end up in %s so the generated voice speaks that language. '
            . 'Do NOT add explanations. Respond ONLY with the translated text.',
            $targetLanguage->promptLabel(),
            $targetLanguage->promptLabel(),
        );

        $response = Prism::text()
            ->using(Provider::OpenAI, config('openai.model'))
            ->withSystemPrompt($systemPrompt)
            ->withPrompt($text)
            ->asText();

        $translated = trim($response->text);

        return $translated !== '' ? $translated : $text;
    }
}
