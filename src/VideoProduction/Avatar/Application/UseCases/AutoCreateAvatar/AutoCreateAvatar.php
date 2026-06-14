<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Avatar\Application\UseCases\AutoCreateAvatar;

use Helmreel\Shared\Media\Domain\Entities\Media;
use Helmreel\Shared\Media\Domain\Repositories\MediaRepository;
use Helmreel\Shared\Media\Domain\ValueObjects\MediaId;
use Helmreel\Shared\Media\Domain\ValueObjects\MediaType;
use Helmreel\Shared\Shared\Domain\Services\Clock;
use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;
use Helmreel\VideoProduction\Avatar\Domain\Entities\AvatarMedia;
use Helmreel\VideoProduction\Avatar\Domain\Factories\AvatarFactory;
use Helmreel\VideoProduction\Avatar\Domain\Repositories\AvatarDataGenerator;
use Helmreel\VideoProduction\Avatar\Domain\Repositories\AvatarProfileImageGenerator;
use Helmreel\VideoProduction\Avatar\Domain\Repositories\AvatarRepository;
use Helmreel\VideoProduction\Avatar\Domain\ValueObjects\AvatarDescription;
use Helmreel\VideoProduction\Avatar\Domain\ValueObjects\AvatarId;
use Helmreel\VideoProduction\Avatar\Domain\ValueObjects\AvatarMediaType;
use Helmreel\VideoProduction\Avatar\Domain\ValueObjects\Category;
use Helmreel\VideoProduction\Avatar\Infrastructure\Repositories\OpenAI\OpenAiAvatarRepository;
use Helmreel\VideoProduction\Voice\Domain\Entities\Voice;
use Helmreel\VideoProduction\Voice\Domain\Repositories\VoiceRepository;
use Helmreel\VideoProduction\Voice\Domain\ValueObjects\VoiceId;
use Illuminate\Support\Str;

final readonly class AutoCreateAvatar
{
    public function __construct(
        private AvatarFactory $avatarFactory,
        private AvatarRepository $avatarRepository,
        private MediaRepository $mediaRepository,
        private AvatarDataGenerator $avatarDataGenerator,
        private AvatarProfileImageGenerator $avatarProfileImageGenerator,
        private OpenAiAvatarRepository $openAiAvatarRepository,
        private VoiceRepository $voiceRepository,
        private Clock $clock,
    ) {
    }

    public function execute(AutoCreateAvatarRequest $request): array
    {
        $userId = new IntegerId($request->userId);
        $category = $this->randomCategory();

        $data = $this->avatarDataGenerator->generate($category);

        $profileImagePath = $this->avatarProfileImageGenerator->generate($data, $category);

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
            name: $data->name,
            profileImagePath: $profileImagePath,
            biography: $data->biography,
            presentationStyle: $data->presentationStyle,
            category: $category,
            description: AvatarDescription::fromString(''),
            media: [new AvatarMedia($profileMedia, AvatarMediaType::PROFILE)],
            voiceId: null,
        );

        $description = $this->openAiAvatarRepository->generateAvatarDescription(
            $profileImagePath,
            $data->name,
            $data->biography,
            $data->presentationStyle,
        );
        $avatar->updateDescription($description);

        $voiceId = $this->assignRandomVoice($userId);
        if ($voiceId !== null) {
            $avatar->updateVoiceId($voiceId);
        }

        $this->avatarRepository->save($avatar);

        return $avatar->toArray();
    }

    private function randomCategory(): Category
    {
        $cases = Category::cases();

        return $cases[array_rand($cases)];
    }

    private function assignRandomVoice(IntegerId $userId): ?VoiceId
    {
        $catalogVoices = $this->voiceRepository->get()->items();

        if (empty($catalogVoices)) {
            return null;
        }

        $catalogVoice = $catalogVoices[array_rand($catalogVoices)];
        $platformId = $catalogVoice->platformId();

        if ($platformId === null) {
            return null;
        }

        $voice = new Voice(
            id: new VoiceId(Str::uuid()->toString()),
            userId: $userId,
            name: $catalogVoice->name(),
            sourceAudioPath: null,
            createdAt: $this->clock->now(),
            platformId: $platformId,
        );

        $this->voiceRepository->save($voice);

        return $voice->id();
    }
}
