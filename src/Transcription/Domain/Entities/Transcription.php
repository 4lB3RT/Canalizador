<?php

declare(strict_types = 1);

namespace Canalizador\Transcription\Domain\Entities;

use Canalizador\Shared\Domain\ValueObjects\DateTime;
use Canalizador\Shared\Domain\ValueObjects\Language;
use Canalizador\Shared\Domain\ValueObjects\StringValue;
use Canalizador\Transcription\Domain\Collections\SegmentationCollection;
use Canalizador\Transcription\Domain\Collections\WordCollection;
use Canalizador\Transcription\Domain\ValueObjects\TranscriptionId;

final readonly class Transcription
{
    public function __construct(
        private TranscriptionId        $id,
        private StringValue            $text,
        private Language               $language,
        private SegmentationCollection $segmentations,
        private WordCollection         $words,
        private DateTime               $createdAt,
    ) {
    }

    public function id(): TranscriptionId
    {
        return $this->id;
    }

    public function text(): StringValue
    {
        return $this->text;
    }

    public function language(): Language
    {
        return $this->language;
    }

    public function segmentations(): SegmentationCollection
    {
        return $this->segmentations;
    }

    public function words(): WordCollection
    {
        return $this->words;
    }

    public function createdAt(): DateTime
    {
        return $this->createdAt;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id->value(),
            'text' => $this->text->value(),
            'language' => $this->language->value(),
            'segmentations' => array_map(
                fn($segmentation) => $segmentation->toArray(),
                $this->segmentations->toArray()
            ),
            'words' => array_map(
                fn($word) => $word->toArray(),
                $this->words->toArray()
            ),
            'createdAt' => $this->createdAt->value(),
        ];
    }

}
