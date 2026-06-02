<?php

declare(strict_types=1);

namespace Helmreel\Shared\Shared\Domain\ValueObjects;

final readonly class Pagination
{
    public function __construct(
        private Page $page,
        private PerPage $perPage,
    ) {
    }

    public static function fromInts(int $page, int $perPage): self
    {
        return new self(Page::fromInt($page), PerPage::fromInt($perPage));
    }

    public function page(): Page
    {
        return $this->page;
    }

    public function perPage(): PerPage
    {
        return $this->perPage;
    }

    public function offset(): int
    {
        return ($this->page->value() - 1) * $this->perPage->value();
    }

    public function limit(): int
    {
        return $this->perPage->value();
    }
}
