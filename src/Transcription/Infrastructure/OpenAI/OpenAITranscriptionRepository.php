<?php

declare(strict_types = 1);

namespace Canalizador\Transcription\Infrastructure\OpenAI;

use Canalizador\Shared\Domain\ValueObjects\Language;
use Canalizador\Transcription\Domain\Entities\Transcription;
use Canalizador\Transcription\Domain\Repositories\TranscriptionRepository;
use Canalizador\Transcription\Domain\ValueObjects\TranscriptionId;
use Canalizador\Video\Domain\ValueObjects\VideoId;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;
use Prism\Prism\ValueObjects\Media\Audio;

final class OpenAITranscriptionRepository implements TranscriptionRepository
{
    private const string MODEL  = 'whisper-1';

    public function findById(TranscriptionId $videoId): ?Transcription
    {
        $audio = Prism::audio()
            ->using(Provider::OpenAI, self::MODEL)
            ->withInput(Audio::fromLocalPath($audioPath))
            ->withProviderOptions([
                'language'                => Language::SPANISH,
                'timestamp_granularities' => ['segment', 'word'],
                'response_format'         => 'verbose_json',
            ])
            ->withClientOptions(['timeout' => 600])
            ->asText();
    }
}
