<?php

declare(strict_types=1);

namespace Canalizador\Voice\Domain\Repositories;

interface VoiceCloner
{
    /**
     * Clones a voice from an audio sample.
     *
     * @return string The platform voice ID for use in Speech-to-Speech
     */
    public function clone(string $audioPath, string $name): string;
}
