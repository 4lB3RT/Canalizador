<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Avatar\Application\UseCases\DeleteAvatar;

use Helmreel\VideoProduction\Avatar\Domain\Exceptions\AvatarNotFound;
use Helmreel\VideoProduction\Avatar\Domain\Repositories\AvatarRepository;
use Helmreel\VideoProduction\Avatar\Domain\ValueObjects\AvatarId;
use Helmreel\Shared\Media\Domain\Repositories\MediaRepository;
use Illuminate\Support\Facades\File;

final readonly class DeleteAvatar
{
    public function __construct(
        private AvatarRepository $avatarRepository,
        private MediaRepository $mediaRepository,
    ) {
    }

    /**
     * @throws AvatarNotFound
     */
    public function execute(string $avatarId, int $userId): void
    {
        $id = AvatarId::fromString($avatarId);
        $avatar = $this->avatarRepository->findById($id);

        if ($avatar->userId()->value() !== $userId) {
            throw AvatarNotFound::withId($avatarId);
        }

        foreach ($avatar->media() as $m) {
            $this->deleteFile($m->media()->path()->value());
            $this->mediaRepository->delete($m->media()->id());
        }

        $this->avatarRepository->delete($id);
    }

    private function deleteFile(string $path): void
    {
        if ($path !== '' && File::exists($path)) {
            File::delete($path);
        }
    }
}
