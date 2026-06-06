<?php

declare(strict_types=1);

namespace Helmreel\YouTube\Channel\Infrastructure\DAO;

use Illuminate\Database\Eloquent\Model;

class ChannelDAO extends Model
{
    protected $table = 'youtube_channels';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'user_id',
        'title',
        'description',
        'custom_url',
        'published_at',
        'thumbnail_url',
        'country',
        'view_count',
        'subscriber_count',
        'video_count',
        'privacy_status',
        'channel_brand',
        'auto_sync',
        'auto_publish',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'token_scope',
        'token_type',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'view_count' => 'integer',
        'subscriber_count' => 'integer',
        'video_count' => 'integer',
        'auto_sync' => 'boolean',
        'auto_publish' => 'boolean',
        'access_token' => 'encrypted',
        'refresh_token' => 'encrypted',
        'token_expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}

