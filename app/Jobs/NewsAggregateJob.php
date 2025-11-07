<?php

namespace App\Jobs;

use DateTimeInterface;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class NewsAggregateJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public int $maxExceptions = 3;

    public array $backoff = [60, 120, 300, 600, 900];

    public int $timeout = 900;

    public function __construct(
        public string  $sourceKey,
        public ?string $sinceIso = null,
        public int     $chunkSize = 500
    )
    {
    }

    public function middleware(): array
    {
        return [
            (new WithoutOverlapping("aggregate:{$this->sourceKey}"))->expireAfter(3600),
            new RateLimited("rate:{$this->sourceKey}"),
        ];
    }

    public function retryUntil(): DateTimeInterface
    {
        return now()->addHours(6);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        /** @var object{pull: callable} $concreteClient */
        $concreteClient = App::make("news.client.{$this->sourceKey}");
        $since = $this->sinceIso ? Carbon::parse($this->sinceIso) : null;

        $buffer = [];

        foreach ($concreteClient->pull($since) as $item) {
            if (empty($item['url'])) {
                continue;
            }

            $buffer[] = [
                'source' => $this->sourceKey,
                'external_id' => $item['external_id'] ?? null,
                'url' => $item['url'],
                'title' => $item['title'] ?? '',
                'summary' => $item['summary'] ?? null,
                'authors' => isset($item['authors']) ? json_encode($item['authors']) : null,
                'category' => $item['category'] ?? null,
                'published_at' => isset($item['published_at']) ? Carbon::parse($item['published_at']) : null,
                'raw' => json_encode($item['raw'] ?? []),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (count($buffer) >= $this->chunkSize) {
                $this->flushBuffer($buffer);
            }
        }

        $this->flushBuffer($buffer);
    }

    private function flushBuffer(array &$buffer): void
    {
        if (count($buffer) === 0) {
            return;
        }

        DB::transaction(function () use (&$buffer): void {
            DB::table('articles')->upsert(
                $buffer,
                ['url'],
                ['title', 'summary', 'authors', 'category', 'published_at', 'raw', 'source', 'external_id', 'updated_at']
            );
        }, 3);

        $buffer = [];

        $this->clearArticleCache();
    }

    protected function clearArticleCache(): void
    {
        Cache::flush();
        logger()->info("Article cache cleared after ingest: {$this->sourceKey}");
    }
}
