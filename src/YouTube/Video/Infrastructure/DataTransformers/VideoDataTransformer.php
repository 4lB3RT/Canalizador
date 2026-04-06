<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Infrastructure\DataTransformers;

use Canalizador\Shared\Shared\Domain\ValueObjects\Description;
use Canalizador\Shared\Shared\Domain\ValueObjects\Duration;
use Canalizador\Shared\Shared\Domain\ValueObjects\Essentials\DateTime;
use Canalizador\Shared\Shared\Domain\ValueObjects\LocalPath;
use Canalizador\Shared\Shared\Domain\ValueObjects\Title;
use Canalizador\Shared\Shared\Domain\ValueObjects\Url;
use Canalizador\YouTube\Metric\Domain\Entities\MetricCollection;
use Canalizador\YouTube\Transcription\Infrastructure\DataTransformer\TranscriptionDataTransformer;
use Canalizador\YouTube\Video\Domain\Entities\Video;
use Canalizador\YouTube\Video\Domain\ValueObjects\Category;
use Canalizador\YouTube\Video\Domain\ValueObjects\Id;
use Canalizador\YouTube\Video\Domain\ValueObjects\PlatformId;
use Canalizador\YouTube\Video\Domain\ValueObjects\YouTubeStatus;

class VideoDataTransformer
{
    public static function fromArray(array $data): Video
    {
        $publishedAt = new DateTime(\DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $data['published_at']));

        return new Video(
            id:             Id::fromString($data['id']),
            title:          Title::fromString($data['title']),
            publishedAt:    $publishedAt,
            duration:       new Duration((int) ($data['duration'] ?? 0)),
            metrics:        MetricCollection::fromArray($data['metrics']),
            category:       Category::tryFrom($data['category']),
            status:         YouTubeStatus::from($data['status']),
            url:            $data['url'] !== null ? Url::fromString($data['url']) : null,
            videoLocalPath: $data['video_local_path'] !== null ? LocalPath::fromString($data['video_local_path']) : null,
            audioLocalPath: $data['audio_local_path'] !== null ? LocalPath::fromString($data['audio_local_path']) : null,
            transcription:  $data['transcription'] !== null ? TranscriptionDataTransformer::transformArray($data['transcription']) : null,
            description:    isset($data['description']) ? new Description($data['description']) : null,
            platformId:     isset($data['platform_id']) ? PlatformId::fromString($data['platform_id']) : null,
            parentId:       isset($data['parent_id']) ? Id::fromString($data['parent_id']) : null,
        );
    }
}
