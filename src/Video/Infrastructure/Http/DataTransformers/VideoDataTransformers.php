<?php

declare(strict_types=1);

namespace Canalizador\Video\Infrastructure\Http\DataTransformers;

use Canalizador\Video\Domain\Entities\Video;

class VideoDataTransformers
{
    public static function transformVideoFromArray(array $videoData): Video
    {
        return new Video(
            $videoData['id'],
            $videoData['title'],
            $videoData['publishedAt'],
            $videoData['metrics'],
            $videoData['category'],
            $videoData['url'],
            $videoData['localPath'],
            $videoData['transcription']
        );
    }

}
