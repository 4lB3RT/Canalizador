<?php

declare(strict_types = 1);

namespace Canalizador\Video\Infrastructure\DAO;

use Illuminate\Database\Eloquent\Model;

class VideoDAO extends Model
{
    protected $table = 'generated_videos';

    protected $primaryKey = 'generated_video_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'generated_video_id',
        'script_id',
        'title',
        'generation_id',
        'video_local_path',
        'audio_local_path',
        'created_at',
        'completed_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public $timestamps = false;
}
