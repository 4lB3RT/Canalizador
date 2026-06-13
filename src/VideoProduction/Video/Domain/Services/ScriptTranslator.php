<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Video\Domain\Services;

use Helmreel\Shared\Shared\Domain\ValueObjects\Language;

interface ScriptTranslator
{
    /**
     * Translates the script content (JSON with full_script + clip_prompts)
     * into the target language, preserving the JSON structure.
     */
    public function translate(string $scriptContent, Language $targetLanguage): string;

    /**
     * Translates the spoken/dialogue text of a single clip prompt into the target
     * language, keeping the rest of the prompt (visual directions) usable by Veo.
     */
    public function translateText(string $text, Language $targetLanguage): string;
}
