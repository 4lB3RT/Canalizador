<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Application\UseCases\GetVideos;

use Canalizador\Shared\Shared\Domain\ValueObjects\Page;
use Canalizador\Shared\Shared\Domain\ValueObjects\Pagination;
use Canalizador\Shared\Shared\Domain\ValueObjects\Total;
use Canalizador\YouTube\Video\Domain\Entities\VideoCollection;

final readonly class GetVideosResponse
{
    public function __construct(
        public VideoCollection $videos,
        public Total $total,
        public Pagination $pagination,
    ) {
    }

    public function lastPage(): Page
    {
        $perPage = $this->pagination->perPage()->value();
        $total = $this->total->value();

        $value = (int) max(1, (int) ceil($total / $perPage));

        return Page::fromInt($value);
    }
}
