<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Avatar\Application\UseCases\CreateAvatar;

use Canalizador\VideoProduction\Avatar\Domain\Factories\AvatarFactory;
use Canalizador\VideoProduction\Avatar\Domain\Repositories\AvatarRepository;
use Canalizador\VideoProduction\Avatar\Domain\ValueObjects\AvatarId;
use Canalizador\VideoProduction\Avatar\Domain\ValueObjects\AvatarName;
use Canalizador\VideoProduction\Avatar\Domain\ValueObjects\Biography;
use Canalizador\VideoProduction\Avatar\Domain\ValueObjects\Category;
use Canalizador\VideoProduction\Avatar\Domain\ValueObjects\PresentationStyle;
use Canalizador\VideoProduction\Avatar\Infrastructure\Repositories\OpenAI\OpenAiAvatarRepository;
use Canalizador\Shared\Domain\ValueObjects\IntegerId;
use Canalizador\Shared\Domain\ValueObjects\LocalPath;
use Canalizador\VideoProduction\Voice\Domain\ValueObjects\VoiceId;
use Illuminate\Support\Facades\File;

final readonly class CreateAvatar
{
    public function __construct(
        private AvatarFactory $avatarFactory,
        private AvatarRepository $avatarRepository,
        private OpenAiAvatarRepository $openAiAvatarRepository,
    ) {
    }

    public function execute(CreateAvatarRequest $request): CreateAvatarResponse
    {
        $tmpImagePath = LocalPath::fromString($request->profileImagePath);
        $userId = new IntegerId($request->userId);

        $avatarName = AvatarName::fromString($request->name);
        $biography = Biography::fromString($request->biography);
        $presentationStyle = PresentationStyle::fromString($request->presentationStyle);
        $category = Category::fromString($request->category);

        $metadataResult = $this->openAiAvatarRepository->generateMetadata(
            imagePath: $tmpImagePath,
            avatarName: $avatarName,
            biography: $biography,
            presentationStyle: $presentationStyle,
            userId: $userId,
            category: $category
        );

        $description = $metadataResult->description();
        $images = $metadataResult->images();

        $permanentDir = storage_path('app/avatars');
        if (!File::exists($permanentDir)) {
            File::makeDirectory($permanentDir, 0755, true);
        }

        $extension = pathinfo($request->profileImagePath, PATHINFO_EXTENSION);
        $permanentFilename = $request->avatarId . '.' . $extension;
        $permanentImagePath = $permanentDir . '/' . $permanentFilename;
        File::move($request->profileImagePath, $permanentImagePath);

        $profileImagePath = LocalPath::fromString($permanentImagePath);

        $avatar = $this->avatarFactory->create(
            id: AvatarId::fromString($request->avatarId),
            userId: $userId,
            name: AvatarName::fromString($request->name),
            profileImagePath: $profileImagePath,
            biography: Biography::fromString($request->biography),
            presentationStyle: PresentationStyle::fromString($request->presentationStyle),
            category: $category,
            description: $description,
            images: $images,
            voiceId: $request->voiceId ? VoiceId::fromString($request->voiceId) : null,
        );

        $this->avatarRepository->save($avatar);

        return new CreateAvatarResponse();
    }
}

