<?php

namespace Tests\Feature\RateLimit;

use App\Services\BestSellerInterface;
use App\Services\FakeHttpService;
use Illuminate\Support\Facades\RateLimiter;
use Tests\Feature\BestSellerBaseTestCase;
use Tests\TestCase;

class BestSellerTest extends BestSellerBaseTestCase
{
    public function test_best_seller_api_limits_minute()
    {
        RateLimiter::clear(BestSellerInterface::LISTS_BEST_SELLERS_HISTORY_ENDPOINT.':minute');
        config(['services.nyt.limits.minute' => 3]);

        $this->get('/api/v1/best-seller', ['Accept' => 'application/json'])
            ->assertSuccessful();

        $this->get('/api/v1/best-seller', ['Accept' => 'application/json'])
            ->assertSuccessful();

        $this->get('/api/v1/best-seller', ['Accept' => 'application/json'])
            ->assertSuccessful();

        $this->get('/api/v1/best-seller', ['Accept' => 'application/json'])
            ->assertTooManyRequests();

        $this->get('/api/v1/best-seller', ['Accept' => 'application/json'])
            ->assertTooManyRequests();
    }

    public function test_best_seller_api_limits_day()
    {
        RateLimiter::clear(BestSellerInterface::LISTS_BEST_SELLERS_HISTORY_ENDPOINT.':minute');
        RateLimiter::clear(BestSellerInterface::LISTS_BEST_SELLERS_HISTORY_ENDPOINT.':day');
        config(['services.nyt.limits.minute' => 3]);
        config(['services.nyt.limits.day' => 5]);

        $this->get('/api/v1/best-seller', ['Accept' => 'application/json'])
            ->assertSuccessful();

        $this->get('/api/v1/best-seller', ['Accept' => 'application/json'])
            ->assertSuccessful();

        $this->get('/api/v1/best-seller', ['Accept' => 'application/json'])
            ->assertSuccessful();

        RateLimiter::clear(BestSellerInterface::LISTS_BEST_SELLERS_HISTORY_ENDPOINT.':minute');

        $this->get('/api/v1/best-seller', ['Accept' => 'application/json'])
            ->assertSuccessful();

        $this->get('/api/v1/best-seller', ['Accept' => 'application/json'])
            ->assertSuccessful();

        $this->get('/api/v1/best-seller', ['Accept' => 'application/json'])
            ->assertTooManyRequests();
    }
}
