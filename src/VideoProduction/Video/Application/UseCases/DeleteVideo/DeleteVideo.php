<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Video\Application\UseCases\DeleteVideo;

use Helmreel\VideoProduction\Clip\Domain\Repositories\ClipRepository;
use Helmreel\Shared\Media\Domain\Repositories\MediaRepository;
use Helmreel\VideoProduction\Video\Domain\Exceptions\VideoNotFound;
use Helmreel\VideoProduction\Video\Domain\Repositories\VideoRepository;
use Helmreel\VideoProduction\Video\Domain\ValueObjects\VideoId;
use Illuminate\Support\Facades\File;

final readonly class DeleteVideo
{
    public function __construct(
        private VideoRepository $videoRepository,
        private ClipRepository $clipRepository,
        private MediaRepository $mediaRepository,
    ) {
    }

    /**
     * @throws VideoNotFound
     */
    public function execute(string $videoId, int $userId): void
    {
        $id = VideoId::fromString($videoId);
        $video = $this->videoRepository->findById($id);

        if ($video->userId()->value() !== $userId) {
            throw VideoNotFound::withId($videoId);
        }

        foreach ($this->clipRepository->findByVideoId($id) as $clip) {
            $this->deleteFile($clip->localPath()?->value());
        }
        $this->clipRepository->deleteByVideoId($id);

        $this->deleteFile($video->videoLocalPath()?->value());

        $mediaId = $video->mediaId();
        if ($mediaId !== null) {
            $media = $this->mediaRepository->findById($mediaId);
            $this->deleteFile($media->path()->value());
            $this->mediaRepository->delete($mediaId);
        }

        $this->videoRepository->delete($id);
    }

    private function deleteFile(?string $path): void
    {
        if ($path !== null && $path !== '' && File::exists($path)) {
            File::delete($path);
        }
    }
}
