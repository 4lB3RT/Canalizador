<?php

declare(strict_types=1);

namespace Helmreel\YouTube\Channel\Application\UseCases\GetChannelVideos;

use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;
use Helmreel\Shared\Shared\Domain\ValueObjects\Pagination;
use Helmreel\YouTube\Channel\Domain\ValueObjects\ChannelId;
use Helmreel\YouTube\Video\Domain\ValueObjects\Category;

final readonly class GetChannelVideosRequest
{
    public function __construct(
        public ChannelId $channelId,
        public IntegerId $userId,
        public Pagination $pagination,
        public ?Category $category = null,
    ) {
    }
}
