<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Application\UseCases\SmartFragmentAndPublishVideo;

use Canalizador\Shared\Shared\Domain\ValueObjects\Essentials\Language;
use Canalizador\Shared\Shared\Domain\ValueObjects\Essentials\LocalPath;
use Canalizador\YouTube\Transcription\Domain\Collections\WordCollection;
use Canalizador\YouTube\Transcription\Domain\Entities\Transcription;
use Canalizador\YouTube\Transcription\Domain\ValueObjects\EndTime;
use Canalizador\YouTube\Transcription\Domain\ValueObjects\StartTime;
use Canalizador\YouTube\Transcription\Domain\ValueObjects\Text as TranscriptionText;
use Canalizador\YouTube\Transcription\Domain\ValueObjects\Word;
use Canalizador\YouTube\Video\Domain\Exceptions\VideoFragmentationFailed;
use Canalizador\YouTube\Video\Domain\Exceptions\VideoNotFound;
use Canalizador\YouTube\Video\Domain\Exceptions\YouTubeOperationFailed;
use Canalizador\YouTube\Video\Domain\Factories\VideoPublisherFactory;
use Canalizador\YouTube\Video\Domain\Repositories\AudioExtractor;
use Canalizador\YouTube\Video\Domain\Repositories\SmartVideoFragmenter;
use Canalizador\YouTube\Video\Domain\Repositories\VideoRepository;
use Canalizador\YouTube\Video\Domain\Repositories\VideoTranscriber;
use Canalizador\YouTube\Video\Domain\ValueObjects\Id;
use Canalizador\Youtube\Video\Domain\ValueObjects\Id as TranscriptionId;
use Canalizador\YouTube\Video\Domain\ValueObjects\VideoToPublish;

final readonly class SmartFragmentAndPublishVideo
{
    public function __construct(
        private VideoRepository      $videoRepository,
        private AudioExtractor       $audioExtractor,
        private VideoTranscriber     $videoTranscriber,
        private SmartVideoFragmenter $smartVideoFragmenter,
        private VideoPublisherFactory $videoPublisherFactory,
    ) {
    }

    /**
     * @throws VideoNotFound
     * @throws VideoFragmentationFailed
     * @throws YouTubeOperationFailed
     */
    public function execute(SmartFragmentAndPublishVideoRequest $request): SmartFragmentAndPublishVideoResponse
    {
        $video = $this->videoRepository->findById(new Id($request->videoId));

        $videoPath = new LocalPath($request->localPath);
        $audioPath = $this->audioExtractor->extract($videoPath);

        $video->updateVideoLocalPath(new LocalPath($videoPath->value()));
        $video->updateAudioLocalPath($audioPath);
        $this->videoRepository->save($video);

        $segments = $this->videoTranscriber->transcribe($audioPath);
        $transcription = $this->buildTranscription($video->id()->value(), $segments);
        $video->updateTranscription($transcription);
        $this->videoRepository->save($video);

        $fragments = $this->smartVideoFragmenter->fragment($videoPath, $transcription);

        $publisher = $this->videoPublisherFactory->create('youtube');

        foreach ($fragments as $index => $fragmentPath) {
            $videoToPublish = new VideoToPublish(
                localPath:   $fragmentPath,
                title:       "{$request->baseTitle} - Short " . ($index + 1),
                description: $request->baseDescription,
            );

            $publishedId = $publisher->publish($videoToPublish);
            $video->addPublishedShortId($publishedId);
            $this->videoRepository->save($video);
        }

        return new SmartFragmentAndPublishVideoResponse(
            publishedVideoIds: $video->publishedShortIds(),
        );
    }

    /** @param array<int, array{start: float, end: float, text: string}> $segments */
    private function buildTranscription(string $videoId, array $segments): Transcription
    {
        $fullText = implode(' ', array_column($segments, 'text'));

        $words = array_map(
            static fn(array $segment) => new Word(
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
