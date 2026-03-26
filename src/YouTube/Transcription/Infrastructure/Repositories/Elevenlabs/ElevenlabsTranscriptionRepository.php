<?php

declare(strict_types = 1);

namespace Canalizador\Transcription\Infrastructure\Repositories\Elevenlabs;

use Canalizador\Shared\Domain\ValueObjects\Language;
use Canalizador\Transcription\Domain\Collections\WordCollection;
use Canalizador\Transcription\Domain\Entities\Transcription;
use Canalizador\Transcription\Domain\Repositories\TranscriptionRepository;
use Canalizador\Transcription\Domain\ValueObjects\EndTime;
use Canalizador\Transcription\Domain\ValueObjects\StartTime;
use Canalizador\Transcription\Domain\ValueObjects\Text;
use Canalizador\Transcription\Domain\ValueObjects\Word;
use Canalizador\Video\Domain\Entities\Video;
use Canalizador\Video\Domain\Exceptions\VideoLocalPathNotFound;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;
use Prism\Prism\ValueObjects\Media\Audio;

final readonly class ElevenlabsTranscriptionRepository implements TranscriptionRepository
{
    private const string MODEL = 'scribe_v1';

    /* @throws VideoLocalPathNotFound */
    public function findByVideo(Video $video): ?Transcription
    {
        if ($video->videoLocalPath() === null) {
            throw VideoLocalPathNotFound::default();
        }

        $transcriptionResponse = Prism::audio()
            ->using(Provider::ElevenLabs, self::MODEL)
            ->withInput(Audio::fromLocalPath($video->videoLocalPath()->value()))
            ->withProviderOptions([
                'language'     => Language::SPANISH,
                'diarize'      => true,
                'num_speakers' => 1,
            ])
            ->asText();


        $transcription = new Transcription(
            videoId: $video->id(),
            text: Text::fromString($transcriptionResponse->text),
            language: Language::SPANISH,
            words: WordCollection::empty(),
        );

        $words = WordCollection::empty();
        foreach ($transcriptionResponse->additionalContent['words'] as $word) {
            if ($word['type'] === 'word') {
                $words->add(
                    new Word(
                        text: Text::fromString($word['text']),
                        start: StartTime::fromFloat($word['start']),
                        end: EndTime::fromFloat($word['end']),
                    )
                );
            }
        }

        $transcription->updateWords($words);

        return $transcription;
    }
}
