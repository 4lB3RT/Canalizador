<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Domain\Repositories;

use Canalizador\Shared\Shared\Domain\ValueObjects\Essentials\LocalPath;

interface SmartVideoFragmenter
{
    /**
     * La IA analiza la transcripción y decide los cortes con criterio narrativo.
     *
     * @param  array<int, array{start: float, end: float, text: string}> $transcription
     * @return LocalPath[] shorts con criterio narrativo
     */
    public function fragment(LocalPath $videoPath, array $transcription): array;
}
