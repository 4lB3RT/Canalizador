<?php

declare(strict_types=1);

namespace Helmreel\Shared\Media\Infrastructure\DAO;

use Illuminate\Database\Eloquent\Model;

class MediaDAO extends Model
{
    protected $table = 'media';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'user_id',
        'type',
        'path',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
