<?php

declare(strict_types=1);

namespace Canalizador\Script\Domain\Services;

use Canalizador\Channel\Domain\Entities\Channel;
use Canalizador\Channel\Domain\Repositories\ChannelRepository;
use Canalizador\Channel\Domain\ValueObjects\ChannelId;
use Canalizador\Script\Domain\Entities\Script;
use Canalizador\Script\Domain\Factories\ScriptFactory;
use Canalizador\Script\Domain\Repositories\ScriptGenerator;
use Canalizador\Script\Domain\Repositories\ScriptIdeaGenerator;
use Canalizador\Script\Domain\Repositories\ScriptRepository;
use Canalizador\Video\Domain\ValueObjects\VideoCategory;

final readonly class GenerateScript
{
    public function __construct(
        private ScriptRepository $scriptRepository,
        private ScriptGenerator $scriptGenerator,
        private ScriptIdeaGenerator $scriptIdeaGenerator,
        private ScriptFactory $scriptFactory,
        private ChannelRepository $channelRepository
    ) {
    }

    public function generate(
        string $scriptId,
        string $channelId,
        VideoCategory $category,
        ?string $prompt = null,
        int $totalClips = 5,
        int $clipDuration = 8,
    ): Script {
        $channel = $this->channelRepository->findById(ChannelId::fromString($channelId));

        $finalPrompt = $prompt ?? match ($category) {
            VideoCategory::GAMING => $this->scriptIdeaGenerator->generateGaming($channel),
            VideoCategory::ASTROLOGY => $this->scriptIdeaGenerator->generateAstrology($channel),
        };

        $scriptContent = match ($category) {
            VideoCategory::GAMING => $this->scriptGenerator->generateGaming($finalPrompt, $channel, $totalClips, $clipDuration),
            VideoCategory::ASTROLOGY => $this->scriptGenerator->generateAstrology($finalPrompt, $channel, $totalClips, $clipDuration),
        };

        $script = $this->scriptFactory->createFromStrings(
            id: $scriptId,
            content: $scriptContent
        );

        $this->scriptRepository->save($script);

        return $script;
    }
}
