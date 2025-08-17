<?php

declare(strict_types = 1);

namespace Canalizador\Recommendation\Domain\ValueObjects;

enum Type: string
{
    case CHANGE_TITLE       = 'change_title';
    case CHANGE_DESCRIPTION = 'change_description';
    case CHANGE_TAGS        = 'change_tags';
}
