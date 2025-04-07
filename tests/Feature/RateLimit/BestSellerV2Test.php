<?php

namespace Tests\Feature\RateLimit;

use App\Services\BestSellerInterface;
use Illuminate\Support\Facades\RateLimiter;
use Tests\Feature\BestSellerBaseTestCase;

class BestSellerV2Test extends BestSellerBaseTestCase
{
    public function test_best_seller_api_limits_minute()
    {
        RateLimiter::clear(BestSellerInterface::LISTS_BEST_SELLERS_HISTORY_ENDPOINT.':minute');
        config(['services.nyt.limits.minute' => 3]);
        config(['services.nyt.cache.enabled' => false]);

        $this->get('/api/v2/best-seller?offset=20', ['Accept' => 'application/json'])
            ->assertSuccessful();

        $this->get('/api/v2/best-seller?offset=40', ['Accept' => 'application/json'])
            ->assertSuccessful();

        $this->get('/api/v2/best-seller?offset=20', ['Accept' => 'application/json'])
            ->assertSuccessful();

        $this->get('/api/v2/best-seller', ['Accept' => 'application/json'])
            ->assertTooManyRequests();

        $this->get('/api/v2/best-seller?offset=40', ['Accept' => 'application/json'])
            ->assertTooManyRequests();
    }

    public function test_best_seller_api_limits_day()
    {
        RateLimiter::clear(BestSellerInterface::LISTS_BEST_SELLERS_HISTORY_ENDPOINT.':minute');
        RateLimiter::clear(BestSellerInterface::LISTS_BEST_SELLERS_HISTORY_ENDPOINT.':day');
        config(['services.nyt.limits.minute' => 3]);
        config(['services.nyt.limits.day' => 5]);
        config(['services.nyt.cache.enabled' => false]);

        $this->get('/api/v2/best-seller', ['Accept' => 'application/json'])
            ->assertSuccessful();

        $this->get('/api/v2/best-seller', ['Accept' => 'application/json'])
            ->assertSuccessful();

        $this->get('/api/v2/best-seller', ['Accept' => 'application/json'])
            ->assertSuccessful();

        RateLimiter::clear(BestSellerInterface::LISTS_BEST_SELLERS_HISTORY_ENDPOINT.':minute');

        $this->get('/api/v2/best-seller', ['Accept' => 'application/json'])
            ->assertSuccessful();

        $this->get('/api/v2/best-seller', ['Accept' => 'application/json'])
            ->assertSuccessful();

        $this->get('/api/v2/best-seller', ['Accept' => 'application/json'])
            ->assertTooManyRequests();
    }
}
