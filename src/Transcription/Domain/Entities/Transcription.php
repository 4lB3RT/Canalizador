<?php

declare(strict_types = 1);

namespace Canalizador\Video\Domain\ValueObjects;

use Canalizador\Category\Domain\Entities\Category;
use Canalizador\Metric\Domain\Entities\MetricCollection;
use Canalizador\Shared\Domain\ValueObjects\DateTime;
use Canalizador\Shared\Domain\ValueObjects\StringValue;

final class Transcription
{
    public function __construct(
        private StringValue $text,
        private StringValue $language,
        private StringValue $segments,

        private DateTime $createdAt,
    ) {
    }

}
