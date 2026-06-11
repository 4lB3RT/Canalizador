<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Script\Application\UseCases\GetScript;

use Helmreel\VideoProduction\Script\Domain\Exceptions\ScriptNotFound;
use Helmreel\VideoProduction\Script\Domain\Repositories\ScriptRepository;
use Helmreel\VideoProduction\Script\Domain\ValueObjects\ScriptId;

final readonly class GetScript
{
    public function __construct(
        private ScriptRepository $scriptRepository,
    ) {
    }

    /**
     * @throws ScriptNotFound
     */
    public function execute(string $scriptId, int $userId): array
    {
        $script = $this->scriptRepository->findById(ScriptId::fromString($scriptId));

        if ($script === null || $script->userId()?->value() !== $userId) {
            throw ScriptNotFound::withId($scriptId);
        }

        return $script->toArray();
    }
}
