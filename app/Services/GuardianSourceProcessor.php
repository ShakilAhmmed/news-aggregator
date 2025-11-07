<?php

namespace App\Services;

use App\Contracts\NewsSourceContract;
use App\Enums\ClientSource;
use Carbon\Carbon;
use Generator;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class GuardianSourceProcessor implements NewsSourceContract
{
    public function __construct(private string $apiKey) {}

    public function sourceKey(): string
    {
        return ClientSource::GUARDIAN->value;
    }

    public function sourceUrl(): string
    {
        return config("aggregator.{$this->sourceKey()}.url");
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function pull(?Carbon $since = null): Generator
    {
        if (! $this->apiKey) {
            return;
        }

        $page = 1;
        $pageSize = 100;
        $params = [
            'api-key' => $this->apiKey,
            'page-size' => $pageSize,
            'show-fields' => 'trailText',
            'show-tags' => 'contributor',
        ];
        if ($since) {
            $params['from-date'] = $since->toDateString();
        }
        while (true) {
            $resp = Http::retry(3, 500)
                ->get($this->sourceUrl(), $params + ['page' => $page]);

            $data = $resp->throw()->json();
            $results = data_get($data, 'response.results', []);

            foreach ($results as $r) {
                yield [
                    'external_id' => $r['id'] ?? null,
                    'url' => $r['webUrl'],
                    'title' => $r['webTitle'] ?? '',
                    'summary' => data_get($r, 'fields.trailText'),
                    'authors' => null,
                    'category' => $r['sectionName'] ?? null,
                    'published_at' => $r['webPublicationDate'] ?? null,
                    'raw' => $r,
                ];
            }

            $current = data_get($data, 'response.currentPage', 1);
            $pages = data_get($data, 'response.pages', 1);
            if ($current >= $pages) {
                break;
            }
            $page++;
        }
    }
}
