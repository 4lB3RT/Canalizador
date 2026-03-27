<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Application\UseCases\GenerateShorts;

use Canalizador\Shared\Shared\Domain\ValueObjects\Essentials\Language;
use Canalizador\Shared\Video\Domain\Repositories\VideoMetadataGenerator;
use Canalizador\YouTube\Transcription\Domain\Collections\WordCollection;
use Canalizador\YouTube\Transcription\Domain\Entities\Transcription;
use Canalizador\YouTube\Transcription\Domain\ValueObjects\EndTime;
use Canalizador\YouTube\Transcription\Domain\ValueObjects\StartTime;
use Canalizador\YouTube\Transcription\Domain\ValueObjects\Text as TranscriptionText;
use Canalizador\YouTube\Transcription\Domain\ValueObjects\Word;
use Canalizador\YouTube\Video\Domain\Exceptions\VideoNotFound;
use Canalizador\YouTube\Video\Domain\Exceptions\YouTubeOperationFailed;
use Canalizador\YouTube\Video\Domain\Factories\VideoPublisherFactory;
use Canalizador\YouTube\Video\Domain\Repositories\AudioExtractor;
use Canalizador\YouTube\Video\Domain\Repositories\VideoDownloader;
use Canalizador\YouTube\Video\Domain\Repositories\VideoFragmenter;
use Canalizador\YouTube\Video\Domain\Repositories\VideoRepository;
use Canalizador\YouTube\Video\Domain\Repositories\VideoTranscriber;
use Canalizador\YouTube\Video\Domain\ValueObjects\Id;
use Canalizador\Youtube\Video\Domain\ValueObjects\Id as TranscriptionId;
use Canalizador\YouTube\Video\Domain\ValueObjects\VideoToPublish;
use Canalizador\YouTube\Video\Domain\ValueObjects\YouTubeVideoId;

final readonly class GenerateShorts
{
    private const int SEGMENT_SECONDS = 60;

    public function __construct(
        private VideoRepository        $videoRepository,
        private VideoDownloader        $videoDownloader,
        private AudioExtractor         $audioExtractor,
        private VideoTranscriber       $videoTranscriber,
        private VideoFragmenter        $videoFragmenter,
        private VideoMetadataGenerator $videoMetadataGenerator,
        private VideoPublisherFactory  $videoPublisherFactory,
    ) {
    }

    /**
     * @throws VideoNotFound
     * @throws YouTubeOperationFailed
     */
    public function execute(GenerateShortsRequest $request): GenerateShortsResponse
    {
        $youtubeId = new YouTubeVideoId($request->videoYoutubeId);
        $video     = $this->videoRepository->findById(new Id($request->videoYoutubeId));

        $videoPath = $this->videoDownloader->download($youtubeId);
        $video->updateVideoLocalPath($videoPath);
        $this->videoRepository->save($video);

        $audioPath = $this->audioExtractor->extract($videoPath);
        $video->updateAudioLocalPath($audioPath);
        $this->videoRepository->save($video);

        $segments      = $this->videoTranscriber->transcribe($audioPath);
        $transcription = $this->buildTranscription($request->videoYoutubeId, $segments);
        $video->updateTranscription($transcription);
        $this->videoRepository->save($video);

        $fragments = $this->videoFragmenter->fragment($videoPath, self::SEGMENT_SECONDS);
        $publisher = $this->videoPublisherFactory->create('youtube');

        foreach ($fragments as $index => $fragmentPath) {
            $startSeconds = $index * self::SEGMENT_SECONDS;
            $endSeconds   = $startSeconds + self::SEGMENT_SECONDS;

            $fragmentWords = $transcription->words()->wordsInRange((float) $startSeconds, (float) $endSeconds);
            $fragmentText  = $fragmentWords->toText();

            $metadata = $this->videoMetadataGenerator->generate($fragmentText ?: $video->title()->value());

            $videoToPublish = new VideoToPublish(
                localPath:   $fragmentPath,
                title:       $metadata->title->value(),
                description: $metadata->description->value(),
            );

            $publishedId = $publisher->publish($videoToPublish);
            $video->addPublishedShortId(new YouTubeVideoId($publishedId));
            $this->videoRepository->save($video);
        }

        return new GenerateShortsResponse(
            publishedShortIds: array_map(
                static fn (YouTubeVideoId $id) => $id->value(),
                $video->publishedShortIds()
            ),
        );
    }

    /** @param array<int, array{start: float, end: float, text: string}> $segments */
    private function buildTranscription(string $videoId, array $segments): Transcription
    {
        $fullText = implode(' ', array_column($segments, 'text'));

        $words = array_map(
            static fn (array $segment) => new Word(
                TranscriptionText::fromString(trim($segment['text'])),
                StartTime::fromFloat($segment['start']),
                EndTime::fromFloat($segment['end']),
            ),
            $segments
        );

        return new Transcription(
            TranscriptionId::fromString($videoId),
            TranscriptionText::fromString($fullText),
            Language::SPANISH,
            new WordCollection($words),
        );
    }
}
