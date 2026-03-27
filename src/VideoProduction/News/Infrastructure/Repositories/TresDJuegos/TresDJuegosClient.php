<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\News\Infrastructure\Repositories\TresDJuegos;

use Canalizador\Shared\Shared\Domain\Services\HttpClient;
use Canalizador\Shared\Shared\Domain\ValueObjects\Essentials\DateTime;
use Canalizador\VideoProduction\News\Domain\Entities\News;
use Canalizador\VideoProduction\News\Domain\Repositories\NewsProvider;
use Canalizador\VideoProduction\News\Domain\ValueObjects\Description;
use Canalizador\VideoProduction\News\Domain\ValueObjects\NewsId;
use Canalizador\VideoProduction\News\Domain\ValueObjects\PublishedAt;
use Canalizador\VideoProduction\News\Domain\ValueObjects\Title;

final readonly class TresDJuegosClient implements NewsProvider
{
    private const RSS_URL = 'https://www.3djuegos.com/feedburner.xml';

    public function __construct(
        private HttpClient $httpClient,
    ) {
    }

    /**
     * @return News[]
     */
    public function fetch(): array
    {
        $response = $this->httpClient->get(self::RSS_URL, []);

        if (!$response->isSuccessful()) {
            return [];
        }

        $xml = simplexml_load_string($response->body());

        if ($xml === false) {
            return [];
        }

        return $this->parseItems($xml);
    }

    /**
     * @return News[]
     */
    private function parseItems(\SimpleXMLElement $xml): array
    {
        $news = [];
        $now = new DateTime(new \DateTimeImmutable());

        foreach ($xml->channel->item as $item) {
            $guid = (string) $item->guid;

            if ($guid === '') {
                continue;
            }

            $news[] = new News(
                id: NewsId::fromString(md5($guid)),
                title: Title::fromString((string) $item->title),
                description: Description::fromString(
                    trim(strip_tags((string) $item->description))
                ),
                publishedAt: new PublishedAt(
                    new \DateTimeImmutable((string) $item->pubDate)
                ),
                createdAt: $now,
            );
        }

        return $news;
    }
}
