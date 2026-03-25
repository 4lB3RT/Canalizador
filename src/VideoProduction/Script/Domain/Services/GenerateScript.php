<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Script\Domain\Services;

use Canalizador\YouTube\Channel\Domain\Repositories\ChannelRepository;
use Canalizador\YouTube\Channel\Domain\ValueObjects\ChannelId;
use Canalizador\VideoProduction\Script\Domain\Entities\Script;
use Canalizador\VideoProduction\Script\Domain\Factories\ScriptFactory;
use Canalizador\VideoProduction\Script\Domain\Repositories\ScriptGenerator;
use Canalizador\VideoProduction\Script\Domain\Repositories\ScriptRepository;

final readonly class GenerateScript
{
    public function __construct(
        private ScriptRepository $scriptRepository,
        private ScriptGenerator $scriptGenerator,
        private ScriptFactory $scriptFactory,
        private ChannelRepository $channelRepository
    ) {
    }

    public function generate(
        string $scriptId,
        string $channelId,
        string $prompt,
        int $totalClips = 5,
        int $clipDuration = 8,
    ): Script {
        $channel = $this->channelRepository->findById(ChannelId::fromString($channelId));

        $scriptContent = $this->scriptGenerator->generateGaming($prompt, $channel, $totalClips, $clipDuration);

        $script = $this->scriptFactory->createFromStrings(
            id: $scriptId,
            content: $scriptContent
        );

        $this->scriptRepository->save($script);

        return $script;
    }

    public function generateWeather(
        string $scriptId,
        string $channelId,
        string $prompt,
        int $totalClips = 5,
        int $clipDuration = 8,
    ): Script {
        $channel = $this->channelRepository->findById(ChannelId::fromString($channelId));

        $scriptContent = $this->scriptGenerator->generateWeather($prompt, $channel, $totalClips, $clipDuration);

        $script = $this->scriptFactory->createFromStrings(
            id: $scriptId,
            content: $scriptContent
        );

        $this->scriptRepository->save($script);

        return $script;
    }
}
