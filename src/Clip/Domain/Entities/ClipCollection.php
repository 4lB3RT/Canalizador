<?php

declare(strict_types=1);

namespace Canalizador\Clip\Domain\Entities;

use Canalizador\Clip\Domain\ValueObjects\ClipStatus;
use Canalizador\Shared\Domain\Collection;

final class ClipCollection extends Collection
{
    protected function type(): string
    {
        return Clip::class;
    }

    public function sortedBySequence(): self
    {
        $items = $this->items();

        usort($items, fn (Clip $a, Clip $b) => $a->sequence()->value() <=> $b->sequence()->value());

        return new self($items);
    }

    public function findGeneratingBySequence(int $sequence): ?Clip
    {
        foreach ($this->items() as $clip) {
            if ($clip->status() === ClipStatus::GENERATING && $clip->sequence()->value() === $sequence) {
                return $clip;
            }
        }

        return null;
    }

    public function lastCompleted(): ?Clip
    {
        $completed = array_filter(
            $this->items(),
            fn (Clip $clip) => $clip->isCompleted()
        );

        if (empty($completed)) {
            return null;
        }

        usort($completed, fn (Clip $a, Clip $b) => $b->sequence()->value() <=> $a->sequence()->value());

        return $completed[0];
    }
}
