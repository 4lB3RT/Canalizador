<?php

declare(strict_types = 1);

namespace Canalizador\Recommendation\Infrastructure\Controllers;

use Canalizador\Recommendation\Application\UseCase\GetRecommendationsByVideoId;
use Canalizador\Video\Domain\ValueObjects\VideoId;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class GetRecommendationsByVideoIdController extends Controller
{
    public function __construct(private GetRecommendationsByVideoId $getRecommendationsByVideoId)
    {
    }

    public function __invoke(Request $request, string $videoId)
    {
        $instruccion = $request->input('instruccion');

        $videoIdVO       = new VideoId($videoId);
        $recommendations = $this->getRecommendationsByVideoId->execute($videoIdVO, $instruccion);

        return response()->json([
            'videoId'         => $videoId,
            'recommendations' => $recommendations,
        ]);
    }
}
