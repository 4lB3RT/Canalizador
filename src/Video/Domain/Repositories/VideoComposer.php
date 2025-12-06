<?php

declare(strict_types = 1);

namespace Canalizador\Video\Domain\Repositories;

interface VideoComposer
{
    public function compose(string $videoPath, string $audioPath): string;
}
