<?php

declare(strict_types=1);

namespace Canalizador\Video\Domain\Services;

interface FileSystem
{
    public function exists(string $path): bool;

    public function size(string $path): int;

    public function mimeType(string $path): string;

    /**
     * @return resource
     */
    public function openReadStream(string $path);

    /**
     * @param resource $handle
     */
    public function readChunk($handle, int $length): string|false;

    /**
     * @param resource $handle
     */
    public function close($handle): bool;

    /**
     * @param resource $handle
     */
    public function eof($handle): bool;
}
