<?php

declare(strict_types = 1);

namespace Helmreel\YouTube\Transcription\Infrastructure\DataTransformer;

use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\Language;
use Helmreel\YouTube\Transcription\Domain\Collections\SentenceCollection;
use Helmreel\YouTube\Transcription\Domain\Entities\Transcription;
use Helmreel\YouTube\Transcription\Domain\ValueObjects\EndTime;
use Helmreel\YouTube\Transcription\Domain\ValueObjects\Sentence;
use Helmreel\YouTube\Transcription\Domain\ValueObjects\StartTime;
use Helmreel\YouTube\Transcription\Domain\ValueObjects\Text;
use Helmreel\Youtube\Video\Domain\ValueObjects\Id;

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
