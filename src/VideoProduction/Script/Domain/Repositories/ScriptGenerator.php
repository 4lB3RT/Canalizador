<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Script\Domain\Repositories;

use Canalizador\YouTube\Channel\Domain\Entities\Channel;

interface ScriptGenerator
{
    public function generateGaming(string $prompt, ?Channel $channel = null, int $totalClips = 5, int $clipDuration = 8): string;

    public function generateWeather(string $prompt, ?Channel $channel = null, int $totalClips = 5, int $clipDuration = 8): string;
}
