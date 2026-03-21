<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Domain\Entities;

use Canalizador\YouTube\Video\Domain\ValueObjects\AudioPath;
use Canalizador\YouTube\Video\Domain\ValueObjects\Id;
use Canalizador\YouTube\Video\Domain\ValueObjects\LocalPath;
use Canalizador\YouTube\Video\Domain\ValueObjects\PublishedAt;
use Canalizador\YouTube\Video\Domain\ValueObjects\Title;
use Canalizador\YouTube\Video\Domain\ValueObjects\Url;

final class Video
{
    public function __construct(
        private readonly Id          $id,
        private readonly Title       $title,
        private readonly PublishedAt $publishedAt,
        private readonly Url         $url,
        private ?LocalPath           $localPath = null,
        private ?AudioPath           $audioPath = null,
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

    public function publishedAt(): PublishedAt
    {
        return $this->publishedAt;
    }

    public function url(): Url
    {
        return $this->url;
    }

    public function localPath(): ?LocalPath
    {
        return $this->localPath;
    }

    public function updateLocalPath(LocalPath $path): void
    {
        $this->localPath = $path;
    }

    public function audioPath(): ?AudioPath
    {
        return $this->audioPath;
    }

    public function updateAudioPath(AudioPath $path): void
    {
        $this->audioPath = $path;
    }

    public function toArray(): array
    {
        return [
            'id'           => $this->id->value(),
            'title'        => $this->title->value(),
            'published_at' => $this->publishedAt->format('Y-m-d H:i:s'),
            'url'          => $this->url->value(),
            'local_path'   => $this->localPath?->value(),
            'audio_path'   => $this->audioPath?->value(),
        ];
    }
}
