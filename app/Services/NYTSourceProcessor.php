<?php

namespace App\Services;

use App\Contracts\NewsSourceContract;
use App\Enums\ClientSource;
use Carbon\Carbon;
use Generator;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class NYTSourceProcessor implements NewsSourceContract
{
    public function __construct(private string $apiKey) {}

    public function sourceKey(): string
    {
        return ClientSource::NEW_YORK_TIME->value;
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

        $page = 0;
        while (true) {
            $params = [
                'api-key' => $this->apiKey,
                'page' => $page,
                'sort' => 'newest',
            ];
            if ($since) {
                $params['begin_date'] = $since->format('Ymd');
            }

            $resp = Http::retry(3, 500)
                ->get($this->sourceUrl(), $params)
                ->throw()
                ->json();
            $docs = data_get($resp, 'response.docs', []);
            $hits = (int) data_get($docs, 'response.metadata.hits', 0);

            if (empty($docs)) {
                break;
            }

            foreach ($docs as $d) {
                $url = $d['web_url'] ?? null;
                if (! $url) {
                    continue;
                }
                yield [
                    'external_id' => $d['_id'] ?? null,
                    'url' => $url,
                    'title' => data_get($d, 'headline.main', ''),
                    'summary' => $d['abstract'] ?? null,
                    'authors' => collect($d['byline']['person'] ?? [])->map(fn ($p) => trim(($p['firstname'] ?? '').' '.($p['lastname'] ?? '')))->filter()->values()->all(),
                    'category' => $d['section_name'] ?? null,
                    'published_at' => $d['pub_date'] ?? null,
                    'raw' => $d,
                ];
            }

            $page++;

            if ($page * 10 >= $hits) {
                break;
            }
        }
    }
}
