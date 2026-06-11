<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Script\Application\UseCases\GetScripts;

use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;
use Helmreel\VideoProduction\Script\Domain\Repositories\ScriptRepository;

final readonly class GetScripts
{
    public function __construct(
        private ScriptRepository $scriptRepository,
    ) {
    }

    public function execute(int $userId): array
    {
        $scripts = $this->scriptRepository->findByUserId(new IntegerId($userId));

        return array_map(fn ($script) => $script->toArray(), $scripts);
    }
}
