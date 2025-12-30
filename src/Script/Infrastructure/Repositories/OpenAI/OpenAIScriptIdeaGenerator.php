<?php

declare(strict_types=1);

namespace Canalizador\Script\Infrastructure\Repositories\OpenAI;

use Canalizador\Script\Domain\Repositories\ScriptIdeaGenerator;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;

final class OpenAIScriptIdeaGenerator implements ScriptIdeaGenerator
{
    private const string MODEL = 'gpt-4o';

    public function generateIdea(): string
    {
        $systemPrompt = config('promptScriptIdeaGenerator.system_prompt');

        $response = Prism::text()
            ->using(Provider::OpenAI, self::MODEL)
            ->withSystemPrompt($systemPrompt)
            ->withPrompt('Generate a creative and original video script idea for a 9-second video.')
            ->asText();

        return trim($response->text);
    }
}
