<?php

use App\Services\NYTSourceProcessor;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

it('yields NYT docs across pages based on hits', function () {
    Config::set('aggregator.new_york_time.url', 'https://api.nytimes.com/svc/search/v2/articlesearch.json');

    Http::fake([
        'api.nytimes.com/*' => Http::sequence()
            ->push([
                'response' => [
                    'meta' => ['hits' => 15], // or 'metadata' => ['hits' => 15]
                    'docs' => array_map(fn ($i) => [
                        '_id' => "nyt-$i",
                        'web_url' => "https://example.com/nyt-$i",
                        'headline' => ['main' => "NYT $i"],
                        'abstract' => "a$i",
                        'byline' => ['person' => [['firstname' => 'A', 'lastname' => 'B']]],
                        'section_name' => 'World',
                        'pub_date' => '2025-11-03T10:00:00Z',
                    ], range(1, 10)),
                ],
            ])
            ->push([
                'response' => [
                    'meta' => ['hits' => 15],
                    'docs' => array_map(fn ($i) => [
                        '_id' => "nyt-$i",
                        'web_url' => "https://example.com/nyt-$i",
                        'headline' => ['main' => "NYT $i"],
                        'abstract' => "a$i",
                        'byline' => ['person' => [['firstname' => 'C', 'lastname' => 'D']]],
                        'section_name' => 'Tech',
                        'pub_date' => '2025-11-03T11:00:00Z',
                    ], range(11, 15)),
                ],
            ]),
    ]);

    $svc = new NYTSourceProcessor(apiKey: 'key');
    $since = Carbon::parse('2025-11-01');
    $out = iterator_to_array($svc->pull($since));

    expect($out)->toHaveCount(15)
        ->and($out[0]['url'])->toBe('https://example.com/nyt-1')
        ->and($out[14]['category'])->toBe('Tech');
});
