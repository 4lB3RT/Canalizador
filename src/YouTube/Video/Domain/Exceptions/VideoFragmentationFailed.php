<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Domain\Exceptions;

use Exception;

final class VideoFragmentationFailed extends Exception
{
    public static function emptyResult(string $path): self
    {
        return new self("Video fragmentation produced no segments for: {$path}");
    }

    public static function commandFailed(string $details): self
    {
        return new self("Video fragmentation command failed: {$details}");
    }
}
