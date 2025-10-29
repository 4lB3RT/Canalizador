<?php

declare(strict_types = 1);

namespace Canalizador\Transcription\Domain\ValueObjects;

use Canalizador\Shared\Domain\ValueObjects\StringValue;

Enum Language: string
{
    case ENGLISH = 'en';
    case PORTUGUESE = 'pt';
    case SPANISH = 'es';
}
