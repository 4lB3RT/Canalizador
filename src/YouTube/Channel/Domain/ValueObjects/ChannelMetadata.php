<?php

declare(strict_types=1);

namespace Helmreel\YouTube\Channel\Domain\ValueObjects;

use Helmreel\Shared\Shared\Domain\ValueObjects\Country;
use Helmreel\Shared\Shared\Domain\ValueObjects\Description;
use Helmreel\Shared\Shared\Domain\ValueObjects\Title;

final readonly class ChannelMetadata
{
    public function __construct(
        private Country $country,
        private ChannelBrand $channelBrand,
        private Description $description,
        private Title $title,
    ) {
    }

    public function country(): Country
    {
        return $this->country;
    }

    public function channelBrand(): ChannelBrand
    {
        return $this->channelBrand;
    }

    public function description(): Description
    {
        return $this->description;
    }

    public function title(): Title
    {
        return $this->title;
    }
}

