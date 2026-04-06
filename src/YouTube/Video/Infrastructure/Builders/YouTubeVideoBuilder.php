<?php

declare(strict_types = 1);

namespace Canalizador\YouTube\Video\Infrastructure\Builders;

use Canalizador\Shared\Shared\Domain\ValueObjects\Description;
use Canalizador\Shared\Shared\Domain\ValueObjects\Duration;
use Canalizador\Shared\Shared\Domain\ValueObjects\Essentials\DateTime;
use Canalizador\Shared\Shared\Domain\ValueObjects\Essentials\Language;
use Canalizador\Shared\Shared\Domain\ValueObjects\LocalPath;
use Canalizador\Shared\Shared\Domain\ValueObjects\Title;
use Canalizador\Shared\Shared\Domain\ValueObjects\Url;
use Canalizador\Shared\Video\Domain\Repositories\VideoMetadataGenerator;
use Canalizador\Shared\Video\Domain\ValueObjects\VideoMetadata;
use Canalizador\YouTube\Shared\Infrastructure\ClientAPI\YoutubeDataApiClient;
use Canalizador\YouTube\Transcription\Domain\Entities\Transcription;
use Canalizador\YouTube\Transcription\Infrastructure\DataTransformer\TranscriptionDataTransformer;
use Canalizador\YouTube\Video\Domain\Entities\Video;
use Canalizador\YouTube\Video\Domain\Entities\VideoCollection;
use Canalizador\YouTube\Video\Domain\Exceptions\VideoFragmentationFailed;
use Canalizador\YouTube\Video\Domain\Exceptions\VideoNotFound;
use Canalizador\YouTube\Video\Domain\Exceptions\YouTubeOperationFailed;
use Canalizador\YouTube\Video\Domain\Repositories\AudioExtractor;
use Canalizador\YouTube\Video\Domain\Repositories\VideoDownloader;
use Canalizador\YouTube\Video\Domain\Repositories\VideoFragmenter;
use Canalizador\YouTube\Video\Domain\Repositories\VideoRepository;
use Canalizador\YouTube\Video\Domain\Repositories\VideoTranscriber;
use Canalizador\YouTube\Video\Domain\ValueObjects\AudioPath;
use Canalizador\YouTube\Video\Domain\ValueObjects\Category;
use Canalizador\YouTube\Video\Domain\ValueObjects\Id;
use Canalizador\YouTube\Video\Domain\ValueObjects\PlatformId;
use Canalizador\YouTube\Video\Domain\ValueObjects\YouTubeStatus;
use Canalizador\YouTube\Video\Domain\ValueObjects\YouTubeVideoId;
use Canalizador\YouTube\Video\Infrastructure\DataTransformers\VideoDataTransformer;
use DateInterval;
use DateMalformedIntervalStringException;
use DateMalformedStringException;
use DateTimeImmutable;
use Throwable;

final class YouTubeVideoBuilder
{
    private Id             $id;
    private Title          $title;
    private DateTime       $publishedAt;
    private Duration       $duration;
    private Category       $category;

    private int  $segmentSeconds = 60;
    private ?int $maxFragments   = null;

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

    public function __construct(
        private readonly YoutubeDataApiClient   $youtubeClient,
        private readonly VideoRepository        $videoRepository,
        private readonly VideoDownloader        $videoDownloader,
        private readonly AudioExtractor         $audioExtractor,
        private readonly VideoTranscriber       $videoTranscriber,
        private readonly VideoMetadataGenerator $videoMetadataGenerator,
        private readonly VideoFragmenter        $videoFragmenter,
    ) {
    }

    /**
     * @throws DateMalformedStringException
     * @throws Throwable
     * @throws VideoNotFound
     * @throws DateMalformedIntervalStringException
     */
    public function fromYouTubeId(string $id): self
    {
        $videoId = Id::fromString($id);

        try {
            $this->cachedVideo = $this->videoRepository->findById($videoId);

            return $this;
        } catch (VideoNotFound) {
        }

        $data = $this->youtubeClient->getVideoById($id);

        if (!$data) {
            throw VideoNotFound::withId($id);
        }

        $durationMinutes = 0;
        if (isset($data['contentDetails']['duration'])) {
            $interval        = new DateInterval($data['contentDetails']['duration']);
            $totalSeconds    = $interval->h * 3600 + $interval->i * 60 + $interval->s;
            $durationMinutes = (int) ceil($totalSeconds / 60);
        }

        $this->id          = $videoId;
        $this->platformId  = PlatformId::fromString($id);
        $this->parentId    = null;
        $this->status      = YouTubeStatus::Public;
        $this->title       = Title::fromString($data['snippet']['title']);
        $this->publishedAt = new DateTime(new DateTimeImmutable($data['snippet']['publishedAt']));
        $this->duration    = new Duration($durationMinutes);
        $this->category    = Category::VIDEO;
        $this->url         = Url::fromString('https://www.youtube.com/watch?v=' . $id);

        return $this;
    }

    /**
     * @throws YouTubeOperationFailed
     */
    public function withDownload(): self
    {
        if ($this->cachedVideo !== null) {
            return $this;
        }

        $this->videoLocalPath = $this->videoDownloader->download(new YouTubeVideoId($this->id->value()));

        return $this;
    }

    public function withAudio(): self
    {
        if ($this->cachedVideo !== null) {
            return $this;
        }

        $this->audioPath      = $this->audioExtractor->extract($this->videoLocalPath);
        $this->audioLocalPath = LocalPath::fromString($this->audioPath->value());

        return $this;
    }

    public function withTranscription(): self
    {
        if ($this->cachedVideo !== null) {
            return $this;
        }

        $segments            = $this->videoTranscriber->transcribe($this->audioPath);
        $this->transcription = $this->buildTranscription($segments);

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
        $this->platformId     = null;
        $this->parentId       = $parent->id();
        $this->status         = YouTubeStatus::Scheduled;
        $this->id             = Id::fromString(md5($fragmentPath->value()));
        $this->title          = Title::fromString($metadata->title->value());
        $this->description    = new Description($metadata->description->value());
        $this->publishedAt    = $parent->publishedAt();
        $this->duration       = new Duration(1);
        $this->category       = Category::SHORT;
        $this->videoLocalPath = $fragmentPath;
        $this->audioLocalPath = null;
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

    public function withSegmentDuration(int $seconds): self
    {
        $this->segmentSeconds = $seconds;

        return $this;
    }

    public function withMaxFragments(int $max): self
    {
        $this->maxFragments = $max;

        return $this;
    }

    /* @throws VideoFragmentationFailed */
    public function buildShorts(): VideoCollection
    {
        $parent    = $this->build();
        $fragments = $this->videoFragmenter->fragment($parent->videoLocalPath(), $this->segmentSeconds);

        if ($this->maxFragments !== null) {
            $fragments = array_slice($fragments, 0, $this->maxFragments);
        }

        $shorts = [];

        foreach ($fragments as $index => $fragmentPath) {
            $this->fromFragment($parent, $fragmentPath, $index);
            $shorts[] = $this->build();
        }

        return new VideoCollection($shorts);
    }

    public function build(): Video
    {
        if ($this->cachedVideo !== null) {
            return $this->cachedVideo;
        }

        return VideoDataTransformer::fromArray([
            'id'               => $this->id->value(),
            'title'            => $this->title->value(),
            'published_at'     => $this->publishedAt->value()->format('Y-m-d H:i:s'),
            'duration'         => $this->duration->value(),
            'metrics'          => [],
            'category'         => $this->category->value,
            'status'           => $this->status->value,
            'url'              => $this->url?->value(),
            'video_local_path' => $this->videoLocalPath?->value(),
            'audio_local_path' => $this->audioLocalPath?->value(),
            'transcription'    => $this->transcription?->toArray(),
            'description'      => $this->description?->value(),
            'platform_id'      => $this->platformId?->value(),
            'parent_id'        => $this->parentId?->value(),
        ]);
    }

    private function buildTranscription(array $segments): Transcription
    {
        return TranscriptionDataTransformer::transformArray([
            'videoId'   => $this->id->value(),
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
