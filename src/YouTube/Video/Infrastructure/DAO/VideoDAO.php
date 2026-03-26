<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Infrastructure\DAO;

use Illuminate\Database\Eloquent\Model;

class VideoDAO extends Model
{
    protected $table = 'youtube_videos';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'title',
        'url',
        'published_at',
        'local_path',
        'audio_path',
        'transcription',
        'published_short_ids',
        'channel_id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'published_at'        => 'datetime',
        'transcription'       => 'array',
        'published_short_ids' => 'array',
        'created_at'          => 'datetime',
        'updated_at'          => 'datetime',
    ];
}
