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
    | Supported resolutions by Sora API:
    | - '1280x720' (720p horizontal/landscape) - RECOMMENDED for testing
    | - '720x1280' (720p vertical/portrait)
    | - '1792x1024' (higher quality horizontal)
    | - '1024x1792' (higher quality vertical)
    |
    | Note: Lower resolution (1280x720) = lower cost for testing
    | Default: 1280x720 (minimum cost option available)
    |
    */

    'resolution' => env('SORA_RESOLUTION', '1280x720'),

    /*
    |--------------------------------------------------------------------------
    | Available Resolutions
    |--------------------------------------------------------------------------
    |
    | List of supported resolutions by Sora API for validation.
    | These are the ONLY resolutions accepted by the API.
    |
    */

    'available_resolutions' => [
        '1280x720',  // 720p horizontal - Minimum cost option
        '720x1280',  // 720p vertical/portrait
        '1792x1024', // Higher quality horizontal
        '1024x1792', // Higher quality vertical
    ],
];
