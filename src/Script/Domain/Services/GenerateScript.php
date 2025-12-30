<?php

declare(strict_types=1);

namespace Canalizador\Script\Domain\Services;

use Canalizador\Script\Domain\Entities\Script;
use Canalizador\Script\Domain\Factories\ScriptFactory;
use Canalizador\Script\Domain\Repositories\ScriptGenerator;
use Canalizador\Script\Domain\Repositories\ScriptIdeaGenerator;
use Canalizador\Script\Domain\Repositories\ScriptRepository;

final readonly class GenerateScript
{
    public function __construct(
        private ScriptRepository $scriptRepository,
        private ScriptGenerator $scriptGenerator,
        private ScriptIdeaGenerator $scriptIdeaGenerator,
        private ScriptFactory $scriptFactory
    ) {
    }

    public function generate(string $scriptId, ?string $prompt = null): Script
    {
        $finalPrompt = $prompt ?? $this->scriptIdeaGenerator->generateIdea();

        $scriptContent = $this->scriptGenerator->generate($finalPrompt);

        $script = $this->scriptFactory->createFromStrings(
            id: $scriptId,
            content: $scriptContent
        );

        $this->scriptRepository->save($script);

        return $script;
    }
}
