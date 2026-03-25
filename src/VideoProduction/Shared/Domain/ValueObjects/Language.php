<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Shared\Domain\ValueObjects;

enum Language: string
{
    case ENGLISH    = 'en';
    case PORTUGUESE = 'pt';
    case SPANISH    = 'es';
}
