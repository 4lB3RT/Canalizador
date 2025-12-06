<?php

declare(strict_types=1);

namespace Canalizador\Video\Application\UseCases;

use Canalizador\Video\Domain\Repositories\VideoContentRetriever;

final readonly class RetrieveVideoContent
{
    public function __construct(
        private VideoContentRetriever $videoContentRetriever,
    ) {
    }

    public function execute(RetrieveVideoContentRequest $request): RetrieveVideoContentResponse
    {
        $videoPath = $this->videoContentRetriever->retrieve($request->videoId);

        return new RetrieveVideoContentResponse($videoPath);
    }
}
