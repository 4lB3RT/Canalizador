<?php

declare(strict_types = 1);

namespace Canalizador\Video\Application\UseCases\GenerateVideo;

use Canalizador\Script\Domain\Services\GenerateScript;
use Canalizador\Shared\Domain\ValueObjects\DateTime;
use Canalizador\Video\Domain\Entities\Video;
use Canalizador\Video\Domain\Repositories\VideoGenerator;
use Canalizador\Video\Domain\Repositories\VideoRepository;
use Canalizador\Video\Domain\ValueObjects\GenerationId;
use Canalizador\Video\Domain\ValueObjects\Title;
use Canalizador\Video\Domain\ValueObjects\VideoId;

final readonly class GenerateVideo
{
    public function __construct(
        private GenerateScript $generateScript,
        private VideoRepository $videoRepository,
        private VideoGenerator $videoGenerator,
    ) {
    }

    public function execute(GenerateVideoRequest $request): GenerateVideoResponse
    {
        $script = $this->generateScript->generate(
            scriptId: $request->scriptId,
            prompt: $request->prompt
        );

        $videoPrompt = $this->extractVideoPrompt($script->content()->value());

        $generationId = $this->videoGenerator->generate(
            $videoPrompt,
            ''
        );

        $video = new Video(
            id: VideoId::fromString($request->videoId),
            script: $script,
            title: Title::fromString($request->title ?? 'Generated Video'),
            createdAt: new DateTime(new \DateTimeImmutable()),
            generationId: GenerationId::fromString($generationId),
        );

        $this->videoRepository->save($video);

        return new GenerateVideoResponse($video);
    }

    private function extractVideoPrompt(string $scriptContent): string
    {
        $jsonData = json_decode($scriptContent, true);

        if (json_last_error() === JSON_ERROR_NONE && isset($jsonData['video_prompt'])) {
            return $jsonData['video_prompt'];
        }

        return $scriptContent;
    }
}
