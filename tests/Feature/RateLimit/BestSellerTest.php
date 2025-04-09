<?php

namespace Tests\Feature\RateLimit;

use App\Services\BestSellerInterface;
use Illuminate\Support\Facades\RateLimiter;
use Tests\Feature\BestSellerBaseTestCase;

class BestSellerTest extends BestSellerBaseTestCase
{
    /**
     * @dataProvider versionDataProvider
     */
    public function test_best_seller_api_limits_minute(string $version)
    {
        RateLimiter::clear(BestSellerInterface::LISTS_BEST_SELLERS_HISTORY_ENDPOINT.':minute');
        config(['services.nyt.limits.minute' => 3]);
        config(['services.nyt.cache.enabled' => false]);

        $this->getBestSellerApi('best-seller', $version)
            ->assertSuccessful();

        $this->getBestSellerApi('best-seller', $version)
            ->assertSuccessful();

        $this->getBestSellerApi('best-seller', $version)
            ->assertSuccessful();

        $this->getBestSellerApi('best-seller', $version)
            ->assertTooManyRequests();

        $this->getBestSellerApi('best-seller', $version)
            ->assertTooManyRequests();
    }

    /**
     * @dataProvider versionDataProvider
     */
    public function test_best_seller_api_limits_day(string $version)
    {
        RateLimiter::clear(BestSellerInterface::LISTS_BEST_SELLERS_HISTORY_ENDPOINT.':minute');
        RateLimiter::clear(BestSellerInterface::LISTS_BEST_SELLERS_HISTORY_ENDPOINT.':day');
        config(['services.nyt.limits.minute' => 3]);
        config(['services.nyt.limits.day' => 5]);
        config(['services.nyt.cache.enabled' => false]);

        $this->getBestSellerApi('best-seller', $version)
            ->assertSuccessful();

        $this->getBestSellerApi('best-seller', $version)
            ->assertSuccessful();

        $this->getBestSellerApi('best-seller', $version)
            ->assertSuccessful();

        RateLimiter::clear(BestSellerInterface::LISTS_BEST_SELLERS_HISTORY_ENDPOINT.':minute');

        $this->getBestSellerApi('best-seller', $version)
            ->assertSuccessful();

        $this->getBestSellerApi('best-seller', $version)
            ->assertSuccessful();

        $this->getBestSellerApi('best-seller', $version)
            ->assertTooManyRequests();
    }
}
