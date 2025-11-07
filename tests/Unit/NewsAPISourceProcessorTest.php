<?php

use App\Services\NewsAPISourceProcessor;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

it('yields newsapi articles until all fetched', function () {
    Config::set('aggregator.news_api.url', 'https://newsapi.org/v2/everything');

    Http::fake([
        'newsapi.org/*' => Http::sequence()
            ->push([
                'status' => 'ok',
                'total' => 3,  // your implementation reads "total"
                'articles' => [
                    [
                        'url' => 'https://example.com/n1',
                        'title' => 'N1',
                        'description' => 'd1',
                        'author' => 'Bob',
                        'source' => ['name' => 'Tech'],
                        'publishedAt' => '2025-11-04T10:00:00Z',
                    ],
                    [
                        'url' => 'https://example.com/n2',
                        'title' => 'N2',
                        'description' => 'd2',
                        'author' => null,
                        'source' => ['name' => 'World'],
                        'publishedAt' => '2025-11-04T11:00:00Z',
                    ],
                ],
            ])
            ->push([
                'status' => 'ok',
                'total' => 3,
                'articles' => [
                    [
                        'url' => 'https://example.com/n3',
                        'title' => 'N3',
                        'description' => 'd3',
                        'author' => 'Carol',
                        'source' => ['name' => 'Tech'],
                        'publishedAt' => '2025-11-04T12:00:00Z',
                    ],
                ],
            ]),
    ]);

    $svc = new NewsAPISourceProcessor(apiKey: 'key');
    $since = Carbon::parse('2025-11-01');
    $out = iterator_to_array($svc->pull($since));

    expect($out)->toHaveCount(3)
        ->and($out[0]['authors'])->toBe(['Bob'])
        ->and($out[1]['authors'])->toBeNull()
        ->and($out[2]['category'])->toBe('Tech');
});
