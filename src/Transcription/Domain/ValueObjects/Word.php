<?php

namespace Canalizador\Transcription\Domain\ValueObjects;

use Canalizador\Shared\Domain\ValueObjects\DateTime;
use Canalizador\Shared\Domain\ValueObjects\StringValue;

final readonly class Word
{
    public function __construct(
        public StringValue $text,
        public DateTime $start,
        public DateTime $end,
    ) {
    }

    public function text(): StringValue
    {
        return $this->text;
    }

    public function start(): DateTime
    {
        return $this->start;
    }

    public function end(): DateTime
    {
        return $this->end;
    }

    public function toArray(): array
    {
        return [
            'text' => $this->text->value(),
            'start' => $this->start->value(),
            'end' => $this->end->value(),
        ];
    }
}
