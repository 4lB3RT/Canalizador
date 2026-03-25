<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Video\Application\UseCases\ApplyVoice;

use Canalizador\VideoProduction\Avatar\Domain\Repositories\AvatarRepository;
use Canalizador\VideoProduction\Avatar\Domain\ValueObjects\AvatarId;
use Canalizador\VideoProduction\Clip\Domain\Services\VideoComposer;
use Canalizador\VideoProduction\Video\Domain\Repositories\VideoRepository;
use Canalizador\VideoProduction\Video\Domain\ValueObjects\VideoId;
use Canalizador\VideoProduction\Voice\Domain\Repositories\AudioIsolator;
use Canalizador\VideoProduction\Voice\Domain\Repositories\VoiceGenerator;
use Canalizador\VideoProduction\Voice\Domain\Repositories\VoiceRepository;
use Illuminate\Support\Facades\File;

final readonly class ApplyVoice
{
    public function __construct(
        private VideoRepository $videoRepository,
        private AvatarRepository $avatarRepository,
        private VoiceRepository $voiceRepository,
        private VoiceGenerator $voiceGenerator,
        private VideoComposer $videoComposer,
        private AudioIsolator $audioIsolator,
    ) {
    }

    /**
     * Aplica la voz del avatar al vídeo preservando el audio de fondo original.
     */
    public function execute(ApplyVoiceRequest $request): string
    {
        $video = $this->videoRepository->findById(VideoId::fromString($request->videoId));
        $videoPath = $video->videoLocalPath();

        if ($videoPath === null) {
            throw new \RuntimeException('Video does not have a local file path');
        }

        $avatar = $this->avatarRepository->findById(AvatarId::fromString($request->avatarId));

        if ($avatar->voiceId() === null) {
            throw new \RuntimeException('Avatar does not have a voice assigned');
        }

        $voice = $this->voiceRepository->findById($avatar->voiceId());

        if ($voice === null || $voice->platformId() === null) {
            throw new \RuntimeException('Voice does not have a platform ID');
        }

        $tmpDir = storage_path('tmp');
        if (!File::exists($tmpDir)) {
            File::makeDirectory($tmpDir, 0755, true);
        }

        $originalAudioPath = $tmpDir . '/' . uniqid('audio_', true) . '.mp3';
        $this->videoComposer->extractAudio($videoPath->value(), $originalAudioPath);

        $vocalsPath = $this->audioIsolator->isolate($originalAudioPath);

        $backgroundPath = $tmpDir . '/' . uniqid('bg_', true) . '.mp3';
        $this->videoComposer->subtractAudio($originalAudioPath, $vocalsPath, $backgroundPath);

        $convertedVoicePath = $this->voiceGenerator->generate($vocalsPath, $voice->platformId());

        $mixedAudioPath = $tmpDir . '/' . uniqid('mix_', true) . '.mp3';
        $this->videoComposer->mixAudio($convertedVoicePath, $backgroundPath, $mixedAudioPath);

        $outputPath = $videoPath->value() . '.voiced.mp4';
        $this->videoComposer->replaceAudio($videoPath->value(), $mixedAudioPath, $outputPath);

        return $outputPath;
    }
}
