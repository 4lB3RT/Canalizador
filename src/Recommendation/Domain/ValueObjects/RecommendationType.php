<?php

declare(strict_types = 1);

namespace Src\Recommendation\Domain\ValueObjects;

enum RecommendationType: string
{
    case TITLE       = 'title';
    case STRUCTURE   = 'structure';
    case ENGAGEMENT  = 'engagement';
    case DESCRIPTION = 'description';
    case TAGS        = 'tags';
}
