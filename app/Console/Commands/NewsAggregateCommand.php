<?php

namespace App\Console\Commands;

use App\Enums\ClientSource;
use App\Jobs\NewsAggregateJob;
use Illuminate\Bus\Batch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Throwable;

class NewsAggregateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'news:pull
        {--since=}
        {--sources=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch queued aggregation for live news sources';

    /**
     * Execute the console command.
     * @throws Throwable
     */
    public function handle(): int
    {
        $clients = [
            ClientSource::GUARDIAN->value,
            ClientSource::NEW_YORK_TIME->value,
            ClientSource::NEWS_API->value,
        ];
        $selected = $this->option('sources') ?: $clients;
        $sinceOpt = $this->option('since');

        $jobs = [];
        foreach ($selected as $key) {
            $cursorKey = "aggregate:{$key}";
            $since = $sinceOpt ?: Cache::get($cursorKey);
            $jobs [] = new NewsAggregateJob($key, $since);
        }

        Bus::batch($jobs)->name('news-aggregator')
            ->then(function (Batch $batch) use ($clients) {
                foreach ($clients as $client) {
                    Cache::put("aggregator:{$client}", now()->toIso8601String(), 86400 * 30);
                }
            })->dispatch();

        $this->info("Dispatched aggregation batch:" . implode(', ', $clients));

        return self::SUCCESS;


    }
}
