<?php

declare(strict_types=1);

namespace Helmreel\YouTube\Video\Application\UseCases\GetVideos;

use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;
use Helmreel\Shared\Shared\Domain\ValueObjects\Pagination;
use Helmreel\Shared\Shared\Domain\ValueObjects\Search;
use Helmreel\YouTube\Channel\Domain\ValueObjects\ChannelId;
use Helmreel\YouTube\Video\Domain\ValueObjects\Category;

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
