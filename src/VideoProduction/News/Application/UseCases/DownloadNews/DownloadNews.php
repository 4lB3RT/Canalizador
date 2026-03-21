<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\News\Application\UseCases\DownloadNews;

use Canalizador\VideoProduction\News\Domain\Entities\News;
use Canalizador\VideoProduction\News\Domain\Repositories\NewsProvider;
use Canalizador\VideoProduction\News\Domain\Repositories\NewsRepository;

final readonly class DownloadNews
{
    public function __construct(
        private NewsProvider $newsProvider,
        private NewsRepository $newsRepository,
    ) {
    }

    /**
     * @return News[]
     */
    public function execute(): array
    {
        $newsList = $this->newsProvider->fetch();

        foreach ($newsList as $news) {
            $this->newsRepository->save($news);
        }

        return $newsList;
    }
}
