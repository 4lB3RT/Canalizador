<?php

declare(strict_types = 1);

namespace Canalizador\Video\Application\UseCases\GenerateVideo;

use Canalizador\Script\Domain\Services\GenerateScript;
use Canalizador\Shared\Domain\ValueObjects\DateTime;
use Canalizador\Video\Domain\Entities\Video;
use Canalizador\Video\Domain\Repositories\VideoGenerator;
use Canalizador\Video\Domain\Repositories\VideoRepository;
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
        // 1. Generar o obtener el script usando el servicio GenerateScript
        $script = $this->generateScript->generate(
            scriptId: $request->scriptId,
            prompt: $request->prompt
        );

        // 2. Extraer el video prompt del contenido del script
        $videoPrompt = $this->extractVideoPrompt($script->content()->value());

        // 3. Generar el video usando VideoGenerator
        $generationId = $this->videoGenerator->generate(
            $videoPrompt,
            ''
        );

        // 4. Crear la entidad Video con Script completo
        $video = new Video(
            id: VideoId::fromString($request->videoId),
            script: $script,
            title: Title::fromString($request->title ?? 'Generated Video'),
            createdAt: new DateTime(new \DateTimeImmutable()),
        );

        // 5. Guardar Video en el repositorio
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
