<?php

declare(strict_types=1);

namespace Canalizador\Video\Application\UseCases\GenerateVideo;

use Canalizador\Avatar\Domain\Repositories\AvatarRepository;
use Canalizador\Avatar\Domain\ValueObjects\AvatarId;
use Canalizador\Channel\Domain\Exceptions\ChannelNotFound;
use Canalizador\Channel\Domain\Repositories\ChannelRepository;
use Canalizador\Channel\Domain\ValueObjects\ChannelId;
use Canalizador\Script\Domain\Repositories\ScriptRepository;
use Canalizador\Script\Domain\Services\GenerateScript;
use Canalizador\Script\Domain\ValueObjects\ScriptId;
use Canalizador\Shared\Domain\Events\EventBus;
use Canalizador\Shared\Domain\Services\Clock;
use Canalizador\Video\Domain\Events\VideoCreated;
use Canalizador\Video\Domain\Factories\VideoFactory;
use Canalizador\Video\Domain\Repositories\VideoGenerator;
use Canalizador\Video\Domain\Repositories\VideoMetadataGenerator;
use Canalizador\Video\Domain\Repositories\VideoRepository;
use Canalizador\Video\Domain\Services\VideoPromptExtractor;
use Canalizador\Video\Domain\ValueObjects\GenerationId;
use Canalizador\Video\Domain\ValueObjects\VideoCategory;
use Canalizador\Video\Domain\ValueObjects\VideoId;

final readonly class GenerateVideo
{
    public function __construct(
        private ScriptRepository $scriptRepository,
        private GenerateScript $generateScript,
        private VideoPromptExtractor $videoPromptExtractor,
        private VideoGenerator $videoGenerator,
        private VideoFactory $videoFactory,
        private VideoRepository $videoRepository,
        private VideoMetadataGenerator $videoMetadataGenerator,
        private ChannelRepository $channelRepository,
        private AvatarRepository $avatarRepository,
        private EventBus $eventBus,
        private Clock $clock,
    ) {
    }

    /**
     * @throws \RuntimeException
     * @throws ChannelNotFound
     */
    public function execute(GenerateVideoRequest $request): GenerateVideoResponse
    {
        $scriptId = ScriptId::fromString($request->scriptId);
        $category = VideoCategory::from($request->category);

        $channel = $this->channelRepository->findById(ChannelId::fromString($request->channelId));

        $script = $this->scriptRepository->findById($scriptId);

        if ($script === null) {
            $script = $this->generateScript->generate(
                scriptId: $request->scriptId,
                channelId: $request->channelId,
                category: $category,
                prompt: $request->prompt
            );
        }

        $metadata = $this->videoMetadataGenerator->generate($script->content()->value());

        $videoPrompt = $request->avatarId !== null
            ? $this->videoPromptExtractor->extractWithAvatar(
                $script,
                $this->avatarRepository->findById(AvatarId::fromString($request->avatarId)),
                $category
            )
            : $this->videoPromptExtractor->extract($script, $category);

        $generationId = $this->videoGenerator->generate($videoPrompt, $channel);

        $video = $this->videoFactory->create(
            id: VideoId::fromString($request->videoId),
            script: $script,
            title: $metadata->title,
            description: $metadata->description,
            category: $category,
            generationId: GenerationId::fromString($generationId),
        );

        $this->videoRepository->save($video);

        $this->eventBus->publish(
            new VideoCreated($video->id()->value(), $this->clock->now())
        );

        return new GenerateVideoResponse($video);
    }
}
