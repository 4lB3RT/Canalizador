<?php

declare(strict_types=1);

namespace Helmreel\YouTube\Channel\Infrastructure\DAO;

use Illuminate\Database\Eloquent\Model;

class ChannelGoogleTokenDAO extends Model
{
    protected $table = 'channel_google_tokens';

    protected $fillable = [
        'channel_id',
        'access_token',
        'refresh_token',
        'expires_at',
        'scope',
        'token_type',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];
}
