<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Voice\Infrastructure\DAO;

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
        'user_id',
        'name',
        'source_audio_path',
        'converted_audio_path',
        'platform_id',
        'stability',
        'similarity_boost',
        'style',
        'speed',
        'use_speaker_boost',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'stability' => 'float',
        'similarity_boost' => 'float',
        'style' => 'float',
        'speed' => 'float',
        'use_speaker_boost' => 'boolean',
    ];
}
