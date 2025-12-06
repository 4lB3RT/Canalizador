<?php

declare(strict_types = 1);

namespace Canalizador\Video\Application\UseCases;

use Canalizador\Script\Domain\Exceptions\ScriptNotFound;
use Canalizador\Script\Domain\Repositories\ScriptRepository;
use Canalizador\Script\Domain\ValueObjects\ScriptId;
use Canalizador\Video\Domain\Repositories\VideoGenerator;

final readonly class GenerateVideoFromScript
{
    public function __construct(
        private ScriptRepository $scriptRepository,
        private VideoGenerator $videoGenerator,
    ) {
    }

    /**
     * @throws ScriptNotFound
     */
    public function execute(GenerateVideoFromScriptRequest $request): GenerateVideoFromScriptResponse
    {
        $scriptId = ScriptId::fromString($request->scriptId);
        $script = $this->scriptRepository->findById($scriptId);

        if (!$script) {
            throw ScriptNotFound::withId($request->scriptId);
        }

        $scriptContent = $script->content()->value();
        $videoPrompt = $this->extractVideoPrompt($scriptContent);

        $generationId = $this->videoGenerator->generate(
            $videoPrompt,
            ''
        );

        return new GenerateVideoFromScriptResponse($generationId);
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
