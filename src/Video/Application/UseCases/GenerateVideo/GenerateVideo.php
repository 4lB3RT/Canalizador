<?php

declare(strict_types = 1);

namespace Canalizador\Video\Application\UseCases\GenerateVideo;

use Canalizador\Script\Domain\Services\GenerateScript;
use Canalizador\Video\Domain\Factories\VideoFactory;
use Canalizador\Video\Domain\Repositories\VideoGenerator;
use Canalizador\Video\Domain\Repositories\VideoRepository;
use Canalizador\Video\Domain\Services\VideoPromptExtractor;
use Canalizador\Video\Domain\ValueObjects\GenerationId;
use Canalizador\Video\Domain\ValueObjects\Title;
use Canalizador\Video\Domain\ValueObjects\VideoId;

final readonly class GenerateVideo
{
    public function __construct(
        private GenerateScript $generateScript,
        private VideoPromptExtractor $videoPromptExtractor,
        private VideoGenerator $videoGenerator,
        private VideoFactory $videoFactory,
        private VideoRepository $videoRepository,
    ) {
    }

    public function execute(GenerateVideoRequest $request): GenerateVideoResponse
    {
        $script = $this->generateScript->generate(
            scriptId: $request->scriptId,
            prompt: $request->prompt
        );

        $videoPrompt = $this->videoPromptExtractor->extract($script);

        $generationId = $this->videoGenerator->generate($videoPrompt);

        $video = $this->videoFactory->create(
            id: VideoId::fromString($request->videoId),
            script: $script,
            title: Title::fromString($request->title ?? 'Generated Video'),
            generationId: GenerationId::fromString($generationId),
        );

        $this->videoRepository->save($video);

        return new GenerateVideoResponse($video);
    }
}
