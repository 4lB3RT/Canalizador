<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Application\UseCases\GetVideos;

use Canalizador\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;
use Canalizador\Shared\Shared\Domain\ValueObjects\Pagination;
use Canalizador\Shared\Shared\Domain\ValueObjects\Search;
use Canalizador\YouTube\Channel\Domain\ValueObjects\ChannelId;
use Canalizador\YouTube\Video\Domain\ValueObjects\Category;

final readonly class GetVideosRequest
{
    public function __construct(
        public IntegerId $userId,
        public Pagination $pagination,
        public ?Category $category = null,
        public ?ChannelId $channelId = null,
        public ?Search $search = null,
    ) {
    }
}
