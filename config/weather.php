<?php

return [
    'cities' => [
        'A Coruña' => '15030',
        'Alicante' => '03014',
        'Barcelona' => '08019',
        'Bilbao' => '48020',
        'Córdoba' => '14021',
        'Las Palmas' => '35016',
        'Madrid' => '28079',
        'Málaga' => '29067',
        'Murcia' => '30030',
        'Palma' => '07040',
        'Sevilla' => '41091',
        'Valencia' => '46250',
        'Valladolid' => '47186',
        'Vigo' => '36057',
        'Zaragoza' => '50297',
    ],

    'fetch_delay_ms' => (int) env('WEATHER_FETCH_DELAY_MS', 200),

    'map_image_path' => env('WEATHER_MAP_IMAGE_PATH', storage_path('app/maps/spain_communities.png')),

    'studio_image_path' => env('WEATHER_STUDIO_IMAGE_PATH'),
];
