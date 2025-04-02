<?php

namespace App\Providers;

use App\Services\BestSellerInterface;
use App\Services\FakeHttpService;
use App\Services\NytBestSellerService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(BestSellerInterface::class, NytBestSellerService::class);

        Http::macro('nyt', function () {
            return Http::baseUrl(config('services.nyt.base_url'))
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
}
