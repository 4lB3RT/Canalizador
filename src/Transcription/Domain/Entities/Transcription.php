<?php

declare(strict_types = 1);

namespace Canalizador\Transcription\Domain\Entities;

use Canalizador\Shared\Domain\ValueObjects\Language;
use Canalizador\Transcription\Domain\Collections\WordCollection;
use Canalizador\Transcription\Domain\ValueObjects\Text;
use Canalizador\Transcription\Domain\ValueObjects\Word;
use Canalizador\Video\Domain\ValueObjects\VideoId;

final class Transcription
{
    public function __construct(
        private readonly VideoId                $videoId,
        private readonly Text                   $text,
        private readonly Language               $language,
        private WordCollection                  $words,
    ) {
    }

    public function videoId(): VideoId
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
            'words'    => array_map(
                fn (Word $word) => $word->toArray(),
                $this->words->items()
            ),
        ];
    }
}
