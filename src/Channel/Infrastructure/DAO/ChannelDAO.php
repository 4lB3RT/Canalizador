<?php

declare(strict_types=1);

namespace Canalizador\Channel\Infrastructure\DAO;

use Illuminate\Database\Eloquent\Model;

class ChannelDAO extends Model
{
    protected $table = 'channels';

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
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'view_count' => 'integer',
        'subscriber_count' => 'integer',
        'video_count' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}

