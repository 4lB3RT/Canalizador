<?php

declare(strict_types=1);

namespace Canalizador\Video\Application\UseCases\CreateVideo;

use Canalizador\Avatar\Domain\ValueObjects\AvatarId;
use Canalizador\Channel\Domain\Exceptions\ChannelNotFound;
use Canalizador\Channel\Domain\ValueObjects\ChannelId;
use Canalizador\Script\Domain\Repositories\ScriptRepository;
use Canalizador\Script\Domain\Services\GenerateScript;
use Canalizador\Script\Domain\ValueObjects\ScriptId;
use Canalizador\Shared\Domain\Events\EventBus;
use Canalizador\Shared\Domain\Services\Clock;
use Canalizador\Video\Domain\Events\VideoCreated;
use Canalizador\Video\Domain\Factories\VideoFactory;
use Canalizador\Video\Domain\Repositories\VideoMetadataGenerator;
use Canalizador\Video\Domain\Repositories\VideoRepository;
use Canalizador\Video\Domain\ValueObjects\VideoCategory;
use Canalizador\Video\Domain\ValueObjects\VideoId;

final readonly class CreateVideo
{
    public function __construct(
        private ScriptRepository $scriptRepository,
        private GenerateScript $generateScript,
        private VideoFactory $videoFactory,
        private VideoRepository $videoRepository,
        private VideoMetadataGenerator $videoMetadataGenerator,
        private EventBus $eventBus,
        private Clock $clock,
    ) {
    }

    public function execute(CreateVideoRequest $request): CreateVideoResponse
    {
        $scriptId = ScriptId::fromString($request->scriptId);
        $channelId = ChannelId::fromString($request->channelId);
        $category = VideoCategory::from($request->category);

        $script = $this->scriptRepository->findById($scriptId);

        if ($script === null) {
            $script = $this->generateScript->generate(
                scriptId: $request->scriptId,
                channelId: $request->channelId,
                category: $category,
                prompt: $request->prompt,
                totalClips: (int) config('veo.total_clips', 5),
                clipDuration: (int) config('veo.duration', 8),
            );
        }

        $metadata = $this->videoMetadataGenerator->generate($script->content()->value());

        $video = $this->videoFactory->create(
            id: VideoId::fromString($request->videoId),
            script: $script,
            channelId: $channelId,
            title: $metadata->title,
            description: $metadata->description,
            category: $category,
            avatarId: $request->avatarId ? AvatarId::fromString($request->avatarId) : null,
        );

        $this->videoRepository->save($video);

        $this->eventBus->publish(
            new VideoCreated($video->id()->value(), $this->clock->now())
        );

        return new CreateVideoResponse($video);
    }
}
