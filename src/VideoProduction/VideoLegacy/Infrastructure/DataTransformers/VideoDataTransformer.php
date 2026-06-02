<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\VideoLegacy\Infrastructure\DataTransformers;

use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\DateTime;
use Helmreel\Shared\Shared\Domain\ValueObjects\LocalPath;
use Helmreel\Shared\Shared\Domain\ValueObjects\Url;
use Helmreel\Shared\Video\Domain\ValueObjects\Title;
use Helmreel\VideoProduction\VideoLegacy\Domain\Entities\Video;
use Helmreel\VideoProduction\VideoLegacy\Domain\ValueObjects\VideoId;
use Helmreel\YouTube\Metric\Domain\Entities\MetricCollection;
use Helmreel\YouTube\Transcription\Infrastructure\DataTransformer\TranscriptionDataTransformer;
use Helmreel\YouTube\Video\Domain\ValueObjects\Category;

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
