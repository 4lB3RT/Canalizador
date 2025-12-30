<?php

declare(strict_types=1);

namespace Canalizador\Video\Domain\Repositories;

interface VideoGenerator
{
    public function generate(string $prompt): string;
}
