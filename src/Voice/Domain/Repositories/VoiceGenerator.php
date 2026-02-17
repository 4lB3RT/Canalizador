<?php

declare(strict_types=1);

namespace Canalizador\Voice\Domain\Repositories;

interface VoiceGenerator
{
    /**
     * Converts audio using Speech-to-Speech with the given voice.
     *
     * @return string Path to the converted audio file
     */
    public function generate(string $sourceAudioPath, string $elevenLabsVoiceId): string;
}
