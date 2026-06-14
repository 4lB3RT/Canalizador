<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Avatar\Infrastructure\Repositories\OpenAI;

use Helmreel\VideoProduction\Avatar\Domain\Repositories\AvatarDataGenerator;
use Helmreel\VideoProduction\Avatar\Domain\ValueObjects\AvatarData;
use Helmreel\VideoProduction\Avatar\Domain\ValueObjects\AvatarName;
use Helmreel\VideoProduction\Avatar\Domain\ValueObjects\Biography;
use Helmreel\VideoProduction\Avatar\Domain\ValueObjects\Category;
use Helmreel\VideoProduction\Avatar\Domain\ValueObjects\PresentationStyle;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Facades\Prism;

final class OpenAIAvatarDataGenerator implements AvatarDataGenerator
{
    public function generate(Category $category): AvatarData
    {
        $systemPrompt = str_replace(
            '{category}',
            $category->value,
            config('prompts.avatar.data_generator.system_prompt'),
        );

        $userPrompt = "Genera el presentador para la categoría indicada.\n\nResponde SOLO con el JSON solicitado, sin ningún texto adicional.";

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
            $cleanedText = preg_replace('/\s*```$/', '', $text);
        }

        $cleanedText = trim($cleanedText);

        $jsonResponse = json_decode($cleanedText, true);

        if (
            json_last_error() !== JSON_ERROR_NONE
            || !isset($jsonResponse['name'])
            || !isset($jsonResponse['biography'])
            || !isset($jsonResponse['presentation_style'])
        ) {
            throw new \RuntimeException('Failed to generate avatar data: Invalid response from OpenAI');
        }

        return new AvatarData(
            name: AvatarName::fromString($jsonResponse['name']),
            biography: Biography::fromString($jsonResponse['biography']),
            presentationStyle: PresentationStyle::fromString($jsonResponse['presentation_style']),
        );
    }
}
