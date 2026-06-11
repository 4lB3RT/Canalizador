<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Script\Application\UseCases\CreateScript;

use Helmreel\Shared\Shared\Domain\Services\Clock;
use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;
use Helmreel\VideoProduction\News\Application\UseCases\DownloadNews\DownloadNews;
use Helmreel\VideoProduction\News\Domain\Repositories\NewsRepository;
use Helmreel\VideoProduction\Script\Domain\Entities\Script;
use Helmreel\VideoProduction\Script\Domain\Repositories\ScriptGenerator;
use Helmreel\VideoProduction\Script\Domain\Repositories\ScriptRepository;
use Helmreel\VideoProduction\Script\Domain\ValueObjects\ScriptContent;
use Helmreel\VideoProduction\Script\Domain\ValueObjects\ScriptId;
use Helmreel\VideoProduction\Video\Domain\ValueObjects\VideoCategory;
use Helmreel\VideoProduction\Weather\Application\UseCases\GetForecasts\GetForecasts;
use Helmreel\VideoProduction\Weather\Domain\Repositories\ForecastRepository;
use RuntimeException;

final readonly class CreateScript
{
    public function __construct(
        private ScriptRepository $scriptRepository,
        private ScriptGenerator $scriptGenerator,
        private NewsRepository $newsRepository,
        private ForecastRepository $forecastRepository,
        private DownloadNews $downloadNews,
        private GetForecasts $getForecasts,
        private Clock $clock,
    ) {
    }

    public function execute(CreateScriptRequest $request): array
    {
        $category = VideoCategory::from($request->category);
        $totalClips = $request->totalClips;
        $clipDuration = (int) config('veo.duration', 8);

        $content = match ($category) {
            VideoCategory::GAMING => $this->scriptGenerator->generateGaming(
                $this->buildPromptFromLatestNews(),
                $totalClips,
                $clipDuration,
            ),
            VideoCategory::METEOROLOGY => $this->scriptGenerator->generateWeather(
                $this->buildPromptFromForecasts(),
                $totalClips,
                $clipDuration,
            ),
        };

        $script = new Script(
            id: ScriptId::fromString($request->scriptId),
            content: new ScriptContent($content),
            userId: new IntegerId($request->userId),
            category: $category,
            title: $request->title ?? $this->extractTitle($content),
            createdAt: $this->clock->now(),
        );

        $this->scriptRepository->save($script);

        return $script->toArray();
    }

    private function extractTitle(string $content): string
    {
        $data = json_decode($content, true);
        $script = is_array($data) ? ($data['full_script'] ?? '') : '';
        $words = trim((string) $script);

        if ($words === '') {
            return 'Guion sin título';
        }

        return mb_substr($words, 0, 60);
    }

    private function buildPromptFromLatestNews(): string
    {
        $news = $this->newsRepository->findLatest();

        if ($news === null) {
            $this->downloadNews->execute();
            $news = $this->newsRepository->findLatest();
        }

        if ($news === null) {
            throw new RuntimeException('No news available and the download returned no results.');
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
            $this->getForecasts->execute($today);
            $forecasts = $this->forecastRepository->findByDate($today);
        }

        if (empty($forecasts)) {
            throw new RuntimeException('No forecasts available and the download returned no results.');
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
