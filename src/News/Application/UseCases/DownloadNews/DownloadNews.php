<?php

declare(strict_types=1);

namespace Canalizador\News\Application\UseCases\DownloadNews;

use Canalizador\News\Domain\Entities\News;
use Canalizador\News\Domain\Repositories\NewsProvider;
use Canalizador\News\Domain\Repositories\NewsRepository;

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
