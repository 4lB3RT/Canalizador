<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\VideoLegacy\Infrastructure\DataTransformers;

use Canalizador\Shared\Shared\Domain\ValueObjects\Essentials\DateTime;
use Canalizador\Shared\Shared\Domain\ValueObjects\Essentials\LocalPath;
use Canalizador\Shared\Shared\Domain\ValueObjects\Essentials\Url;
use Canalizador\VideoProduction\VideoLegacy\Domain\Entities\Video;
use Canalizador\Shared\Video\Domain\ValueObjects\Title;
use Canalizador\VideoProduction\VideoLegacy\Domain\ValueObjects\VideoId;
use Canalizador\YouTube\Metric\Domain\Entities\MetricCollection;
use Canalizador\YouTube\Transcription\Infrastructure\DataTransformer\TranscriptionDataTransformer;
use Canalizador\YouTube\Video\Domain\ValueObjects\Category;

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
