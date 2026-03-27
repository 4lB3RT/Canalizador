<?php

declare(strict_types = 1);

namespace Canalizador\YouTube\Transcription\Domain\Entities;

use Canalizador\Shared\Shared\Domain\ValueObjects\Essentials\Language;
use Canalizador\YouTube\Transcription\Domain\Collections\WordCollection;
use Canalizador\YouTube\Transcription\Domain\ValueObjects\Text;
use Canalizador\YouTube\Transcription\Domain\ValueObjects\Word;
use Canalizador\Youtube\Video\Domain\ValueObjects\Id;

final class Transcription
{
    public function __construct(
        private readonly Id                     $videoId,
        private readonly Text                   $text,
        private readonly Language               $language,
        private WordCollection                  $words,
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

    public function words(): WordCollection
    {
        return $this->words;
    }

    public function updateWords(WordCollection $words): void
    {
        $this->words = $words;
    }

    public function toArray(): array
    {
        return [
            'videoId'  => $this->videoId->value(),
            'text'     => $this->text->value(),
            'language' => $this->language->value,
            'words'    => $this->words->map(fn (Word $word) => $word->toArray()),
        ];
    }
}
