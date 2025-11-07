<?php

use App\Enums\ClientSource;

return [
    ClientSource::GUARDIAN->value => [
        'key' => env('GUARDIAN_API_KEY', ''),
        'url' => env('GUARDIAN_API_URL', 'https://content.guardianapis.com/search'),
    ],
    ClientSource::NEW_YORK_TIME->value => [
        'key' => env('NEW_YORK_TIME_API_KEY', ''),
        'url' => env('NEW_YORK_TIME_API_URL', 'https://api.nytimes.com/svc/search/v2/articlesearch.json'),
    ],
    ClientSource::NEWS_API->value => [
        'key' => env('NEWS_API_KEY', ''),
        'url' => env('NEWS_API_URL', 'https://newsapi.org/v2/everything'),
    ],
];
