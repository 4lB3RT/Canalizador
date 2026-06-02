<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Script\Domain\Repositories;

use Helmreel\VideoProduction\Script\Domain\Entities\Script;
use Helmreel\VideoProduction\Script\Domain\ValueObjects\ScriptId;

interface ScriptRepository
{
    public function save(Script $script): void;

    public function findById(ScriptId $id): ?Script;

    public function delete(ScriptId $id): void;
}
