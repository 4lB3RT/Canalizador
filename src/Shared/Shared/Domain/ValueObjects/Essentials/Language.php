<?php

declare(strict_types=1);

namespace Helmreel\Shared\Shared\Domain\ValueObjects\Essentials;

enum Language: string
{
    case ENGLISH    = 'en';
    case PORTUGUESE = 'pt';
    case SPANISH    = 'es';
}
