<?php

declare(strict_types = 1);

namespace Canalizador\YouTube\Video\Domain\Entities;

use Canalizador\Shared\Shared\Domain\AggregateRoot;
use Canalizador\Shared\Shared\Domain\Services\Clock;
use Canalizador\Shared\Shared\Domain\ValueObjects\Description;
use Canalizador\Shared\Shared\Domain\ValueObjects\Duration;
use Canalizador\Shared\Shared\Domain\ValueObjects\Essentials\DateTime;
use Canalizador\Shared\Shared\Domain\ValueObjects\LocalPath;
use Canalizador\Shared\Shared\Domain\ValueObjects\Title;
use Canalizador\Shared\Shared\Domain\ValueObjects\Url;
use Canalizador\YouTube\Channel\Domain\ValueObjects\ChannelId;
use Canalizador\YouTube\Metric\Domain\Entities\MetricCollection;
use Canalizador\YouTube\Transcription\Domain\Entities\Transcription;
use Canalizador\YouTube\Video\Domain\Events\ShortGenerated;
use Canalizador\YouTube\Video\Domain\Events\VideoCreated;
use Canalizador\YouTube\Video\Domain\ValueObjects\Category;
use Canalizador\YouTube\Video\Domain\ValueObjects\Id;
use Canalizador\YouTube\Video\Domain\ValueObjects\PlatformId;
use Canalizador\YouTube\Video\Domain\ValueObjects\YouTubeStatus;
use Canalizador\YouTube\Video\Domain\ValueObjects\YouTubeVideoId;

final class Video extends AggregateRoot
{
    /** @var YouTubeVideoId[] */
    private array $publishedShortIds = [];

    public function __construct(
        private readonly Id            $id,
        private readonly Title         $title,
        private readonly DateTime      $publishedAt,
        private readonly Duration      $duration,
        private MetricCollection       $metrics,
        private readonly Category        $category,
        private readonly YouTubeStatus   $status,
        private ChannelId                $channelId,
        private ?Url                     $url = null,
        private ?LocalPath               $videoLocalPath = null,
        private ?LocalPath               $audioLocalPath = null,
        private ?Transcription           $transcription = null,
        private readonly ?Description    $description = null,
        private ?PlatformId              $platformId = null,
        private readonly ?Id             $parentId = null,
        private readonly VideoCollection $shorts,
    ) {
    }

    public static function create(
        Id              $id,
        ChannelId       $channelId,
        Title           $title,
        DateTime        $publishedAt,
        Duration        $duration,
        Category        $category,
        YouTubeStatus   $status,
        Clock           $clock,
        VideoCollection $shorts,
        ?PlatformId     $platformId = null,
        ?Url            $url = null,
        ?LocalPath      $videoLocalPath = null,
        ?LocalPath      $audioLocalPath = null,
        ?Transcription  $transcription = null,
        ?Description    $description = null,
        ?Id             $parentId = null,
    ): self
    {
        $video = new self(
            id: $id,
            title: $title,
            publishedAt: $publishedAt,
            duration: $duration,
            metrics: MetricCollection::fromArray([]),
            category: $category,
            status: $status,
            channelId: $channelId,
            url: $url,
            videoLocalPath: $videoLocalPath,
            audioLocalPath: $audioLocalPath,
            transcription: $transcription,
            description: $description,
            platformId: $platformId,
            parentId: $parentId,
            shorts: $shorts,
        );

        if ($platformId !== null) {
            $video->recordEvent(new VideoCreated($id->value(), $platformId->value(), $clock->now()));
        }

        return $video;
    }

    public function id(): Id
    {
        return $this->id;
    }

    public function title(): Title
    {
        return $this->title;
    }

    public function publishedAt(): DateTime
    {
        return $this->publishedAt;
    }

    public function metrics(): MetricCollection
    {
        return $this->metrics;
    }

    public function category(): Category
    {
        return $this->category;
    }

    public function status(): YouTubeStatus
    {
        return $this->status;
    }

    public function updateMetrics(MetricCollection $metrics): void
    {
        $this->metrics = $metrics;
    }

    public function transcription(): ?Transcription
    {
        return $this->transcription;
    }

    public function updateTranscription(Transcription $transcription): void
    {
        $this->transcription = $transcription;
    }

    public function description(): ?Description
    {
        return $this->description;
    }

    public function channelId(): ChannelId
    {
        return $this->channelId;
    }

    public function updateChannelId(ChannelId $channelId): void
    {
        $this->channelId = $channelId;
    }

    public function url(): ?Url
    {
        return $this->url;
    }

    public function updateUrl(Url $url): void
    {
        $this->url = $url;
    }

    public function videoLocalPath(): ?LocalPath
    {
        return $this->videoLocalPath;
    }

    public function updateVideoLocalPath(LocalPath $videoLocalPath): void
    {
        $this->videoLocalPath = $videoLocalPath;
    }

    public function audioLocalPath(): ?LocalPath
    {
        return $this->audioLocalPath;
    }

    public function updateAudioLocalPath(LocalPath $audioLocalPath): void
    {
        $this->audioLocalPath = $audioLocalPath;
    }

    public function duration(): Duration
    {
        return $this->duration;
    }

    public function addShort(Video $short, Clock $clock): void
    {
        $this->shorts->add($short);
        $this->recordEvent(new ShortGenerated($short->id()->value(), $this->id->value(), $clock->now()));
    }

    public function shorts(): VideoCollection
    {
        return $this->shorts;
    }

    public function addPublishedShortId(YouTubeVideoId $id): void
    {
        $this->publishedShortIds[] = $id;
    }

    /** @return YouTubeVideoId[] */
    public function publishedShortIds(): array
    {
        return $this->publishedShortIds;
    }

    public function platformId(): ?PlatformId
    {
        return $this->platformId;
    }

    public function updatePlatformId(PlatformId $id): void
    {
        $this->platformId = $id;
    }

    public function parentId(): ?Id
    {
        return $this->parentId;
    }

    public function toArray(): array
    {
        return [
            'id'           => $this->id->value(),
            'title'        => $this->title->value(),
            'published_at' => $this->publishedAt->value()->format('Y-m-d H:i:s'),
            'category'     => $this->category->value,
            'status' => $this->status->value,
            'metrics'      => $this->metrics->map(function ($metric) {
                return [
                    'name'  => $metric->name()->value(),
                    'type'  => $metric->type()->value(),
                    'value' => $metric->value()->value(),
                ];
            }),
            'channel_id' => $this->channelId->value(),
            'transcription'    => $this->transcription?->toArray(),
            'url'              => $this->url?->value(),
            'video_local_path' => $this->videoLocalPath?->value(),
            'audio_local_path' => $this->audioLocalPath?->value(),
            'duration' => $this->duration->value(),
            'description' => $this->description?->value(),
            'platform_id' => $this->platformId?->value(),
            'parent_id' => $this->parentId?->value(),
        ];
    }

    public function created(): void
    {
        $this->recordEvent(new VideoCreated($this->id->value(), $this->platformId->value(), DateTime::now()));
    }
}
