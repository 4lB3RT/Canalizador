<?php

declare(strict_types=1);

namespace Canalizador\Avatar\Application\UseCases\UpdateAvatar;

use Canalizador\Avatar\Domain\Repositories\AvatarRepository;
use Canalizador\Avatar\Domain\ValueObjects\AvatarDescription;
use Canalizador\Avatar\Domain\ValueObjects\AvatarId;
use Canalizador\Avatar\Domain\ValueObjects\Biography;
use Canalizador\Avatar\Domain\ValueObjects\PresentationStyle;
use Canalizador\Voice\Domain\Repositories\VoiceRepository;
use Canalizador\Voice\Domain\ValueObjects\VoiceId;
use InvalidArgumentException;

final readonly class UpdateAvatar
{
    public function __construct(
        private AvatarRepository $avatarRepository,
        private VoiceRepository $voiceRepository,
    ) {
    }

    public function execute(
        string $avatarId,
        ?string $voiceId = null,
        ?string $biography = null,
        ?string $presentationStyle = null,
        ?string $description = null,
    ): array {
        $avatar = $this->avatarRepository->findById(AvatarId::fromString($avatarId));

        if ($voiceId !== null) {
            $voice = $this->voiceRepository->findById(VoiceId::fromString($voiceId));

            if ($voice === null) {
                throw new InvalidArgumentException("Voice with id '{$voiceId}' not found");
            }

            $avatar->updateVoiceId($voice->id());
        }

        if ($biography !== null) {
            $avatar->updateBiography(Biography::fromString($biography));
        }

        if ($presentationStyle !== null) {
            $avatar->updatePresentationStyle(PresentationStyle::fromString($presentationStyle));
        }

        if ($description !== null) {
            $avatar->updateDescription(AvatarDescription::fromString($description));
        }

        $this->avatarRepository->save($avatar);

        return $avatar->toArray();
    }
}
