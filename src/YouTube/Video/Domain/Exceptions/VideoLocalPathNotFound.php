<?php

declare(strict_types=1);

namespace Helmreel\YouTube\Video\Domain\Exceptions;

use Exception;

final class VideoLocalPathNotFound extends Exception
{
    public static function default(): self
    {
        return new self('Video local path not found');
    }
}
