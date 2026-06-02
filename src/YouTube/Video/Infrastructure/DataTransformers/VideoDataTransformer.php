<?php

declare(strict_types = 1);

namespace Helmreel\YouTube\Video\Infrastructure\DataTransformers;

use Helmreel\Shared\Shared\Domain\ValueObjects\Description;
use Helmreel\Shared\Shared\Domain\ValueObjects\Duration;
use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\DateTime;
use Helmreel\Shared\Shared\Domain\ValueObjects\LocalPath;
use Helmreel\Shared\Shared\Domain\ValueObjects\Title;
use Helmreel\Shared\Shared\Domain\ValueObjects\Url;
use Helmreel\YouTube\Channel\Domain\ValueObjects\ChannelId;
use Helmreel\YouTube\Metric\Domain\Entities\MetricCollection;
use Helmreel\YouTube\Transcription\Infrastructure\DataTransformer\TranscriptionDataTransformer;
use Helmreel\YouTube\Video\Domain\Entities\Video;
use Helmreel\YouTube\Video\Domain\Entities\VideoCollection;
use Helmreel\YouTube\Video\Domain\ValueObjects\Category;
use Helmreel\YouTube\Video\Domain\ValueObjects\Id;
use Helmreel\YouTube\Video\Domain\ValueObjects\PlatformId;
use Helmreel\YouTube\Video\Domain\ValueObjects\YouTubeStatus;

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
            channelId:      ChannelId::fromString($data['channel_id']),
            url:            $data['url']              !== null ? Url::fromString($data['url']) : null,
            videoLocalPath: $data['video_local_path'] !== null ? LocalPath::fromString($data['video_local_path']) : null,
            audioLocalPath: $data['audio_local_path'] !== null ? LocalPath::fromString($data['audio_local_path']) : null,
            transcription:  $data['transcription']    !== null ? TranscriptionDataTransformer::transformArray($data['transcription']) : null,
            description:    isset($data['description']) ? new Description($data['description']) : null,
            platformId:     isset($data['platform_id']) ? PlatformId::fromString($data['platform_id']) : null,
            parentId:       isset($data['parent_id']) ? Id::fromString($data['parent_id']) : null,
            shorts:         new VideoCollection(
                array_map(fn (array $short) => self::fromArray($short), $data['shorts'] ?? [])
            ),
        );
    }
}
