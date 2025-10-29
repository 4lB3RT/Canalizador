<?php

declare(strict_types = 1);

namespace Canalizador\Video\Infrastructure\DataTransformers;

use Canalizador\Metric\Domain\Entities\MetricCollection;
use Canalizador\Shared\Domain\ValueObjects\DateTime;
use Canalizador\Shared\Domain\ValueObjects\LocalPath;
use Canalizador\Shared\Domain\ValueObjects\Url;
use Canalizador\Transcription\Infrastructure\DataTransformer\TranscriptionDataTransformer;
use Canalizador\Video\Domain\Entities\Video;
use Canalizador\Video\Domain\ValueObjects\Category;
use Canalizador\Video\Domain\ValueObjects\Title;
use Canalizador\Video\Domain\ValueObjects\VideoId;

class VideoDataTransformer
{
    public static function fromArray(array $data): Video
    {
        $publishedAt = new DateTime(\DateTimeImmutable::createFromFormat('Y-m-d H:i:s.u', $data['published_at']['date']));

        return new Video(
            id: VideoId::fromString($data['id']),
            title: Title::fromString($data['title']),
            publishedAt: $publishedAt,
            metrics: MetricCollection::fromArray($data['metrics']),
            category: Category::fromString($data['category']),
            url: $data['url'] !== null ? Url::fromString($data['url']) : null,
            videoLocalPath: LocalPath::fromString($data['video_local_path']),
            audioLocalPath: $data['audio_local_path'] !== null ? LocalPath::fromString($data['audio_local_path']) : null,
            transcription: $data['transcription']     !== null ? TranscriptionDataTransformer::transformArray($data['transcription']) : null
        );
    }
}
