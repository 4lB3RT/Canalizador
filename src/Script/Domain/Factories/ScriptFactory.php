<?php

declare(strict_types = 1);

namespace Canalizador\Script\Domain\Factories;

use Canalizador\Script\Domain\Entities\Script;
use Canalizador\Script\Domain\ValueObjects\ScriptContent;
use Canalizador\Script\Domain\ValueObjects\ScriptId;

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
