<?php

declare(strict_types=1);

namespace Canalizador\Video\Application\UseCases\GenerateVideo;

use Canalizador\Script\Domain\Repositories\ScriptRepository;
use Canalizador\Script\Domain\Services\GenerateScript;
use Canalizador\Script\Domain\ValueObjects\ScriptId;
use Canalizador\Video\Domain\Factories\VideoFactory;
use Canalizador\Video\Domain\Repositories\VideoGenerator;
use Canalizador\Video\Domain\Repositories\VideoMetadataGenerator;
use Canalizador\Video\Domain\Repositories\VideoRepository;
use Canalizador\Video\Domain\Services\VideoPromptExtractor;
use Canalizador\Video\Domain\ValueObjects\GenerationId;
use Canalizador\Video\Domain\ValueObjects\VideoId;

final readonly class GenerateVideo
{
    public function __construct(
        private ScriptRepository $scriptRepository,
        private GenerateScript $generateScript,
        private VideoPromptExtractor $videoPromptExtractor,
        private VideoGenerator $videoGenerator,
        private VideoFactory $videoFactory,
        private VideoRepository $videoRepository,
        private VideoMetadataGenerator $videoMetadataGenerator,
    ) {
    }

    /**
     * @throws \RuntimeException
     */
    public function execute(GenerateVideoRequest $request): GenerateVideoResponse
    {
        $scriptId = ScriptId::fromString($request->scriptId);

        $script = $this->scriptRepository->findById($scriptId);

        if ($script === null) {
            $script = $this->generateScript->generate(
                scriptId: $request->scriptId,
                prompt: $request->prompt
            );
        }

        $metadata = $this->videoMetadataGenerator->generate($script->content()->value());

        $videoPrompt = $this->videoPromptExtractor->extract($script);

        $generationId = $this->videoGenerator->generate($videoPrompt);

        $video = $this->videoFactory->create(
            id: VideoId::fromString($request->videoId),
            script: $script,
            title: $metadata->title,
            description: $metadata->description,
            generationId: GenerationId::fromString($generationId),
        );

        $this->videoRepository->save($video);

        return new GenerateVideoResponse($video);
    }
}
