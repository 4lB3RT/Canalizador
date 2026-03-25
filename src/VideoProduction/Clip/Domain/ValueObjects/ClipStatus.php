<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Clip\Domain\ValueObjects;

enum ClipStatus: string
{
    case GENERATING = 'generating';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
}
