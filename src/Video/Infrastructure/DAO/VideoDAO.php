<?php

declare(strict_types = 1);

namespace Canalizador\Video\Infrastructure\DAO;

use Illuminate\Database\Eloquent\Model;

class VideoDAO extends Model
{
    protected $table = 'videos';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
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
