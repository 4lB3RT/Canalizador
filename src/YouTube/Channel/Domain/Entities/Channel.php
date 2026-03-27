<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Channel\Domain\Entities;

use Canalizador\Shared\Shared\Domain\ValueObjects\Essentials\DateTime;
use Canalizador\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;
use Canalizador\YouTube\Channel\Domain\ValueObjects\ChannelBrand;
use Canalizador\YouTube\Channel\Domain\ValueObjects\ChannelId;
use Canalizador\YouTube\Channel\Domain\ValueObjects\Country;
use Canalizador\YouTube\Channel\Domain\ValueObjects\CustomUrl;
use Canalizador\YouTube\Channel\Domain\ValueObjects\Description;
use Canalizador\YouTube\Channel\Domain\ValueObjects\PrivacyStatus;
use Canalizador\YouTube\Channel\Domain\ValueObjects\SubscriberCount;
use Canalizador\YouTube\Channel\Domain\ValueObjects\ThumbnailUrl;
use Canalizador\YouTube\Channel\Domain\ValueObjects\Title;
use Canalizador\YouTube\Channel\Domain\ValueObjects\VideoCount;
use Canalizador\YouTube\Channel\Domain\ValueObjects\ViewCount;

final class Channel
{
    public function __construct(
        private readonly ChannelId $id,
        private readonly IntegerId $userId,
        private Title $title,
        private Description $description,
        private readonly DateTime $publishedAt,
        private readonly ViewCount $viewCount,
        private readonly SubscriberCount $subscriberCount,
        private readonly VideoCount $videoCount,
        private readonly PrivacyStatus $privacyStatus,
        private Country $country,
        private ChannelBrand $channelBrand,
        private readonly ?CustomUrl $customUrl = null,
        private readonly ?ThumbnailUrl $thumbnailUrl = null,
    ) {
    }

    public function id(): ChannelId
    {
        return $this->id;
    }

    public function userId(): IntegerId
    {
        return $this->userId;
    }

    public function title(): Title
    {
        return $this->title;
    }

    public function description(): Description
    {
        return $this->description;
    }

    public function publishedAt(): DateTime
    {
        return $this->publishedAt;
    }

    public function viewCount(): ViewCount
    {
        return $this->viewCount;
    }

    public function subscriberCount(): SubscriberCount
    {
        return $this->subscriberCount;
    }

    public function videoCount(): VideoCount
    {
        return $this->videoCount;
    }

    public function privacyStatus(): PrivacyStatus
    {
        return $this->privacyStatus;
    }

    public function customUrl(): ?CustomUrl
    {
        return $this->customUrl;
    }

    public function thumbnailUrl(): ?ThumbnailUrl
    {
        return $this->thumbnailUrl;
    }

    public function country(): Country
    {
        return $this->country;
    }

    public function channelBrand(): ChannelBrand
    {
        return $this->channelBrand;
    }

    public function updateCountry(Country $country): void
    {
        $this->country = $country;
    }

    public function updateChannelBrand(ChannelBrand $channelBrand): void
    {
        $this->channelBrand = $channelBrand;
    }

    public function updateDescription(Description $description): void
    {
        $this->description = $description;
    }

    public function updateTitle(Title $title): void
    {
        $this->title = $title;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id->value(),
            'user_id' => $this->userId->value(),
            'title' => $this->title->value(),
            'description' => $this->description->value(),
            'custom_url' => $this->customUrl?->value(),
            'published_at' => $this->publishedAt->value()->format('Y-m-d H:i:s'),
            'thumbnail_url' => $this->thumbnailUrl?->value(),
            'country' => $this->country->value(),
            'view_count' => $this->viewCount->value(),
            'subscriber_count' => $this->subscriberCount->value(),
            'video_count' => $this->videoCount->value(),
            'privacy_status' => $this->privacyStatus->value,
            'channel_brand' => $this->channelBrand->value(),
        ];
    }
}

