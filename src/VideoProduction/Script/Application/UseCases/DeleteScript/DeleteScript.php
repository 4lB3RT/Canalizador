<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Script\Application\UseCases\DeleteScript;

use Helmreel\VideoProduction\Script\Domain\Exceptions\ScriptNotFound;
use Helmreel\VideoProduction\Script\Domain\Repositories\ScriptRepository;
use Helmreel\VideoProduction\Script\Domain\ValueObjects\ScriptId;

final readonly class DeleteScript
{
    public function __construct(
        private ScriptRepository $scriptRepository,
    ) {
    }

    /**
     * @throws ScriptNotFound
     */
    public function execute(string $scriptId, int $userId): void
    {
        $id = ScriptId::fromString($scriptId);
        $script = $this->scriptRepository->findById($id);

        if ($script === null || $script->userId()?->value() !== $userId) {
            throw ScriptNotFound::withId($scriptId);
        }

        $this->scriptRepository->delete($id);
    }
}
