<?php

declare(strict_types = 1);

namespace Canalizador\YouTube\Video\Domain\Entities;

use Canalizador\Shared\Domain\ValueObjects\DateTime;
use Canalizador\Shared\Domain\ValueObjects\LocalPath;
use Canalizador\Shared\Domain\ValueObjects\Url;
use Canalizador\YouTube\Metric\Domain\Entities\MetricCollection;
use Canalizador\YouTube\Transcription\Domain\Entities\Transcription;
use Canalizador\YouTube\Video\Domain\ValueObjects\Category;
use Canalizador\YouTube\Video\Domain\ValueObjects\Id;
use Canalizador\YouTube\Video\Domain\ValueObjects\Title;

final class Video
{
    public function __construct(
        private readonly Id       $id,
        private readonly Title    $title,
        private readonly DateTime $publishedAt,
        private MetricCollection  $metrics,
        private readonly Category $category,
        private readonly ?Url     $url = null,
        private ?LocalPath        $videoLocalPath = null,
        private ?LocalPath        $audioLocalPath = null,
        private ?Transcription    $transcription = null,
    ) {
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
