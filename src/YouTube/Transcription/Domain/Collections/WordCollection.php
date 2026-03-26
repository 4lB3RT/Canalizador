<?php

declare(strict_types = 1);

namespace Canalizador\YouTube\Transcription\Domain\Collections;

use Canalizador\Shared\Domain\Collection;
use Canalizador\YouTube\Transcription\Domain\ValueObjects\Word;

final class WordCollection extends Collection
{
    protected function type(): string
    {
        return Word::class;
    }
}
