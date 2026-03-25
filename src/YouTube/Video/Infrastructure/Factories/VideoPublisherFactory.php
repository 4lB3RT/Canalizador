<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Infrastructure\Factories;

use Canalizador\YouTube\Video\Domain\Exceptions\YouTubeOperationFailed;
use Canalizador\YouTube\Video\Domain\Factories\VideoPublisherFactory as VideoPublisherFactoryInterface;
use Canalizador\YouTube\Video\Domain\Repositories\VideoPublisher;
use Canalizador\YouTube\Video\Infrastructure\Repositories\YouTube\YoutubeVideoPublisher;

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
            default                => throw YouTubeOperationFailed::apiError("Unsupported platform: {$platform}"),
        };
    }
}
