<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Script\Domain\Repositories;

interface ScriptGenerator
{
    public function generateGaming(string $prompt, int $totalClips = 5, int $clipDuration = 8): string;

    public function generateWeather(string $prompt, int $totalClips = 5, int $clipDuration = 8): string;
}
