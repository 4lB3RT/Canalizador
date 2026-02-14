<?php

declare(strict_types=1);

namespace Canalizador\Clip\Infrastructure\DAO;

use Illuminate\Database\Eloquent\Model;

class ClipDAO extends Model
{
    protected $table = 'clips';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'video_id',
        'sequence',
        'generation_id',
        'script',
        'status',
        'local_path',
        'video_uri',
        'created_at',
        'completed_at',
    ];

    protected $casts = [
        'sequence' => 'integer',
        'created_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public $timestamps = false;
}
