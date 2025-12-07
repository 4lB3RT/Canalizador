<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Sora Video Generation Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for OpenAI Sora-2 video generation API.
    | These settings affect video quality and cost.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Model Selection
    |--------------------------------------------------------------------------
    |
    | Available models:
    | - 'sora-2': Standard model, lower cost (RECOMMENDED for testing)
    | - 'sora-2-pro': Pro model, higher quality but higher cost
    |
    | For testing with minimum cost, use 'sora-2'
    |
    */

    'model' => env('SORA_MODEL', 'sora-2'),

    /*
    |--------------------------------------------------------------------------
    | Video Duration
    |--------------------------------------------------------------------------
    |
    | Duration of the generated video in seconds.
    | Lower duration = lower cost (cost is proportional to duration).
    | 
    | Recommended values:
    | - 5 seconds: Maximum savings for initial testing (44% cheaper than 9s)
    | - 7 seconds: Balanced testing (22% cheaper than 9s)
    | - 9 seconds: Production (aligned with script generation) - DEFAULT
    |
    | Cost formula: Cost = Price per second × Duration
    | Example: $0.08/second × 5s = $0.40 per video
    |
    */

    'duration' => (int) env('SORA_DURATION', 9),

    /*
    |--------------------------------------------------------------------------
    | Video Resolution
    |--------------------------------------------------------------------------
    |
    | Resolution of the generated video.
    | Options: '1280x720' (720p), '854x480' (480p), '640x360' (360p)
    | Lower resolution = lower cost.
    | Default: 360p (MINIMUM COST for testing)
    | For production: Use 480p or 720p
    |
    */

    'resolution' => env('SORA_RESOLUTION', '640x360'),

    /*
    |--------------------------------------------------------------------------
    | Available Resolutions
    |--------------------------------------------------------------------------
    |
    | List of supported resolutions for validation.
    |
    */

    'available_resolutions' => [
        '1280x720', // 720p - Higher quality, higher cost
        '854x480',  // 480p - Balanced quality/cost (recommended)
        '640x360',  // 360p - Lower quality, lower cost
    ],
];
