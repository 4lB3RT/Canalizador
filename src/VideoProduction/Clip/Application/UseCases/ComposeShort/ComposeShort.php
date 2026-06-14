<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Clip\Application\UseCases\ComposeShort;

use Helmreel\Shared\Shared\Domain\Services\Clock;
use Helmreel\Shared\Shared\Domain\ValueObjects\LocalPath;
use Helmreel\VideoProduction\Avatar\Domain\Repositories\AvatarRepository;
use Helmreel\VideoProduction\Clip\Domain\Repositories\ClipRepository;
use Helmreel\VideoProduction\Clip\Domain\Services\VideoComposer;
use Helmreel\Shared\Media\Domain\Entities\Media;
use Helmreel\Shared\Media\Domain\Repositories\MediaRepository;
use Helmreel\Shared\Media\Domain\ValueObjects\MediaId;
use Helmreel\Shared\Media\Domain\ValueObjects\MediaType;
use Helmreel\VideoProduction\Video\Domain\Entities\Video;
use Helmreel\VideoProduction\Video\Domain\Repositories\VideoRepository;
use Helmreel\VideoProduction\Video\Domain\ValueObjects\VideoId;
use Helmreel\VideoProduction\Voice\Domain\Repositories\VoiceGenerator;
use Helmreel\VideoProduction\Voice\Domain\Repositories\VoiceRepository;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

final readonly class ComposeShort
{
    public function __construct(
        private ClipRepository $clipRepository,
        private VideoRepository $videoRepository,
        private Clock $clock,
        private AvatarRepository $avatarRepository,
        private VoiceRepository $voiceRepository,
        private VoiceGenerator $voiceGenerator,
        private VideoComposer $videoComposer,
        private MediaRepository $mediaRepository,
    ) {
    }

    public function execute(ComposeShortRequest $request): void
    {
        $videoId = VideoId::fromString($request->videoId);
        $video = $this->videoRepository->findById($videoId);
        $clips = $this->clipRepository->findByVideoId($videoId)->sortedBySequence();

        $clipPaths = [];
        foreach ($clips as $clip) {
            if ($clip->localPath() !== null) {
                $clipPaths[] = $clip->localPath()->value();
            }
        }

        $outputDir = storage_path('app/videos');
        if (!File::exists($outputDir)) {
            File::makeDirectory($outputDir, 0755, true);
        }

        $outputPath = $outputDir . "/{$videoId->value()}.mp4";

        $this->videoComposer->concatenate($clipPaths, $outputPath);

        $this->applyAvatarVoice($video, $outputPath);

        $media = new Media(
            id: MediaId::fromString(Str::uuid()->toString()),
            userId: $video->userId(),
            type: MediaType::VIDEO,
            path: LocalPath::fromString($outputPath),
            createdAt: $this->clock->now(),
        );
        $this->mediaRepository->save($media);

        $video->markAsCompleted(LocalPath::fromString($outputPath), $this->clock->now(), $media->id());
        $this->videoRepository->save($video);
    }

    private function applyAvatarVoice(Video $video, string $videoPath): void
    {
        if ($video->avatarId() === null) {
            return;
        }

        $avatar = $this->avatarRepository->findById($video->avatarId());

        if ($avatar->voiceId() === null) {
            return;
        }

        $voice = $this->voiceRepository->findById($avatar->voiceId());

        if ($voice === null || $voice->platformId() === null) {
            return;
        }

        $narrationDir = storage_path('app/narrations');
        if (!File::exists($narrationDir)) {
            File::makeDirectory($narrationDir, 0755, true);
        }

        $audioPath = $narrationDir . "/{$video->id()->value()}.mp3";
        $this->videoComposer->extractAudio($videoPath, $audioPath);

        $convertedAudioPath = $this->voiceGenerator->generate($audioPath, $voice->platformId());

        $tempVideoPath = $videoPath . '.tmp.mp4';
        $this->videoComposer->replaceAudio($videoPath, $convertedAudioPath, $tempVideoPath);

        File::move($tempVideoPath, $videoPath);
    }
}
