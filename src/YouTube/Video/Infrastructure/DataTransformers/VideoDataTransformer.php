<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Infrastructure\DataTransformers;

use Canalizador\Shared\Domain\ValueObjects\DateTime;
use Canalizador\Shared\Domain\ValueObjects\LocalPath;
use Canalizador\Shared\Domain\ValueObjects\Url;
use Canalizador\YouTube\Metric\Domain\Entities\MetricCollection;
use Canalizador\YouTube\Transcription\Infrastructure\DataTransformer\TranscriptionDataTransformer;
use Canalizador\YouTube\Video\Domain\Entities\Video;
use Canalizador\YouTube\Video\Domain\ValueObjects\Category;
use Canalizador\YouTube\Video\Domain\ValueObjects\Id;
use Canalizador\YouTube\Video\Domain\ValueObjects\Title;

class VideoDataTransformer
{
    public static function fromArray(array $data): Video
    {
        $publishedAt = new DateTime(\DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $data['published_at']));

        return new Video(
            id:             Id::fromString($data['id']),
            title:          Title::fromString($data['title']),
            publishedAt:    $publishedAt,
            metrics:        MetricCollection::fromArray($data['metrics']),
            category:       Category::tryFrom($data['category']),
            url:            $data['url']              !== null ? Url::fromString($data['url']) : null,
            videoLocalPath: $data['video_local_path'] !== null ? LocalPath::fromString($data['video_local_path']) : null,
            audioLocalPath: $data['audio_local_path'] !== null ? LocalPath::fromString($data['audio_local_path']) : null,
            transcription:  $data['transcription']    !== null ? TranscriptionDataTransformer::transformArray($data['transcription']) : null,
        );
    }
}
