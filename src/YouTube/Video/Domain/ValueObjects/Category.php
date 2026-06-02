<?php

declare(strict_types = 1);

namespace Helmreel\YouTube\Video\Domain\ValueObjects;

enum Category: string
{
    case VIDEO = 'video';
    case SHORT = 'short';
}
