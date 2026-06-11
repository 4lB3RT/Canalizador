<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Avatar\Application\UseCases\GenerateAvatarMetadata;

use Helmreel\VideoProduction\Avatar\Domain\Entities\AvatarMedia;
use Helmreel\VideoProduction\Avatar\Domain\Exceptions\AvatarNotFound;
use Helmreel\VideoProduction\Avatar\Domain\Repositories\AvatarRepository;
use Helmreel\VideoProduction\Avatar\Domain\ValueObjects\AvatarId;
use Helmreel\VideoProduction\Avatar\Domain\ValueObjects\AvatarMediaType;
use Helmreel\VideoProduction\Avatar\Infrastructure\Repositories\OpenAI\OpenAiAvatarRepository;

final readonly class GenerateAvatarMetadata
{
    public function __construct(
        private AvatarRepository $avatarRepository,
        private OpenAiAvatarRepository $openAiAvatarRepository,
    ) {
    }

    /**
     * @throws AvatarNotFound
     */
    public function execute(string $avatarId, int $userId): array
    {
        $avatar = $this->avatarRepository->findById(AvatarId::fromString($avatarId));

        if ($avatar->userId()->value() !== $userId) {
            throw AvatarNotFound::withId($avatarId);
        }

        $metadataResult = $this->openAiAvatarRepository->generateMetadata(
            imagePath: $avatar->profileImagePath(),
            avatarName: $avatar->name(),
            biography: $avatar->biography(),
            presentationStyle: $avatar->presentationStyle(),
            userId: $avatar->userId(),
            category: $avatar->category(),
        );

        $profileMedia = array_filter(
            $avatar->media(),
            fn (AvatarMedia $m) => $m->type() === AvatarMediaType::PROFILE,
        );

        $avatar->updateDescription($metadataResult->description());
        $avatar->updateMedia([
            ...$profileMedia,
            ...$metadataResult->media(),
        ]);

        $this->avatarRepository->save($avatar);

        return $avatar->toArray();
    }
}
