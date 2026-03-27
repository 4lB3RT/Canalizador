<?php

namespace Canalizador\YouTube\Transcription\Domain\ValueObjects;

use Canalizador\Shared\Shared\Domain\ValueObjects\Essentials\StringValue;

final readonly class Word
{
    public function __construct(
        private Text $text,
        private StartTime $start,
        private EndTime $end,
    ) {
    }

    public function text(): StringValue
    {
        return $this->text;
    }

    public function start(): StartTime
    {
        return $this->start;
    }

    public function end(): EndTime
    {
        return $this->end;
    }

    public function toArray(): array
    {
        return [
            'text'  => $this->text->value(),
            'start' => $this->start->value(),
            'end'   => $this->end->value(),
        ];
    }
}
