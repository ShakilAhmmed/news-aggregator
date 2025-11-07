<?php

namespace App\Providers;

use App\Enums\ClientSource;
use App\Services\GuardianSourceProcessor;
use App\Services\NewsAPISourceProcessor;
use App\Services\NYTSourceProcessor;
use Illuminate\Support\ServiceProvider;

class NewsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind('news.client.guardian', function ($app) {
            $sourceKey = ClientSource::GUARDIAN->value;

            return new GuardianSourceProcessor(config("aggregator.{$sourceKey}.key"));
        });

        $this->app->bind('news.client.nyt', function ($app) {
            $sourceKey = ClientSource::NEW_YORK_TIME->value;

            return new NYTSourceProcessor(config("aggregator.{$sourceKey}.key"));
        });

        $this->app->bind('news.client.news_api', function ($app) {
            $sourceKey = ClientSource::NEWS_API->value;

            return new NewsAPISourceProcessor(config("aggregator.{$sourceKey}.key"));
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
