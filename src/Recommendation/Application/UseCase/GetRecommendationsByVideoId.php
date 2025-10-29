<?php

declare(strict_types=1);

namespace Canalizador\Recommendation\Application\UseCase;

use App\Services\YoutubeInsightsAnalysisAgent;
use Canalizador\Video\Domain\ValueObjects\VideoId;
use Canalizador\Video\Infrastructure\Repositories\Youtube\YoutubeVideoRepository;

class GetRecommendationsByVideoId
{
    public function __construct(
        private YoutubeVideoRepository $videoRepository,
        private YoutubeInsightsAnalysisAgent $insightsAgent
    ) {}

    public function execute(VideoId $videoId, ?string $instruccion = null): ?string
    {
        $video = $this->videoRepository->findById($videoId);
        $metrics = $this->videoRepository->getMetricsById($videoId);
        if (!$metrics || !$video) {
            return null;
        }
        $insights = [
            'title' => $video->title()->value(),
            'publishedAt' => $video->publishedAt()->value(),
            'metrics' => collect($metrics->items())->map(function($metric) {
                return [
                    'name' => $metric->name()->value(),
                    'type' => $metric->type()->value(),
                    'value' => $metric->value()->value(),
                ];
            })->toArray(),
        ];

        $recommendations = $this->insightsAgent->recommendations($videoId, $insights, $instruccion);
    }
}
