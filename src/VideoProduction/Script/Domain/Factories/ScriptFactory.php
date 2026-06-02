<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Script\Domain\Factories;

use Helmreel\VideoProduction\Script\Domain\Entities\Script;
use Helmreel\VideoProduction\Script\Domain\ValueObjects\ScriptContent;
use Helmreel\VideoProduction\Script\Domain\ValueObjects\ScriptId;

final readonly class ScriptFactory
{
    public function create(
        ScriptId $id,
        ScriptContent $content
    ): Script {
        return new Script(
            id: $id,
            content: $content,
        );
    }

    public function createFromStrings(
        string $id,
        string $content
    ): Script {
        return $this->create(
            id: ScriptId::fromString($id),
            content: new ScriptContent($content),
        );
    }
}
