<?php

declare(strict_types = 1);

namespace Canalizador\YouTube\Transcription\Domain\Entities;

use Canalizador\Shared\Shared\Domain\ValueObjects\Essentials\Language;
use Canalizador\YouTube\Transcription\Domain\Collections\SentenceCollection;
use Canalizador\YouTube\Transcription\Domain\ValueObjects\Sentence;
use Canalizador\YouTube\Transcription\Domain\ValueObjects\Text;
use Canalizador\Youtube\Video\Domain\ValueObjects\Id;

final class Transcription
{
    public function __construct(
        private readonly Id                $videoId,
        private readonly Text              $text,
        private readonly Language          $language,
        private SentenceCollection         $sentences,
    ) {
    }

    public function videoId(): Id
    {
        return $this->videoId;
    }

    public function text(): Text
    {
        return $this->text;
    }

    public function language(): Language
    {
        return $this->language;
    }

    public function sentences(): SentenceCollection
    {
        return $this->sentences;
    }

    public function updateSentences(SentenceCollection $sentences): void
    {
        $this->sentences = $sentences;
    }

    public function toArray(): array
    {
        return [
            'videoId'   => $this->videoId->value(),
            'text'      => $this->text->value(),
            'language'  => $this->language->value,
            'sentences' => $this->sentences->map(fn (Sentence $sentence) => $sentence->toArray()),
        ];
    }
}
