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
use Helmreel\Shared\Media\Domain\Entities\Media;
use Helmreel\Shared\Media\Domain\Repositories\MediaRepository;
use Helmreel\Shared\Media\Domain\ValueObjects\MediaId;
use Helmreel\Shared\Media\Domain\ValueObjects\MediaType;
use Helmreel\VideoProduction\Voice\Domain\Entities\Voice;
use Helmreel\VideoProduction\Voice\Domain\Repositories\VoiceRepository;
use Helmreel\VideoProduction\Voice\Domain\ValueObjects\VoiceId;
use Helmreel\VideoProduction\Voice\Domain\ValueObjects\VoiceSettings;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

final readonly class CreateAvatar
{
    public function __construct(
        private AvatarFactory $avatarFactory,
        private AvatarRepository $avatarRepository,
        private MediaRepository $mediaRepository,
        private VoiceRepository $voiceRepository,
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
            voiceId: $this->resolveVoiceId($request, $userId),
        );

        $this->avatarRepository->save($avatar);

        return new CreateAvatarResponse();
    }

    private function resolveVoiceId(CreateAvatarRequest $request, IntegerId $userId): ?VoiceId
    {
        if ($request->voicePlatformId !== null) {
            return $this->persistCatalogVoice($request, $userId);
        }

        if ($request->voiceId !== null) {
            $this->applySettingsToExistingVoice($request);

            return VoiceId::fromString($request->voiceId);
        }

        return null;
    }

    private function persistCatalogVoice(CreateAvatarRequest $request, IntegerId $userId): VoiceId
    {
        $voice = new Voice(
            id: new VoiceId(Str::uuid()->toString()),
            userId: $userId,
            name: $request->voiceCatalogName ?? 'Voz ElevenLabs',
            sourceAudioPath: null,
            createdAt: $this->clock->now(),
            platformId: $request->voicePlatformId,
            settings: $this->buildSettings($request->voiceSettings),
        );

        $this->voiceRepository->save($voice);

        return $voice->id();
    }

    private function applySettingsToExistingVoice(CreateAvatarRequest $request): void
    {
        if ($request->voiceSettings === null) {
            return;
        }

        $voice = $this->voiceRepository->findById(VoiceId::fromString($request->voiceId));
        if ($voice === null) {
            return;
        }

        $voice->updateSettings($this->buildSettings($request->voiceSettings));
        $this->voiceRepository->save($voice);
    }

    private function buildSettings(?array $settings): VoiceSettings
    {
        if ($settings === null) {
            return new VoiceSettings();
        }

        return new VoiceSettings(
            stability: (float) ($settings['stability'] ?? 0.5),
            similarityBoost: (float) ($settings['similarity_boost'] ?? 0.75),
            style: (float) ($settings['style'] ?? 0.0),
            speed: (float) ($settings['speed'] ?? 1.0),
            useSpeakerBoost: (bool) ($settings['use_speaker_boost'] ?? true),
        );
    }
}
