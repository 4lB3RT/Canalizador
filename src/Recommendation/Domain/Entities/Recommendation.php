<?php

declare(strict_types = 1);

namespace Canalizador\Recommendation\Domain\Entities;

use Canalizador\Recommendation\Domain\ValueObjects\Message;
use Canalizador\Recommendation\Domain\ValueObjects\RecommendationId;
use Canalizador\Recommendation\Domain\ValueObjects\Type;
use Canalizador\Recommendation\Domain\ValueObjects\Value;
use Canalizador\Recommendation\Domain\ValueObjects\Score;
use Canalizador\Recommendation\Domain\ValueObjects\ValueCollection;
use Canalizador\Video\Domain\ValueObjects\VideoId;

final readonly class Recommendation
{
    public function __construct(
        private VideoId           $videoId,
        private Message           $message,
        private ValueCollection   $values,
        private Type              $type,
        private Score             $score,
        private ?RecommendationId $id = null,
    ) {
    }

    public function id(): ?RecommendationId
    {
        return $this->id;
    }

    public function videoId(): VideoId
    {
        return $this->videoId;
    }

    public function message(): Message
    {
        return $this->message;
    }

    public function type(): Type
    {
        return $this->type;
    }

    public function score(): Score
    {
        return $this->score;
    }

    public function values(): ValueCollection
    {
        return $this->values;
    }
}
