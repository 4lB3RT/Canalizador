<?php

declare(strict_types = 1);

namespace Canalizador\YouTube\Transcription\Domain\Collections;

use Canalizador\Shared\Shared\Domain\Collection;
use Canalizador\YouTube\Transcription\Domain\ValueObjects\Word;

final class WordCollection extends Collection
{
    protected function type(): string
    {
        return Word::class;
    }

    public function wordsInRange(float $startSeconds, float $endSeconds): self
    {
        $filtered = array_filter(
            $this->items,
            static fn(Word $word) => $word->start()->value() >= $startSeconds
                && $word->end()->value() <= $endSeconds
        );

        return new self(array_values($filtered));
    }

    public function toText(): string
    {
        return implode(' ', array_map(
            static fn(Word $word) => $word->text()->value(),
            $this->items
        ));
    }
}
