<?php

declare(strict_types = 1);

namespace Canalizador\Transcription\Infrastructure\DataTransformer;

use Canalizador\Shared\Domain\ValueObjects\Language;
use Canalizador\Transcription\Domain\Collections\WordCollection;
use Canalizador\Transcription\Domain\Entities\Transcription;
use Canalizador\Transcription\Domain\ValueObjects\EndTime;
use Canalizador\Transcription\Domain\ValueObjects\StartTime;
use Canalizador\Transcription\Domain\ValueObjects\Text;
use Canalizador\Transcription\Domain\ValueObjects\Word;
use Canalizador\Video\Domain\ValueObjects\VideoId;

final class TranscriptionDataTransformer
{
    public static function transformArray(array $data): Transcription
    {
        $words = WordCollection::empty();

        if ($data['words'] !== null) {
            $words->add(
                array_map(
                    fn (array $wordData) => new Word(
                        Text::fromString($wordData['text']),
                        StartTime::fromFloat($wordData['start']),
                        EndTime::fromFloat($wordData['end']),
                    ),
                    $data['words']
                )
            );
        }

        return new Transcription(
            VideoId::fromString($data['videoId']),
            Text::fromString($data['text']),
            Language::tryFrom($data['language']),
            $words
        );
    }
}
