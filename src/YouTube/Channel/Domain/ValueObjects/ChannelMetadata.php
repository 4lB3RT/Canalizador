<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Channel\Domain\ValueObjects;

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

