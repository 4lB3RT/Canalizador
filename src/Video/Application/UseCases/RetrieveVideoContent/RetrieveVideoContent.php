<?php

declare(strict_types=1);

namespace Canalizador\Video\Application\UseCases\RetrieveVideoContent;

use Canalizador\Shared\Domain\ValueObjects\LocalPath;
use Canalizador\Video\Domain\Exceptions\VideoGenerationFailed;
use Canalizador\Video\Domain\Exceptions\VideoNotFound;
use Canalizador\Video\Domain\Repositories\VideoContentRetriever;
use Canalizador\Video\Domain\Repositories\VideoRepository;
use Canalizador\Video\Domain\ValueObjects\VideoId;
use Illuminate\Http\Client\ConnectionException;

final readonly class RetrieveVideoContent
{
    public function __construct(
        private VideoContentRetriever $videoContentRetriever,
        private VideoRepository $videoRepository,
    ) {
    }

    /**
     * @throws VideoNotFound
     * @throws VideoGenerationFailed
     * @throws ConnectionException
     */
    public function execute(RetrieveVideoContentRequest $request): RetrieveVideoContentResponse
    {
        $videoId = new VideoId($request->videoId);
        $video = $this->videoRepository->findById($videoId);

        $this->videoContentRetriever->retrieve($video);
        $localPath = new LocalPath(storage_path('tmp' . DIRECTORY_SEPARATOR . $video->id()->value()) . '.mp4');

        $video->updateVideoLocalPath($localPath);
        $this->videoRepository->save($video);

        return new RetrieveVideoContentResponse($video->videoLocalPath());
    }
}
