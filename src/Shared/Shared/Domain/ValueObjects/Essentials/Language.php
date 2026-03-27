<?php

declare(strict_types=1);

namespace Canalizador\Shared\Shared\Domain\ValueObjects\Essentials;

enum Language: string
{
    case ENGLISH    = 'en';
    case PORTUGUESE = 'pt';
    case SPANISH    = 'es';
}
