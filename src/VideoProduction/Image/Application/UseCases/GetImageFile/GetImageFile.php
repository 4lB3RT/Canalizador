<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Image\Application\UseCases\GetImageFile;

use Helmreel\VideoProduction\Image\Domain\Exceptions\ImageNotFound;
use Helmreel\VideoProduction\Image\Domain\Repositories\ImageRepository;
use Helmreel\VideoProduction\Image\Domain\ValueObjects\ImageId;

final readonly class GetImageFile
{
    public function __construct(
        private ImageRepository $imageRepository,
    ) {
    }

    /**
     * @throws ImageNotFound
     */
    public function execute(string $imageId, int $userId): string
    {
        $image = $this->imageRepository->findById(ImageId::fromString($imageId));

        if ($image->userId()->value() !== $userId) {
            throw ImageNotFound::withId($imageId);
        }

        return $image->path()->value();
    }
}
