<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Script\Infrastructure\Repositories\Eloquent;

use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\DateTime;
use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;
use Helmreel\VideoProduction\Script\Domain\Entities\Script;
use Helmreel\VideoProduction\Script\Domain\Repositories\ScriptRepository;
use Helmreel\VideoProduction\Script\Domain\ValueObjects\ScriptContent;
use Helmreel\VideoProduction\Script\Domain\ValueObjects\ScriptId;
use Helmreel\VideoProduction\Script\Infrastructure\DAO\ScriptDAO;
use Helmreel\VideoProduction\Video\Domain\ValueObjects\VideoCategory;

final class EloquentScriptRepository implements ScriptRepository
{
    public function save(Script $script): void
    {
        ScriptDAO::updateOrCreate(
            ['script_id' => $script->id()->value()],
            [
                'user_id' => $script->userId()?->value(),
                'category' => $script->category()?->value,
                'title' => $script->title(),
                'content' => $script->content()->value(),
                'created_at' => $script->createdAt()?->value() ?? now(),
                'updated_at' => $script->updatedAt()?->value(),
            ]
        );
    }

    public function findById(ScriptId $id): ?Script
    {
        $model = ScriptDAO::find($id->value());

        if (!$model) {
            return null;
        }

        return $this->toEntity($model);
    }

    /**
     * @return Script[]
     */
    public function findByUserId(IntegerId $userId): array
    {
        return ScriptDAO::query()
            ->where('user_id', $userId->value())
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (ScriptDAO $model) => $this->toEntity($model))
            ->all();
    }

    public function delete(ScriptId $id): void
    {
        ScriptDAO::destroy($id->value());
    }

    private function toEntity(ScriptDAO $model): Script
    {
        return new Script(
            id: ScriptId::fromString($model->script_id),
            content: new ScriptContent($model->content),
            userId: $model->user_id !== null ? new IntegerId((int) $model->user_id) : null,
            category: $model->category !== null ? VideoCategory::from($model->category) : null,
            title: $model->title,
            createdAt: $model->created_at ? new DateTime($model->created_at->toDateTimeImmutable()) : null,
            updatedAt: $model->updated_at ? new DateTime($model->updated_at->toDateTimeImmutable()) : null,
        );
    }
}
