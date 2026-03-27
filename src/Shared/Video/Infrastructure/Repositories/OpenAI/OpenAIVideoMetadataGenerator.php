<?php

declare(strict_types=1);

namespace Canalizador\Shared\Video\Infrastructure\Repositories\OpenAI;

use Canalizador\Shared\Shared\Domain\ValueObjects\Description;
use Canalizador\Shared\Shared\Domain\ValueObjects\Title;
use Canalizador\Shared\Video\Domain\Repositories\VideoMetadataGenerator;
use Canalizador\Shared\Video\Domain\ValueObjects\VideoMetadata;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Facades\Prism;

final class OpenAIVideoMetadataGenerator implements VideoMetadataGenerator
{
    public function generate(string $scriptContent): VideoMetadata
    {
        $systemPrompt = config('prompts.video.metadata_generator.system_prompt');

        $userPrompt = "Based on the following video script, generate both an SEO-optimized title and description:\n\n" . $scriptContent . "\n\nRespond ONLY with the requested JSON, without any additional text.";

        $response = Prism::text()
            ->using(Provider::OpenAI, config('openai.model_light'))
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

        if (json_last_error() !== JSON_ERROR_NONE || !isset($jsonResponse['title']) || !isset($jsonResponse['description'])) {
            throw new \RuntimeException('Failed to generate video metadata: Invalid response from OpenAI');
        }

        return new VideoMetadata(
            title:       Title::fromString($jsonResponse['title']),
            description: Description::fromString($jsonResponse['description']),
        );
    }
}
