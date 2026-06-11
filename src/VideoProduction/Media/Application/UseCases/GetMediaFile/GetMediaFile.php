<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Media\Application\UseCases\GetMediaFile;

use Helmreel\VideoProduction\Media\Domain\Entities\Media;
use Helmreel\VideoProduction\Media\Domain\Exceptions\MediaNotFound;
use Helmreel\VideoProduction\Media\Domain\Repositories\MediaRepository;
use Helmreel\VideoProduction\Media\Domain\ValueObjects\MediaId;

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
