<?php

declare(strict_types = 1);

namespace Canalizador\Video\Domain\Repositories;

interface TextToSpeechGenerator
{
    public function generate(string $text, ?string $voice = null): string;
}
