<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Video\Application\UseCases\CreateVideo;

use Helmreel\Shared\Shared\Domain\Events\EventBus;
use Helmreel\Shared\Shared\Domain\Services\Clock;
use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;
use Helmreel\Shared\Shared\Domain\ValueObjects\Language;
use Helmreel\Shared\Video\Domain\Repositories\VideoMetadataGenerator;
use Helmreel\VideoProduction\Avatar\Domain\Repositories\AvatarRepository;
use Helmreel\VideoProduction\Avatar\Domain\ValueObjects\AvatarId;
use Helmreel\VideoProduction\News\Domain\Repositories\NewsRepository;
use Helmreel\VideoProduction\Script\Domain\Repositories\ScriptRepository;
use Helmreel\VideoProduction\Script\Domain\Services\GenerateScript;
use Helmreel\VideoProduction\Script\Domain\ValueObjects\ScriptId;
use Helmreel\VideoProduction\Video\Domain\Events\VideoCreated;
use Helmreel\VideoProduction\Video\Domain\Exceptions\VideoNotFound;
use Helmreel\VideoProduction\Video\Domain\Factories\VideoFactory;
use Helmreel\VideoProduction\Video\Domain\Repositories\VideoRepository;
use Helmreel\VideoProduction\Video\Domain\Services\AvatarContextFrameGenerator;
use Helmreel\VideoProduction\Video\Domain\ValueObjects\AspectRatio;
use Helmreel\VideoProduction\Video\Domain\ValueObjects\Resolution;
use Helmreel\VideoProduction\Video\Domain\ValueObjects\TotalClips;
use Helmreel\VideoProduction\Video\Domain\ValueObjects\VideoModel;
use Helmreel\VideoProduction\Video\Domain\ValueObjects\VideoCategory;
use Helmreel\VideoProduction\Video\Domain\ValueObjects\VideoId;
use Helmreel\VideoProduction\Weather\Domain\Repositories\ForecastRepository;

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
        private AvatarRepository $avatarRepository,
        private AvatarContextFrameGenerator $avatarContextFrameGenerator,
    ) {
    }

    public function execute(CreateVideoRequest $request): void
    {
        $videoId = VideoId::fromString($request->videoId);

        try {
            $video = $this->videoRepository->findById($videoId);
        } catch (VideoNotFound) {
            $scriptId = ScriptId::fromString($request->scriptId);
            $category = VideoCategory::from($request->category);
            $language = Language::from($request->language);

            $script = $this->scriptRepository->findById($scriptId);

            if ($script === null) {
                $script = match ($category) {
                    VideoCategory::GAMING => $this->generateScript->generate(
                        scriptId: $request->scriptId,
                        prompt: $this->buildPromptFromLatestNews(),
                        language: $language,
                        totalClips: $request->totalClips,
                        clipDuration: (int) config('veo.duration', 8),
                    ),
                    VideoCategory::METEOROLOGY => $this->generateScript->generateWeather(
                        scriptId: $request->scriptId,
                        prompt: $this->buildPromptFromForecasts(),
                        language: $language,
                        totalClips: $request->totalClips,
                        clipDuration: (int) config('veo.duration', 8),
                    ),
                };
            }

            $metadata = $this->videoMetadataGenerator->generate($script->content()->value(), $language);

            $video = $this->videoFactory->create(
                id: $videoId,
                userId: new IntegerId($request->userId),
                script: $script,
                title: $metadata->title,
                description: $metadata->description,
                category: $category,
                resolution: Resolution::fromString($request->resolution),
                totalClips: new TotalClips($request->totalClips),
                language: $language,
                model: VideoModel::from($request->model),
                aspectRatio: AspectRatio::fromString($request->aspectRatio),
                avatarId: $request->avatarId ? AvatarId::fromString($request->avatarId) : null,
            );

            $this->videoRepository->save($video);

            if ($video->avatarId() !== null) {
                $avatar = $this->avatarRepository->findById($video->avatarId());
                $this->avatarContextFrameGenerator->frameFor($avatar, $video->category(), $video->aspectRatio());
            }
        }

        $this->eventBus->publish(
            new VideoCreated($video->id()->value(), $this->clock->now())
        );
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
