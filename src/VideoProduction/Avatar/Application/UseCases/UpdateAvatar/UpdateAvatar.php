<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Avatar\Application\UseCases\UpdateAvatar;

use Helmreel\Shared\Shared\Domain\Services\Clock;
use Helmreel\Shared\Shared\Domain\ValueObjects\LocalPath;
use Helmreel\VideoProduction\Avatar\Domain\Entities\AvatarMedia;
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
use Helmreel\VideoProduction\Voice\Domain\Repositories\VoiceRepository;
use Helmreel\VideoProduction\Voice\Domain\ValueObjects\VoiceId;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use InvalidArgumentException;

final readonly class UpdateAvatar
{
    public function __construct(
        private AvatarRepository $avatarRepository,
        private VoiceRepository $voiceRepository,
        private MediaRepository $mediaRepository,
        private Clock $clock,
    ) {
    }

    public function execute(UpdateAvatarRequest $request): array
    {
        $avatar = $this->avatarRepository->findById(AvatarId::fromString($request->avatarId));

        if ($avatar->userId()->value() !== $request->userId) {
            throw \Helmreel\VideoProduction\Avatar\Domain\Exceptions\AvatarNotFound::withId($request->avatarId);
        }

        if ($request->name !== null) {
            $avatar->updateName(AvatarName::fromString($request->name));
        }

        if ($request->category !== null) {
            $avatar->updateCategory(Category::fromString($request->category));
        }

        if ($request->presentationStyle !== null) {
            $avatar->updatePresentationStyle(PresentationStyle::fromString($request->presentationStyle));
        }

        if ($request->biography !== null) {
            $avatar->updateBiography(Biography::fromString($request->biography));
        }

        if ($request->description !== null) {
            $avatar->updateDescription(AvatarDescription::fromString($request->description));
        }

        if ($request->clearVoice) {
            $avatar->clearVoiceId();
        } elseif ($request->voiceId !== null) {
            $voice = $this->voiceRepository->findById(VoiceId::fromString($request->voiceId));

            if ($voice === null) {
                throw new InvalidArgumentException("Voice with id '{$request->voiceId}' not found");
            }

            $avatar->updateVoiceId($voice->id());
        }

        if ($request->profileImagePath !== null) {
            $this->replaceProfileImage($avatar, $request->profileImagePath);
        }

        $this->avatarRepository->save($avatar);

        return $avatar->toArray();
    }

    private function replaceProfileImage(\Helmreel\VideoProduction\Avatar\Domain\Entities\Avatar $avatar, string $tmpPath): void
    {
        foreach ($avatar->media() as $m) {
            if ($m->type() === AvatarMediaType::PROFILE) {
                $oldPath = $m->media()->path()->value();
                if ($oldPath !== '' && File::exists($oldPath)) {
                    File::delete($oldPath);
                }
                $this->mediaRepository->delete($m->media()->id());
            }
        }

        $permanentDir = storage_path('app/avatars');
        if (!File::exists($permanentDir)) {
            File::makeDirectory($permanentDir, 0755, true);
        }

        $extension = pathinfo($tmpPath, PATHINFO_EXTENSION);
        $permanentPath = $permanentDir . '/' . $avatar->id()->value() . '.' . $extension;
        File::move($tmpPath, $permanentPath);

        $profileImagePath = LocalPath::fromString($permanentPath);
        $avatar->updateProfileImagePath($profileImagePath);

        $profileMedia = new Media(
            id: MediaId::fromString(Str::uuid()->toString()),
            userId: $avatar->userId(),
            type: MediaType::IMAGE,
            path: $profileImagePath,
            createdAt: $this->clock->now(),
        );
        $this->mediaRepository->save($profileMedia);

        $remaining = array_filter(
            $avatar->media(),
            fn (AvatarMedia $m) => $m->type() !== AvatarMediaType::PROFILE,
        );

        $avatar->updateMedia([new AvatarMedia($profileMedia, AvatarMediaType::PROFILE), ...$remaining]);
    }
}
