<?php

namespace App\Services;

use App\Contracts\NewsSourceContract;
use App\Enums\ClientSource;
use Carbon\Carbon;
use Generator;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class NewsAPISourceProcessor implements NewsSourceContract
{
    public function __construct(private string $apiKey)
    {
    }

    public function sourceKey(): string
    {
        return ClientSource::NEWS_API->value;
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
        if (!$this->apiKey) {
            return;
        }
        $page = 1;
        $pageSize = 100;
        $totalFetched = 0;
        $totalResults = 0;

        while (true) {
            $params = [
                'apiKey' => $this->apiKey,
                'pageSize' => 100,
                'page' => $page,
                'sortBy' => 'publishedAt',
                'language' => 'en',
            ];
            if ($since) {
                $params['from'] = $since->toIso8601String();
            }

            $resp = Http::retry(3, 500)
                ->get($this->sourceUrl(), $params)
                ->throw()
                ->json();
            $articles = $resp['articles'] ?? [];
            $totalResults = (int)($resp['total'] ?? 0);
            if (empty($articles)) {
                break;
            }

            foreach ($articles as $article) {
                if (empty($article['url'])) {
                    continue;
                }
                yield [
                    'external_id' => null,
                    'url' => $article['url'] ?? '',
                    'title' => $article['title'] ?? '',
                    'summary' => $article['description'] ?? null,
                    'authors' => $article['author'] ? [$article['author']] : null,
                    'category' => data_get($article, 'source.name'),
                    'published_at' => $article['publishedAt'] ?? null,
                    'raw' => $article,
                ];
            }
            $count = count($articles);
            $totalFetched += $count;
            if ($count < $pageSize) {
                break;
            }
            if ($totalFetched >= $totalResults) {
                break;
            }
            $page++;
        }
    }
}
