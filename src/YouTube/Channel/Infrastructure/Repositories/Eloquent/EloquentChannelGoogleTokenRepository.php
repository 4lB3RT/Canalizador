<?php

declare(strict_types=1);

namespace Helmreel\YouTube\Channel\Infrastructure\Repositories\Eloquent;

use Helmreel\YouTube\Channel\Domain\Entities\ChannelGoogleToken;
use Helmreel\YouTube\Channel\Domain\Exceptions\ChannelGoogleTokenNotFound;
use Helmreel\YouTube\Channel\Domain\Repositories\ChannelGoogleTokenRepository;
use Helmreel\YouTube\Channel\Domain\ValueObjects\ChannelId;
use Helmreel\YouTube\Channel\Infrastructure\DAO\ChannelGoogleTokenDAO;
use DateTimeImmutable;

final class EloquentChannelGoogleTokenRepository implements ChannelGoogleTokenRepository
{
    public function findByChannelId(ChannelId $channelId): ChannelGoogleToken
    {
        $model = ChannelGoogleTokenDAO::where('channel_id', $channelId->value())->first();

        if (!$model) {
            throw ChannelGoogleTokenNotFound::forChannelId($channelId->value());
        }

        return new ChannelGoogleToken(
            channelId:    ChannelId::fromString((string) $model->channel_id),
            accessToken:  (string) $model->access_token,
            refreshToken: $model->refresh_token ? (string) $model->refresh_token : null,
            expiresAt:    $model->expires_at ? DateTimeImmutable::createFromMutable($model->expires_at->toDateTime()) : null,
            scope:        $model->scope ? (string) $model->scope : null,
            tokenType:    $model->token_type ? (string) $model->token_type : null,
        );
    }

    public function save(ChannelGoogleToken $token): void
    {
        ChannelGoogleTokenDAO::updateOrCreate(
            ['channel_id' => $token->channelId->value()],
            [
                'access_token'  => $token->accessToken,
                'refresh_token' => $token->refreshToken,
                'expires_at'    => $token->expiresAt,
                'scope'         => $token->scope,
                'token_type'    => $token->tokenType,
            ]
        );
    }

    public function deleteByChannelId(ChannelId $channelId): void
    {
        ChannelGoogleTokenDAO::where('channel_id', $channelId->value())->delete();
    }
}
