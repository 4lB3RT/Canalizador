<?php

declare(strict_types=1);

namespace Canalizador\Clip\Application\UseCases\ComposeShort;

use Canalizador\Avatar\Domain\Repositories\AvatarRepository;
use Canalizador\Clip\Domain\Repositories\ClipRepository;
use Canalizador\Clip\Domain\Services\VideoComposer;
use Canalizador\Shared\Domain\Services\Clock;
use Canalizador\Shared\Domain\ValueObjects\LocalPath;
use Canalizador\Video\Domain\Entities\Video;
use Canalizador\Video\Domain\Repositories\VideoRepository;
use Canalizador\Video\Domain\ValueObjects\VideoId;
use Canalizador\Voice\Domain\Repositories\VoiceGenerator;
use Canalizador\Voice\Domain\Repositories\VoiceRepository;
use Illuminate\Support\Facades\File;

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
    ) {
    }

    public function execute(ComposeShortRequest $request): void
    {
        $videoId = VideoId::fromString($request->videoId);
        $video = $this->videoRepository->findById($videoId);
        $clips = $this->clipRepository->findByVideoId($videoId)->sortedBySequence();

        $lastClip = $clips->last();

        $outputDir = storage_path('app/videos');
        if (!File::exists($outputDir)) {
            File::makeDirectory($outputDir, 0755, true);
        }

        $outputPath = $outputDir . "/{$videoId->value()}.mp4";

        File::copy($lastClip->localPath()->value(), $outputPath);

        $this->applyAvatarVoice($video, $outputPath);

        $video->markAsCompleted(LocalPath::fromString($outputPath), $this->clock->now());
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
