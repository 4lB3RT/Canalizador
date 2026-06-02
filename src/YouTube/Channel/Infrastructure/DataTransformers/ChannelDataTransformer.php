<?php

declare(strict_types = 1);

namespace Helmreel\YouTube\Channel\Infrastructure\DataTransformers;

use Helmreel\Shared\Shared\Domain\ValueObjects\Country;
use Helmreel\Shared\Shared\Domain\ValueObjects\Description;
use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\DateTime;
use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;
use Helmreel\Shared\Shared\Domain\ValueObjects\Title;
use Helmreel\Shared\Shared\Domain\ValueObjects\Url;
use Helmreel\YouTube\Channel\Domain\Entities\Channel;
use Helmreel\YouTube\Channel\Domain\ValueObjects\ChannelBrand;
use Helmreel\YouTube\Channel\Domain\ValueObjects\ChannelId;
use Helmreel\YouTube\Channel\Domain\ValueObjects\PrivacyStatus;
use DateTimeImmutable;

class ChannelDataTransformer
{
    public static function fromArray(array $data): Channel
    {
        if (empty($data['country'])) {
            throw new \RuntimeException("Channel {$data['id']} does not have a country. It must be set before loading.");
        }

        if (empty($data['channel_brand'])) {
            throw new \RuntimeException("Channel {$data['id']} does not have a channel brand. It must be set before loading.");
        }

        $description = $data['description'] ?? '';
        if (strlen($description) > 1000) {
            $description = substr($description, 0, 1000);
        }

        return new Channel(
            id:              ChannelId::fromString($data['id']),
            userId:          new IntegerId((int) $data['user_id']),
            title:           Title::fromString($data['title']),
            description:     Description::fromString($description),
            publishedAt:     new DateTime(DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $data['published_at'])),
            viewCount:       (int) $data['view_count'],
            subscriberCount: (int) $data['subscriber_count'],
            videoCount:      (int) $data['video_count'],
            privacyStatus:   PrivacyStatus::from($data['privacy_status']),
            country:         Country::fromString($data['country']),
            channelBrand:    ChannelBrand::fromString($data['channel_brand']),
            autoSync:        (bool) ($data['auto_sync'] ?? false),
            autoPublish:     (bool) ($data['auto_publish'] ?? false),
            customUrl:       isset($data['custom_url'])    ? Url::fromString($data['custom_url'])          : null,
            thumbnailUrl:    isset($data['thumbnail_url']) ? Url::fromString($data['thumbnail_url'])       : null,
        );
    }
}
