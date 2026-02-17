<?php

declare(strict_types=1);

namespace Canalizador\Voice\Infrastructure\DAO;

use Illuminate\Database\Eloquent\Model;

class VoiceDAO extends Model
{
    protected $table = 'voices';

    protected $primaryKey = 'voice_id';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'voice_id',
        'name',
        'source_audio_path',
        'converted_audio_path',
        'platform_id',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];
}
