<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Google Veo Video Generation Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Google Veo 3 video generation API.
    | These settings affect video quality and generation behavior.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Model Selection
    |--------------------------------------------------------------------------
    |
    | Available models:
    | - 'veo-3.1-generate-preview': Preview model (current)
    |
    */

    'model' => env('VEO_MODEL', 'veo-3.1-generate-preview'),

    /*
    |--------------------------------------------------------------------------
    | Video Duration
    |--------------------------------------------------------------------------
    |
    | Duration of the generated video in seconds.
    | Supported values: 4, 6, 8 seconds
    |
    | Default: 8 seconds (maximum available)
    |
    */

    'duration' => (int) env('VEO_DURATION', 8),

    /*
    |--------------------------------------------------------------------------
    | Video Resolution
    |--------------------------------------------------------------------------
    |
    | Resolution of the generated video.
    | Supported resolutions:
    | - '720p'
    | - '1080p'
    | - '4K' (preview)
    |
    | Default: 1080p
    |
    */

    'resolution' => env('VEO_RESOLUTION', '720p'),

    /*
    |--------------------------------------------------------------------------
    | Video Aspect Ratio
    |--------------------------------------------------------------------------
    |
    | Aspect ratio of the generated video.
    | Supported ratios:
    | - '16:9' (landscape/horizontal)
    | - '9:16' (portrait/vertical)
    |
    | Default: 16:9 (landscape)
    |
    */

    'aspect_ratio' => env('VEO_ASPECT_RATIO', '16:9'),

    /*
    |--------------------------------------------------------------------------
    | Polling Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for polling the API to check video generation status.
    |
    */

    'polling' => [
        'interval' => (int) env('VEO_POLLING_INTERVAL', 5), // seconds between polls
        'max_attempts' => (int) env('VEO_POLLING_MAX_ATTEMPTS', 120), // maximum polling attempts
    ],
];
