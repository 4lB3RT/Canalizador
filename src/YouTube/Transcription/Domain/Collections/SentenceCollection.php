<?php

declare(strict_types = 1);

namespace Canalizador\YouTube\Transcription\Domain\Collections;

use Canalizador\Shared\Shared\Domain\Collection;
use Canalizador\YouTube\Transcription\Domain\ValueObjects\Sentence;

final class SentenceCollection extends Collection
{
    protected function type(): string
    {
        return Sentence::class;
    }

    public function sentencesInRange(float $startSeconds, float $endSeconds): self
    {
        $filtered = array_filter(
            $this->items,
            static fn(Sentence $sentence) => $sentence->start()->value() >= $startSeconds
                && $sentence->end()->value() <= $endSeconds
        );

        return new self(array_values($filtered));
    }

    public function toText(): string
    {
        return implode(' ', array_map(
            static fn(Sentence $sentence) => $sentence->text()->value(),
            $this->items
        ));
    }
}
