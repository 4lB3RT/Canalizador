<?php

declare(strict_types=1);

namespace Helmreel\Shared\Media\Application\UseCases\GetMediaFile;

use Helmreel\Shared\Media\Domain\Entities\Media;
use Helmreel\Shared\Media\Domain\Exceptions\MediaNotFound;
use Helmreel\Shared\Media\Domain\Repositories\MediaRepository;
use Helmreel\Shared\Media\Domain\ValueObjects\MediaId;

final readonly class GetMediaFile
{
    public function __construct(
        private MediaRepository $mediaRepository,
    ) {
    }

    /**
     * @throws MediaNotFound
     */
    public function execute(string $mediaId, int $userId): Media
    {
        $media = $this->mediaRepository->findById(MediaId::fromString($mediaId));

        if ($media->userId()->value() !== $userId) {
            throw MediaNotFound::withId($mediaId);
        }

        return $media;
    }
}
