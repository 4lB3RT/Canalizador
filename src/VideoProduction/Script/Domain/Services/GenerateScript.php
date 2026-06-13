<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Script\Domain\Services;

use Helmreel\Shared\Shared\Domain\ValueObjects\Language;
use Helmreel\VideoProduction\Script\Domain\Entities\Script;
use Helmreel\VideoProduction\Script\Domain\Factories\ScriptFactory;
use Helmreel\VideoProduction\Script\Domain\Repositories\ScriptGenerator;
use Helmreel\VideoProduction\Script\Domain\Repositories\ScriptRepository;

final readonly class GenerateScript
{
    public function __construct(
        private ScriptRepository $scriptRepository,
        private ScriptGenerator $scriptGenerator,
        private ScriptFactory $scriptFactory,
    ) {
    }

    public function generate(
        string $scriptId,
        string $prompt,
        Language $language = Language::SPANISH,
        int $totalClips = 5,
        int $clipDuration = 8,
    ): Script {
        $scriptContent = $this->scriptGenerator->generateGaming($prompt, $language, $totalClips, $clipDuration);

        $script = $this->scriptFactory->createFromStrings(
            id: $scriptId,
            content: $scriptContent
        );

        $this->scriptRepository->save($script);

        return $script;
    }

    public function generateWeather(
        string $scriptId,
        string $prompt,
        Language $language = Language::SPANISH,
        int $totalClips = 5,
        int $clipDuration = 8,
    ): Script {
        $scriptContent = $this->scriptGenerator->generateWeather($prompt, $language, $totalClips, $clipDuration);

        $script = $this->scriptFactory->createFromStrings(
            id: $scriptId,
            content: $scriptContent
        );

        $this->scriptRepository->save($script);

        return $script;
    }
}
