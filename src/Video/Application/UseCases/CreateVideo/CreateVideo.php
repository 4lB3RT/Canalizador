<?php

declare(strict_types=1);

namespace Canalizador\Video\Application\UseCases\CreateVideo;

use Canalizador\Avatar\Domain\ValueObjects\AvatarId;
use Canalizador\Channel\Domain\ValueObjects\ChannelId;
use Canalizador\News\Domain\Repositories\NewsRepository;
use Canalizador\Script\Domain\Repositories\ScriptRepository;
use Canalizador\Script\Domain\Services\GenerateScript;
use Canalizador\Script\Domain\ValueObjects\ScriptId;
use Canalizador\Shared\Domain\Events\EventBus;
use Canalizador\Shared\Domain\Services\Clock;
use Canalizador\Video\Domain\Events\VideoCreated;
use Canalizador\Video\Domain\Exceptions\VideoNotFound;
use Canalizador\Video\Domain\Factories\VideoFactory;
use Canalizador\Video\Domain\Repositories\VideoMetadataGenerator;
use Canalizador\Video\Domain\Repositories\VideoRepository;
use Canalizador\Video\Domain\ValueObjects\VideoCategory;
use Canalizador\Video\Domain\ValueObjects\VideoId;
use Canalizador\Weather\Domain\Repositories\ForecastRepository;

final readonly class CreateVideo
{
    public function __construct(
        private ScriptRepository $scriptRepository,
        private GenerateScript $generateScript,
        private VideoFactory $videoFactory,
        private VideoRepository $videoRepository,
        private VideoMetadataGenerator $videoMetadataGenerator,
        private EventBus $eventBus,
        private Clock $clock,
        private NewsRepository $newsRepository,
        private ForecastRepository $forecastRepository,
    ) {
    }

    public function execute(CreateVideoRequest $request): CreateVideoResponse
    {
        $videoId = VideoId::fromString($request->videoId);

        try {
            $video = $this->videoRepository->findById($videoId);

        } catch (VideoNotFound) {
            $scriptId = ScriptId::fromString($request->scriptId);
            $channelId = ChannelId::fromString($request->channelId);
            $category = VideoCategory::from($request->category);

            $script = $this->scriptRepository->findById($scriptId);

            if ($script === null) {
                $script = match ($category) {
                    VideoCategory::GAMING => $this->generateScript->generate(
                        scriptId: $request->scriptId,
                        channelId: $request->channelId,
                        prompt: $this->buildPromptFromLatestNews(),
                        totalClips: (int) config('veo.total_clips', 5),
                        clipDuration: (int) config('veo.duration', 8),
                    ),
                    VideoCategory::METEOROLOGY => $this->generateScript->generateWeather(
                        scriptId: $request->scriptId,
                        channelId: $request->channelId,
                        prompt: $this->buildPromptFromForecasts(),
                        totalClips: (int) config('veo.total_clips', 5),
                        clipDuration: (int) config('veo.duration', 8),
                    ),
                };
            }

            $metadata = $this->videoMetadataGenerator->generate($script->content()->value());

            $video = $this->videoFactory->create(
                id: $videoId,
                script: $script,
                channelId: $channelId,
                title: $metadata->title,
                description: $metadata->description,
                category: $category,
                avatarId: $request->avatarId ? AvatarId::fromString($request->avatarId) : null,
            );

            $this->videoRepository->save($video);
        }

        $this->eventBus->publish(
            new VideoCreated($video->id()->value(), $this->clock->now())
        );

        return new CreateVideoResponse($video);
    }

    private function buildPromptFromLatestNews(): string
    {
        $news = $this->newsRepository->findLatest();

        if ($news === null) {
            throw new \RuntimeException('No news available. Run POST /api/news/download first.');
        }

        return sprintf(
            "Noticia: %s\n\nDescripcion: %s",
            $news->title()->value(),
            $news->description()->value()
        );
    }

    private function buildPromptFromForecasts(): string
    {
        $today = $this->clock->now()->value()->format('Y-m-d');
        $forecasts = $this->forecastRepository->findByDate($today);

        if (empty($forecasts)) {
            throw new \RuntimeException('No forecasts available for today. Run POST /api/weather/forecasts first.');
        }

        $lines = ["=== PREVISIÓN METEOROLÓGICA PARA HOY ($today) ===\n"];

        foreach ($forecasts as $forecast) {
            $lines[] = sprintf(
                "%s: %s\n",
                $forecast->cityName()->value(),
                $forecast->summary() ?? 'Sin resumen disponible',
            );
        }

        return implode("\n", $lines);
    }
}
