<?php

declare(strict_types=1);

namespace Canalizador\Video\Infrastructure\Factories;

use Canalizador\Video\Domain\Exceptions\VideoGenerationFailed;
use Canalizador\Video\Domain\Factories\VideoPublisherFactory as VideoPublisherFactoryInterface;
use Canalizador\Video\Domain\Repositories\VideoPublisher;
use Canalizador\Video\Infrastructure\Repositories\YouTube\YoutubeVideoPublisher;

final class VideoPublisherFactory implements VideoPublisherFactoryInterface
{
    private const string PLATFORM_YOUTUBE = 'youtube';

    public function __construct(
        private readonly YoutubeVideoPublisher $youtubeVideoPublisher
    ) {
    }

    public function create(string $platform): VideoPublisher
    {
        return match ($platform) {
            self::PLATFORM_YOUTUBE => $this->youtubeVideoPublisher,
            default => throw VideoGenerationFailed::apiError("Unsupported platform: {$platform}"),
        };
    }
}
