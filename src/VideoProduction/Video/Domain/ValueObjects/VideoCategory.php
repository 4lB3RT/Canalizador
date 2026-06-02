<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Video\Domain\ValueObjects;

enum VideoCategory: string
{
    case GAMING = 'gaming';
    case METEOROLOGY = 'meteorology';
}

