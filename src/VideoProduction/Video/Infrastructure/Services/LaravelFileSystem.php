<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Video\Infrastructure\Services;

use Canalizador\VideoProduction\Video\Domain\Services\FileSystem;
use Illuminate\Support\Facades\File;

final class LaravelFileSystem implements FileSystem
{
    public function exists(string $path): bool
    {
        return File::exists($path);
    }

    public function size(string $path): int
    {
        return (int) File::size($path);
    }

    public function mimeType(string $path): string
    {
        return File::mimeType($path) ?: 'video/mp4';
    }

    /**
     * @return resource
     */
    public function openReadStream(string $path)
    {
        $handle = fopen($path, 'rb');
        if (!$handle) {
            throw new \RuntimeException("Could not open file: {$path}");
        }

        return $handle;
    }

    /**
     * @param resource $handle
     */
    public function readChunk($handle, int $length): string|false
    {
        return fread($handle, $length);
    }

    /**
     * @param resource $handle
     */
    public function close($handle): bool
    {
        return fclose($handle);
    }

    /**
     * @param resource $handle
     */
    public function eof($handle): bool
    {
        return feof($handle);
    }
}
