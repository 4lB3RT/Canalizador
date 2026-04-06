<?php

declare(strict_types = 1);

namespace Canalizador\YouTube\Transcription\Infrastructure\DataTransformer;

use Canalizador\Shared\Shared\Domain\ValueObjects\Essentials\Language;
use Canalizador\YouTube\Transcription\Domain\Collections\SentenceCollection;
use Canalizador\YouTube\Transcription\Domain\Entities\Transcription;
use Canalizador\YouTube\Transcription\Domain\ValueObjects\EndTime;
use Canalizador\YouTube\Transcription\Domain\ValueObjects\Sentence;
use Canalizador\YouTube\Transcription\Domain\ValueObjects\StartTime;
use Canalizador\YouTube\Transcription\Domain\ValueObjects\Text;
use Canalizador\Youtube\Video\Domain\ValueObjects\Id;

final class TranscriptionDataTransformer
{
    public static function transformArray(array $data): Transcription
    {
        if (!empty($data['sentences'])) {
            $sentences = array_map(
                fn (array $sentenceData) => new Sentence(
                    Text::fromString($sentenceData['text']),
                    StartTime::fromFloat($sentenceData['start']),
                    EndTime::fromFloat($sentenceData['end']),
                ),
                $data['sentences']
            );
        }

        $sentences = new SentenceCollection($sentences ?? []);

        return new Transcription(
            Id::fromString($data['videoId']),
            Text::fromString($data['text']),
            Language::tryFrom($data['language']),
            $sentences
        );
    }
}
