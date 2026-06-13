<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Script\Infrastructure\DAO;

use Illuminate\Database\Eloquent\Model;

class ScriptDAO extends Model
{
    protected $table = 'scripts';

    protected $primaryKey = 'script_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'script_id',
        'user_id',
        'category',
        'language',
        'title',
        'content',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public $timestamps = true;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;
}
