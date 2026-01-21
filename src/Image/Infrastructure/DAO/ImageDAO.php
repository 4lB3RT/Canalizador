<?php

declare(strict_types=1);

namespace Canalizador\Image\Infrastructure\DAO;

use Illuminate\Database\Eloquent\Model;

class ImageDAO extends Model
{
    protected $table = 'images';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'user_id',
        'path',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
