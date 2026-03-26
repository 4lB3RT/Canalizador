<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\VideoLegacy\Infrastructure\DataTransformers;

use Canalizador\Metric\Domain\Entities\MetricCollection;
use Canalizador\Shared\Domain\ValueObjects\DateTime;
use Canalizador\Shared\Domain\ValueObjects\LocalPath;
use Canalizador\Shared\Domain\ValueObjects\Url;
use Canalizador\Transcription\Infrastructure\DataTransformer\TranscriptionDataTransformer;
use Canalizador\VideoProduction\VideoLegacy\Domain\Entities\Video;
use Canalizador\VideoProduction\VideoLegacy\Domain\ValueObjects\Category;
use Canalizador\VideoProduction\VideoLegacy\Domain\ValueObjects\Title;
use Canalizador\VideoProduction\VideoLegacy\Domain\ValueObjects\VideoId;

class VideoDataTransformer
{
    public static function fromArray(array $data): Video
    {
        $publishedAt = new DateTime(\DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $data['published_at']));

        return new Video(
            id: VideoId::fromString($data['id']),
            title: Title::fromString($data['title']),
            publishedAt: $publishedAt,
            metrics: MetricCollection::fromArray($data['metrics']),
            category: Category::tryFrom($data['category']),
            url: $data['url']                         !== null ? Url::fromString($data['url']) : null,
            videoLocalPath: $data['video_local_path'] !== null ? LocalPath::fromString($data['video_local_path']) : null,
            audioLocalPath: $data['audio_local_path'] !== null ? LocalPath::fromString($data['audio_local_path']) : null,
            transcription: $data['transcription']     !== null ? TranscriptionDataTransformer::transformArray($data['transcription']) : null
        );
    }
}
