<?php

declare(strict_types = 1);

namespace Canalizador\Recommendation\Domain\Entities;

use Canalizador\Recommendation\Domain\ValueObjects\Message;
use Canalizador\Recommendation\Domain\ValueObjects\RecommendationId;
use Canalizador\Recommendation\Domain\ValueObjects\RecommendationType;
use Canalizador\Recommendation\Domain\ValueObjects\Score;
use Canalizador\Video\Domain\ValueObjects\VideoId;

final readonly class Recommendation
{
    public function __construct(
        private RecommendationId   $id,
        private VideoId            $videoId,
        private Message            $message,
        private RecommendationType $type,
        private ?Score             $score = null
    ) {
    }

    public function id(): RecommendationId
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

    public function type(): RecommendationType
    {
        return $this->type;
    }

    public function score(): ?Score
    {
        return $this->score;
    }
}
