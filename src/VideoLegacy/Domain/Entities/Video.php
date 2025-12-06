<?php

declare(strict_types = 1);

namespace Canalizador\VideoLegacy\Domain\Entities;

use Canalizador\Metric\Domain\Entities\MetricCollection;
use Canalizador\Shared\Domain\ValueObjects\DateTime;
use Canalizador\Shared\Domain\ValueObjects\LocalPath;
use Canalizador\Shared\Domain\ValueObjects\Url;
use Canalizador\Transcription\Domain\Entities\Transcription;
use Canalizador\VideoLegacy\Domain\ValueObjects\Category;
use Canalizador\VideoLegacy\Domain\ValueObjects\Title;
use Canalizador\VideoLegacy\Domain\ValueObjects\VideoId;

final class Video
{
    public function __construct(
        private readonly VideoId        $id,
        private readonly Title          $title,
        private readonly DateTime       $publishedAt,
        private MetricCollection        $metrics,
        private readonly Category       $category,
        private readonly ?Url           $url = null,
        private ?LocalPath     $videoLocalPath = null,
        private ?LocalPath     $audioLocalPath = null,
        private ?Transcription $transcription = null,
    ) {
    }

    public function id(): VideoId
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

    public function url(): ?Url
    {
        return $this->url;
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

    public function toArray(): array
    {
        return [
            'id'           => $this->id->value(),
            'title'        => $this->title->value(),
            'published_at' => $this->publishedAt->value()->format('Y-m-d H:i:s'),
            'category'     => $this->category->value,
            'metrics'      => $this->metrics->map(function ($metric) {
                return [
                    'name'  => $metric->name()->value(),
                    'type'  => $metric->type()->value(),
                    'value' => $metric->value()->value(),
                ];
            }),
            'transcription'    => $this->transcription?->toArray(),
            'url'              => $this->url?->value(),
            'video_local_path' => $this->videoLocalPath?->value(),
            'audio_local_path' => $this->audioLocalPath?->value(),
        ];
    }
}
