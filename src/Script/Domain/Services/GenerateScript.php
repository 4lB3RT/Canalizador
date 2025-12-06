<?php

declare(strict_types = 1);

namespace Canalizador\Script\Domain\Services;

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

    public function generate(string $scriptId, ?string $prompt = null): Script
    {
        $scriptContent = $this->scriptGenerator->generate($prompt);

        $id = ScriptId::fromString($scriptId);

        $script = new Script(
            id: $id,
            content: new ScriptContent($scriptContent)
        );

        $this->scriptRepository->save($script);

        return $script;
    }
}
