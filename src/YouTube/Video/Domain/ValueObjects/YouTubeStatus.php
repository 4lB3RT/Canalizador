<?php

declare(strict_types = 1);

namespace Helmreel\YouTube\Video\Domain\ValueObjects;

enum YouTubeStatus: string
{
    case Private   = 'private';
    case Public    = 'public';
    case Scheduled = 'scheduled';
    case Unlisted  = 'unlisted';
}
