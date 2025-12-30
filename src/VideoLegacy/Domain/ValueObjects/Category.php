<?php

declare(strict_types=1);

namespace Canalizador\VideoLegacy\Domain\ValueObjects;

enum Category: string
{
    case VIDEO = 'video';
    case SHORT = 'short';
}
