<?php

use App\Services\GuardianSourceProcessor;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

it('yields guardian articles across pages', function () {
    Config::set('aggregator.guardian.url', 'https://content.guardianapis.com/search');

    Http::fake([
        'content.guardianapis.com/*' => Http::sequence()
            ->push([
                'response' => [
                    'currentPage' => 1,
                    'pages' => 2,
                    'results' => [
                        [
                            'id' => 'g-1',
                            'webUrl' => 'https://example.com/g1',
                            'webTitle' => 'G1',
                            'fields' => ['trailText' => 't1'],
                            'sectionName' => 'Technology',
                            'webPublicationDate' => '2025-11-05T10:00:00Z',
                            'tags' => [
                                ['type' => 'contributor', 'webTitle' => 'Alice'],
                            ],
                        ],
                        [
                            'id' => 'g-2',
                            'webUrl' => 'https://example.com/g2',
                            'webTitle' => 'G2',
                            'fields' => ['trailText' => 't2'],
                            'sectionName' => 'World',
                            'webPublicationDate' => '2025-11-05T11:00:00Z',
                        ],
                    ],
                ],
            ])
            // Page 2 (1 result), last page
            ->push([
                'response' => [
                    'currentPage' => 2,
                    'pages' => 2,
                    'results' => [
                        [
                            'id' => 'g-3',
                            'webUrl' => 'https://example.com/g3',
                            'webTitle' => 'G3',
                            'fields' => ['trailText' => 't3'],
                            'sectionName' => 'Sports',
                            'webPublicationDate' => '2025-11-05T12:00:00Z',
                        ],
                    ],
                ],
            ]),
    ]);

    $svc = new GuardianSourceProcessor(apiKey: 'key');
    $since = Carbon::parse('2025-11-01');
    $out = iterator_to_array($svc->pull($since));

    expect($out)->toHaveCount(3)
        ->and($out[0]['url'])->toBe('https://example.com/g1')
        ->and($out[1]['title'])->toBe('G2')
        ->and($out[2]['category'])->toBe('Sports');
});
