<?php

declare(strict_types=1);

namespace Canalizador\Script\Domain\Repositories;

use Canalizador\Script\Domain\Entities\Script;
use Canalizador\Script\Domain\ValueObjects\ScriptId;

interface ScriptRepository
{
    public function save(Script $script): void;

    public function findById(ScriptId $id): ?Script;

    public function delete(ScriptId $id): void;
}
