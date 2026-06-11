<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Avatar\Application\UseCases\CreateAvatar;

use Helmreel\Shared\Shared\Domain\Services\Clock;
use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;
use Helmreel\Shared\Shared\Domain\ValueObjects\LocalPath;
use Helmreel\VideoProduction\Avatar\Domain\Entities\AvatarMedia;
use Helmreel\VideoProduction\Avatar\Domain\Factories\AvatarFactory;
use Helmreel\VideoProduction\Avatar\Domain\Repositories\AvatarRepository;
use Helmreel\VideoProduction\Avatar\Domain\ValueObjects\AvatarDescription;
use Helmreel\VideoProduction\Avatar\Domain\ValueObjects\AvatarId;
use Helmreel\VideoProduction\Avatar\Domain\ValueObjects\AvatarMediaType;
use Helmreel\VideoProduction\Avatar\Domain\ValueObjects\AvatarName;
use Helmreel\VideoProduction\Avatar\Domain\ValueObjects\Biography;
use Helmreel\VideoProduction\Avatar\Domain\ValueObjects\Category;
use Helmreel\VideoProduction\Avatar\Domain\ValueObjects\PresentationStyle;
use Helmreel\VideoProduction\Media\Domain\Entities\Media;
use Helmreel\VideoProduction\Media\Domain\Repositories\MediaRepository;
use Helmreel\VideoProduction\Media\Domain\ValueObjects\MediaId;
use Helmreel\VideoProduction\Media\Domain\ValueObjects\MediaType;
use Helmreel\VideoProduction\Voice\Domain\ValueObjects\VoiceId;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

final readonly class CreateAvatar
{
    public function __construct(
        private AvatarFactory $avatarFactory,
        private AvatarRepository $avatarRepository,
        private MediaRepository $mediaRepository,
        private Clock $clock,
    ) {
    }

    public function execute(CreateAvatarRequest $request): CreateAvatarResponse
    {
        $userId = new IntegerId($request->userId);
        $category = Category::fromString($request->category);

        $permanentDir = storage_path('app/avatars');
        if (!File::exists($permanentDir)) {
            File::makeDirectory($permanentDir, 0755, true);
        }

        $extension = pathinfo($request->profileImagePath, PATHINFO_EXTENSION);
        $permanentFilename = $request->avatarId . '.' . $extension;
        $permanentImagePath = $permanentDir . '/' . $permanentFilename;
        File::move($request->profileImagePath, $permanentImagePath);

        $profileImagePath = LocalPath::fromString($permanentImagePath);

        $profileMedia = new Media(
            id: MediaId::fromString(Str::uuid()->toString()),
            userId: $userId,
            type: MediaType::IMAGE,
            path: $profileImagePath,
            createdAt: $this->clock->now(),
        );
        $this->mediaRepository->save($profileMedia);

        $avatar = $this->avatarFactory->create(
            id: AvatarId::fromString($request->avatarId),
            userId: $userId,
            name: AvatarName::fromString($request->name),
            profileImagePath: $profileImagePath,
            biography: Biography::fromString($request->biography),
            presentationStyle: PresentationStyle::fromString($request->presentationStyle),
            category: $category,
            description: AvatarDescription::fromString(''),
            media: [new AvatarMedia($profileMedia, AvatarMediaType::PROFILE)],
            voiceId: $request->voiceId ? VoiceId::fromString($request->voiceId) : null,
        );

        $this->avatarRepository->save($avatar);

        return new CreateAvatarResponse();
    }
}
