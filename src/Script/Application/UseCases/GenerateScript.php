<?php

declare(strict_types = 1);

namespace Canalizador\Script\Application\UseCases;

use Canalizador\Script\Domain\Entities\Script;
use Canalizador\Script\Domain\Repositories\ScriptGenerator;
use Canalizador\Script\Domain\Repositories\ScriptRepository;
use Canalizador\Script\Domain\ValueObjects\ScriptContent;
use Canalizador\Script\Domain\ValueObjects\ScriptId;

final readonly class GenerateScript
{
    public function __construct(
        private ScriptRepository $scriptRepository,
        private ScriptGenerator $scriptGenerator
    ) {
    }

    public function execute(GenerateScriptRequest $request): GenerateScriptResponse
    {
        $scriptContent = $this->scriptGenerator->generate($request->prompt);

        $scriptId = ScriptId::fromString($request->uuid);

        $script = new Script(
            id: $scriptId,
            content: new ScriptContent($scriptContent)
        );

        $this->scriptRepository->save($script);

        return new GenerateScriptResponse($script);
    }
}
