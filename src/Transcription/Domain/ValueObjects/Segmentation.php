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
    
    public function getText(): StringValue
    {
        return $this->text;
    }

    public function getStart(): DateTime
    {
        return $this->start;
    }

    public function getEnd(): DateTime
    {
        return $this->end;
    }
}
