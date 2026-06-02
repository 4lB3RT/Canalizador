<?php

declare(strict_types=1);

namespace Helmreel\YouTube\Video\Application\UseCases\SmartFragmentAndPublishVideo;

use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\Language;
use Helmreel\Shared\Shared\Domain\ValueObjects\LocalPath;
use Helmreel\YouTube\Transcription\Domain\Collections\SentenceCollection;
use Helmreel\YouTube\Transcription\Domain\Entities\Transcription;
use Helmreel\YouTube\Transcription\Domain\ValueObjects\EndTime;
use Helmreel\YouTube\Transcription\Domain\ValueObjects\Sentence;
use Helmreel\YouTube\Transcription\Domain\ValueObjects\StartTime;
use Helmreel\YouTube\Transcription\Domain\ValueObjects\Text as TranscriptionText;
use Helmreel\YouTube\Video\Domain\Exceptions\VideoFragmentationFailed;
use Helmreel\YouTube\Video\Domain\Exceptions\VideoNotFound;
use Helmreel\YouTube\Video\Domain\Exceptions\YouTubeOperationFailed;
use Helmreel\YouTube\Video\Domain\Factories\VideoPublisherFactory;
use Helmreel\YouTube\Video\Domain\Repositories\AudioExtractor;
use Helmreel\YouTube\Video\Domain\Repositories\SmartVideoFragmenter;
use Helmreel\YouTube\Video\Domain\Repositories\VideoRepository;
use Helmreel\YouTube\Video\Domain\Repositories\VideoTranscriber;
use Helmreel\YouTube\Video\Domain\ValueObjects\Id;
use Helmreel\Youtube\Video\Domain\ValueObjects\Id as TranscriptionId;

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

        $sentences = array_map(
            static fn(array $segment) => new Sentence(
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
            new SentenceCollection($sentences),
        );
    }
}
