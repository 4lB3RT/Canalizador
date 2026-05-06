<?php

declare(strict_types = 1);

namespace Canalizador\YouTube\Video\Infrastructure\Builders;

use Canalizador\Shared\Shared\Domain\Services\Clock;
use Canalizador\Shared\Shared\Domain\ValueObjects\Description;
use Canalizador\Shared\Shared\Domain\ValueObjects\Duration;
use Canalizador\Shared\Shared\Domain\ValueObjects\Essentials\DateTime;
use Canalizador\Shared\Shared\Domain\ValueObjects\Essentials\Language;
use Canalizador\Shared\Shared\Domain\ValueObjects\LocalPath;
use Canalizador\Shared\Shared\Domain\ValueObjects\Title;
use Canalizador\Shared\Shared\Domain\ValueObjects\Url;
use Canalizador\Shared\Video\Domain\Repositories\VideoMetadataGenerator;
use Canalizador\Shared\Video\Domain\ValueObjects\VideoMetadata;
use Canalizador\YouTube\Channel\Domain\ValueObjects\ChannelId;
use Canalizador\YouTube\Transcription\Domain\Entities\Transcription;
use Canalizador\YouTube\Transcription\Infrastructure\DataTransformer\TranscriptionDataTransformer;
use Canalizador\YouTube\Video\Domain\Entities\Video;
use Canalizador\YouTube\Video\Domain\Entities\VideoCollection;
use Canalizador\YouTube\Video\Domain\Exceptions\VideoNotFound;
use Canalizador\YouTube\Video\Domain\Exceptions\YouTubeOperationFailed;
use Canalizador\YouTube\Video\Domain\Repositories\AudioExtractor;
use Canalizador\YouTube\Video\Domain\Repositories\VideoDownloader;
use Canalizador\YouTube\Video\Domain\Repositories\VideoTranscriber;
use Canalizador\YouTube\Video\Domain\ValueObjects\AudioPath;
use Canalizador\YouTube\Video\Domain\ValueObjects\Category;
use Canalizador\YouTube\Video\Domain\ValueObjects\Id;
use Canalizador\YouTube\Video\Domain\ValueObjects\PlatformId;
use Canalizador\YouTube\Video\Domain\ValueObjects\YouTubeStatus;
use Canalizador\YouTube\Video\Domain\ValueObjects\YouTubeVideoId;
use Canalizador\YouTube\Video\Infrastructure\Repositories\Eloquent\EloquentVideoRepository;
use Canalizador\YouTube\Video\Infrastructure\Repositories\YouTube\YoutubeVideoRepository;

final class YouTubeVideoBuilder
{
    private Id             $id;
    private Title          $title;
    private DateTime       $publishedAt;
    private Duration       $duration;
    private Category       $category;

    private int  $segmentSeconds = 60;
    private ?int $maxFragments   = null;

    private ChannelId $channelId;
    private ?Url         $url             = null;
    private ?LocalPath   $videoLocalPath  = null;
    private ?LocalPath   $audioLocalPath  = null;
    private ?AudioPath   $audioPath       = null;
    private ?Transcription $transcription = null;
    private ?Description $description     = null;
    private ?PlatformId  $platformId      = null;
    private ?Id          $parentId        = null;
    private YouTubeStatus $status         = YouTubeStatus::Private;
    private ?Video       $cachedVideo     = null;
    private ?VideoMetadata $lastMetadata  = null;
    private ?VideoCollection $shorts      = null;

    public function __construct(
        private readonly EloquentVideoRepository $localRepository,
        private readonly YoutubeVideoRepository  $youtubeRepository,
        private readonly VideoDownloader         $videoDownloader,
        private readonly AudioExtractor          $audioExtractor,
        private readonly VideoTranscriber        $videoTranscriber,
        private readonly VideoMetadataGenerator  $videoMetadataGenerator,
        private readonly Clock                   $clock,
    ) {
    }

    /* @throws VideoNotFound */
    public function fromId(Id $id): self
    {
        return $this->loadVideo(
            $this->localRepository->findById($id)
        );
    }

    /* @throws VideoNotFound */
    public function fromPlatformId(PlatformId $platformId): self
    {
        return $this->loadVideo(
            $this->youtubeRepository->findByPlatformId($platformId)
        );
    }

    /* @throws YouTubeOperationFailed */
    public function withDownload(): self
    {
        if ($this->cachedVideo?->videoLocalPath() !== null) {
            return $this;
        }

        $youtubeVideoId = $this->cachedVideo !== null
            ? new YouTubeVideoId($this->cachedVideo->platformId()->value())
            : new YouTubeVideoId($this->id->value());
        $localPath            = $this->videoDownloader->download($youtubeVideoId);
        $this->videoLocalPath = $localPath;

        if ($this->cachedVideo !== null) {
            $this->cachedVideo->updateVideoLocalPath($localPath);
        }

        return $this;
    }

    public function withAudio(): self
    {
        if ($this->cachedVideo?->audioLocalPath() !== null) {
            return $this;
        }

        $videoLocalPath       = $this->cachedVideo?->videoLocalPath() ?? $this->videoLocalPath;
        $this->audioPath      = $this->audioExtractor->extract($videoLocalPath);
        $audioLocalPath       = LocalPath::fromString($this->audioPath->value());
        $this->audioLocalPath = $audioLocalPath;

        if ($this->cachedVideo !== null) {
            $this->cachedVideo->updateAudioLocalPath($audioLocalPath);
        }

        return $this;
    }

    public function withTranscription(): self
    {
        if ($this->cachedVideo?->transcription() !== null) {
            return $this;
        }

        $segments            = $this->videoTranscriber->transcribe($this->audioPath);
        $transcription       = $this->buildTranscription($segments);
        $this->transcription = $transcription;

        if ($this->cachedVideo !== null) {
            $this->cachedVideo->updateTranscription($transcription);
        }

        return $this;
    }

    public function fromFragment(Video $parent, LocalPath $fragmentPath, int $index): self
    {
        $startSeconds = $index * $this->segmentSeconds;
        $endSeconds   = $startSeconds + $this->segmentSeconds;

        $fragmentSentences = $parent->transcription()?->sentences()->sentencesInRange(
            (float) $startSeconds,
            (float) $endSeconds
        );

        $fragmentText = $fragmentSentences?->toText() ?: $parent->title()->value();
        $metadata     = $this->videoMetadataGenerator->generate($fragmentText);

        $this->cachedVideo    = null;
        $this->lastMetadata   = $metadata;
        $this->shorts         = null;
        $this->id             = Id::generate();
        $this->channelId      = $parent->channelId();
        $this->title          = Title::fromString($metadata->title->value());
        $this->publishedAt    = $parent->publishedAt();
        $this->duration       = new Duration(1);
        $this->category       = Category::SHORT;
        $this->status         = YouTubeStatus::Scheduled;
        $this->platformId     = null;
        $this->url            = null;
        $this->videoLocalPath = $fragmentPath;
        $this->audioLocalPath = null;
        $this->description    = new Description($metadata->description->value());
        $this->parentId       = $parent->id();
        $this->transcription  = $fragmentSentences !== null && $parent->transcription() !== null
            ? TranscriptionDataTransformer::transformArray([
                'videoId'   => $this->id->value(),
                'text'      => $fragmentText,
                'language'  => $parent->transcription()->language()->value,
                'sentences' => array_map(
                    static fn ($s) => ['text' => $s->text()->value(), 'start' => $s->start()->value(), 'end' => $s->end()->value()],
                    $fragmentSentences->items()
                ),
            ])
            : null;

        return $this;
    }

    public function build(): Video
    {
        return Video::create(
            id:             $this->id,
            channelId:      $this->channelId,
            title:          $this->title,
            publishedAt:    $this->publishedAt,
            duration:       $this->duration,
            category:       $this->category,
            status:         $this->status,
            clock:          $this->clock,
            shorts:         $this->shorts ?? new VideoCollection([]),
            platformId:     $this->platformId,
            url:            $this->url,
            videoLocalPath: $this->videoLocalPath,
            audioLocalPath: $this->audioLocalPath,
            transcription:  $this->transcription,
            description:    $this->description,
            parentId:       $this->parentId,
        );
    }

    private function loadVideo(Video $video): self
    {
        $this->cachedVideo    = $video;
        $this->id             = $video->id();
        $this->channelId      = $video->channelId();
        $this->title          = $video->title();
        $this->publishedAt    = $video->publishedAt();
        $this->duration       = $video->duration();
        $this->category       = $video->category();
        $this->status         = $video->status();
        $this->platformId     = $video->platformId();
        $this->url            = $video->url();
        $this->videoLocalPath = $video->videoLocalPath();
        $this->audioLocalPath = $video->audioLocalPath();
        $this->transcription  = $video->transcription();
        $this->description    = $video->description();
        $this->shorts         = $video->shorts();

        return $this;
    }

    private function buildTranscription(array $segments): Transcription
    {
        return TranscriptionDataTransformer::transformArray([
            'videoId'   => $this->cachedVideo?->id()->value() ?? $this->id->value(),
            'text'      => implode(' ', array_column($segments, 'text')),
            'language'  => Language::SPANISH->value,
            'sentences' => array_map(
                static fn (array $s) => [
                    'text'  => trim($s['text']),
                    'start' => $s['start'],
                    'end'   => $s['end'],
                ],
                $segments
            ),
        ]);
    }
}
