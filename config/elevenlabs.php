<?php

return [
    'model_id'                => env('ELEVENLABS_MODEL_ID', 'eleven_multilingual_sts_v2'),
    'output_format'           => env('ELEVENLABS_OUTPUT_FORMAT', 'mp3_44100_128'),
    'remove_background_noise' => (bool) env('ELEVENLABS_REMOVE_BG_NOISE', false),
    'timeout'                 => (int) env('ELEVENLABS_TIMEOUT', 60),
    'stability'               => (float) env('ELEVENLABS_STABILITY', 0.5),
    'similarity_boost'        => (float) env('ELEVENLABS_SIMILARITY_BOOST', 0.75),
];
