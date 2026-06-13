<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Script\Domain\Repositories;

use Helmreel\Shared\Shared\Domain\ValueObjects\Language;

interface ScriptGenerator
{
    public function generateGaming(string $prompt, Language $language, int $totalClips = 5, int $clipDuration = 8): string;

    public function generateWeather(string $prompt, Language $language, int $totalClips = 5, int $clipDuration = 8): string;
}
