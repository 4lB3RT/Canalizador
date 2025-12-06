<?php

declare(strict_types=1);

namespace Canalizador\Video\Domain\Repositories;

interface VideoContentRetriever
{
    /**
     * Retrieves the video content (URL or file) for a given video ID.
     *
     * @param string $videoId The video generation ID
     * @return string The video content URL or file path
     * @throws \Canalizador\Video\Domain\Exceptions\VideoGenerationFailed
     * @throws \Illuminate\Http\Client\ConnectionException
     */
    public function retrieve(string $videoId): string;
}
