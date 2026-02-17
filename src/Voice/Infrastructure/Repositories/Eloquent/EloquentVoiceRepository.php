<?php

declare(strict_types=1);

namespace Canalizador\Voice\Infrastructure\Repositories\Eloquent;

use Canalizador\Shared\Domain\ValueObjects\DateTime;
use Canalizador\Shared\Domain\ValueObjects\LocalPath;
use Canalizador\Voice\Domain\Entities\Voice;
use Canalizador\Voice\Domain\Repositories\VoiceRepository;
use Canalizador\Voice\Domain\ValueObjects\VoiceId;
use Canalizador\Voice\Infrastructure\DAO\VoiceDAO;

final class EloquentVoiceRepository implements VoiceRepository
{
    public function save(Voice $voice): void
    {
        VoiceDAO::updateOrCreate(
            ['voice_id' => $voice->id()->value()],
            [
                'name' => $voice->name(),
                'source_audio_path' => $voice->sourceAudioPath()->value(),
                'converted_audio_path' => $voice->convertedAudioPath()?->value(),
                'platform_id' => $voice->platformId(),
                'created_at' => $voice->createdAt()->value(),
            ]
        );
    }

    public function findById(VoiceId $id): ?Voice
    {
        $dao = VoiceDAO::query()->find($id->value());

        if ($dao === null) {
            return null;
        }

        return new Voice(
            id: new VoiceId($dao->voice_id),
            name: $dao->name,
            sourceAudioPath: new LocalPath($dao->source_audio_path),
            createdAt: new DateTime($dao->created_at->toDateTimeImmutable()),
            platformId: $dao->platform_id,
            convertedAudioPath: $dao->converted_audio_path ? new LocalPath($dao->converted_audio_path) : null,
        );
    }
}
