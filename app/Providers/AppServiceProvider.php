<?php

namespace App\Providers;

use App\Services\BestSellerInterface;
use App\Services\NytBestSellerService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
    {
        $this->app->bind(BestSellerInterface::class, NytBestSellerService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
