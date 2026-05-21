<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Channel\Application\UseCases\GetChannelVideos;

use Canalizador\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;
use Canalizador\Shared\Shared\Domain\ValueObjects\Pagination;
use Canalizador\YouTube\Channel\Domain\ValueObjects\ChannelId;
use Canalizador\YouTube\Video\Domain\ValueObjects\Category;

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
