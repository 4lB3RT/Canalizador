<?php

use Canalizador\Video\Infrastructure\Http\Api\Controllers\GetYoutubeVideoController;
use Illuminate\Support\Facades\Route;
use Canalizador\Shared\Infrastructure\ClientAPI\YoutubeAnalyticsApiClient;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/youtube-video/{id}', [GetYoutubeVideoController::class, '__invoke']);

Route::get('/test-yt-analytics', function (YoutubeAnalyticsApiClient $client) {
    $params = [
        'channelId' => 'YOUR_CHANNEL_ID', // Replace with your channel ID
        'videoId' => 'YOUR_VIDEO_ID',     // Replace with your video ID
        'startDate' => '2025-06-01',
        'endDate' => '2025-07-01',
        'metrics' => 'views,likes,comments',
    ];
    $result = $client->getVideoMetrics($params);
    return response()->json($result);
});
