<?php

namespace App\Providers;

use App\Services\BestSellerInterface;
use App\Services\CachedBestSellerDecorator;
use App\Services\FakeHttpService;
use App\Services\LimitedBestSellerDecorator;
use App\Services\NytBestSellerService;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->registerBestSellerService();

        Http::macro('nyt', function () {
            return Http::baseUrl(config('services.nyt.base_url'))
                ->timeout(config('services.nyt.timeout'))
                ->connectTimeout(config('services.nyt.timeout'))
                ->withQueryParameters(['api-key' => config('services.nyt.api_key')]);
        });

        // todo temporary during development
        if ($fake = false) {
            FakeHttpService::fakeNytBestSellerHistory();
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('testing')) {
            Http::preventStrayRequests();
        }
    }

    private function registerBestSellerService(): void
    {
        $this->app->bind(BestSellerInterface::class, NytBestSellerService::class);
        // add limits to prevent hitting NYT api limits
        $this->app->extend(BestSellerInterface::class, function (BestSellerInterface $service, Application $app) {
            return new LimitedBestSellerDecorator($service);
        });
        // cache decorator after limits, so that we don't limit on cache hits
        $this->app->extend(BestSellerInterface::class, function (BestSellerInterface $service, Application $app) {
            return new CachedBestSellerDecorator($service);
        });
    }
}
