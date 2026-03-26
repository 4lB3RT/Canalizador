<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Video\Application\UseCases\RetrieveVideoContent;

use Canalizador\Shared\Domain\Services\Clock;
use Canalizador\Shared\Domain\ValueObjects\LocalPath;
use Canalizador\VideoProduction\Video\Domain\Exceptions\VideoGenerationFailed;
use Canalizador\VideoProduction\Video\Domain\Exceptions\VideoNotFound;
use Canalizador\VideoProduction\Video\Domain\Repositories\VideoContentRetriever;
use Canalizador\VideoProduction\Video\Domain\Repositories\VideoRepository;
use Canalizador\VideoProduction\Video\Domain\ValueObjects\VideoId;
use Illuminate\Http\Client\ConnectionException;

final readonly class RetrieveVideoContent
{
    public function __construct(
        private VideoContentRetriever $videoContentRetriever,
        private VideoRepository $videoRepository,
        private Clock $clock,
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

        $video->markAsCompleted($localPath, $this->clock->now());
        $this->videoRepository->save($video);

        return new RetrieveVideoContentResponse($video->videoLocalPath());
    }
}
