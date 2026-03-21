<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\News\Infrastructure\DAO;

use Illuminate\Database\Eloquent\Model;

class NewsDAO extends Model
{
    protected $table = 'news';

    protected $primaryKey = 'news_id';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'news_id',
        'title',
        'description',
        'published_at',
        'created_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'created_at' => 'datetime',
    ];
}
