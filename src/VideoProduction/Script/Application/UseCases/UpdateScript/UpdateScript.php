<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Script\Application\UseCases\UpdateScript;

use Helmreel\Shared\Shared\Domain\Services\Clock;
use Helmreel\VideoProduction\Script\Domain\Exceptions\ScriptNotFound;
use Helmreel\VideoProduction\Script\Domain\Repositories\ScriptRepository;
use Helmreel\VideoProduction\Script\Domain\ValueObjects\ScriptContent;
use Helmreel\VideoProduction\Script\Domain\ValueObjects\ScriptId;

final readonly class UpdateScript
{
    public function __construct(
        private ScriptRepository $scriptRepository,
        private Clock $clock,
    ) {
    }

    /**
     * @throws ScriptNotFound
     */
    public function execute(string $scriptId, int $userId, ?string $title, ?string $content): array
    {
        $script = $this->scriptRepository->findById(ScriptId::fromString($scriptId));

        if ($script === null || $script->userId()?->value() !== $userId) {
            throw ScriptNotFound::withId($scriptId);
        }

        if ($title !== null) {
            $script->updateTitle($title);
        }

        if ($content !== null) {
            $script->updateContent(new ScriptContent($content));
        }

        $script->touch($this->clock->now());

        $this->scriptRepository->save($script);

        return $script->toArray();
    }
}
