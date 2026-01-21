<?php

declare(strict_types=1);

namespace Canalizador\Avatar\Infrastructure\DAO;

use Illuminate\Database\Eloquent\Model;

class AvatarDAO extends Model
{
    protected $table = 'avatars';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'user_id',
        'name',
        'profile_image_path',
        'biography',
        'presentation_style',
        'description',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}

