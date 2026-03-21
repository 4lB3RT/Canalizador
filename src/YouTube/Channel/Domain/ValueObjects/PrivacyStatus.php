<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Channel\Domain\ValueObjects;

enum PrivacyStatus: string
{
    case PRIVATE = 'private';
    case UNLISTED = 'unlisted';
    case PUBLIC = 'public';
}

