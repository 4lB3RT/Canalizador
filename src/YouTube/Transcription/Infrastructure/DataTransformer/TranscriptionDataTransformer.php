<?php

declare(strict_types = 1);

namespace Canalizador\YouTube\Transcription\Infrastructure\DataTransformer;

use Canalizador\Shared\Shared\Domain\ValueObjects\Essentials\Language;
use Canalizador\YouTube\Transcription\Domain\Collections\WordCollection;
use Canalizador\YouTube\Transcription\Domain\Entities\Transcription;
use Canalizador\YouTube\Transcription\Domain\ValueObjects\EndTime;
use Canalizador\YouTube\Transcription\Domain\ValueObjects\StartTime;
use Canalizador\YouTube\Transcription\Domain\ValueObjects\Text;
use Canalizador\YouTube\Transcription\Domain\ValueObjects\Word;
use Canalizador\Youtube\Video\Domain\ValueObjects\Id;

final class TranscriptionDataTransformer
{
    public static function transformArray(array $data): Transcription
    {
        if ($data['words'] !== null) {
            $words = array_map(
                    fn (array $wordData) => new Word(
                        Text::fromString($wordData['text']),
                        StartTime::fromFloat($wordData['start']),
                        EndTime::fromFloat($wordData['end']),
                    ),
                    $data['words']
                );
        }

        $words = new WordCollection($words ?? []);

        return new Transcription(
            Id::fromString($data['videoId']),
            Text::fromString($data['text']),
            Language::tryFrom($data['language']),
            $words
        );
    }
}
