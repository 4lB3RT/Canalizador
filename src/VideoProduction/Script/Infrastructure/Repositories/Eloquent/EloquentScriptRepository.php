<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Script\Infrastructure\Repositories\Eloquent;

use Helmreel\VideoProduction\Script\Domain\Entities\Script;
use Helmreel\VideoProduction\Script\Domain\Repositories\ScriptRepository;
use Helmreel\VideoProduction\Script\Domain\ValueObjects\ScriptContent;
use Helmreel\VideoProduction\Script\Domain\ValueObjects\ScriptId;
use Helmreel\VideoProduction\Script\Infrastructure\DAO\ScriptDAO;

final class EloquentScriptRepository implements ScriptRepository
{
    public function save(Script $script): void
    {
        ScriptDAO::updateOrCreate(
            ['script_id' => $script->id()->value()],
            ['content' => $script->content()->value()]
        );
    }

    public function findById(ScriptId $id): ?Script
    {
        $model = ScriptDAO::find($id->value());

        if (!$model) {
            return null;
        }

        return new Script(
            id: ScriptId::fromString($model->script_id),
            content: new ScriptContent($model->content)
        );
    }

    public function delete(ScriptId $id): void
    {
        ScriptDAO::destroy($id->value());
    }
}
