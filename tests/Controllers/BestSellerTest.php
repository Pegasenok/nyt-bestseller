<?php

namespace Tests\Controllers;

use App\Services\BestSellerInterface;
use App\Services\FakeHttpService;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class BestSellerTest extends TestCase
{
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        RateLimiter::clear(BestSellerInterface::LISTS_BEST_SELLERS_HISTORY_ENDPOINT.':minute');
        RateLimiter::clear(BestSellerInterface::LISTS_BEST_SELLERS_HISTORY_ENDPOINT.':day');
    }

    public function test_best_seller_offset_validation()
    {
        $this->get('/api/best-seller', ['Accept' => 'application/json'])
            ->assertHeader('Content-Type', 'application/json')
            ->assertSuccessful();

        $this->get('/api/best-seller?offset=0', ['Accept' => 'application/json'])
            ->assertSuccessful();

        $this->get('/api/best-seller?offset=40', ['Accept' => 'application/json'])
            ->assertSuccessful();

        $this->get('/api/best-seller?offset=a', ['Accept' => 'application/json'])
            ->assertUnprocessable();

        $this->get('/api/best-seller?offset=35', ['Accept' => 'application/json'])
            ->assertUnprocessable();

        // when not application/json, Laravel defaults to redirect
        $this->get('/api/best-seller?offset=a')
            ->assertRedirect();
    }

    public function test_best_seller_api_limits_minute()
    {
        RateLimiter::clear(BestSellerInterface::LISTS_BEST_SELLERS_HISTORY_ENDPOINT.':minute');
        config(['services.nyt.limits.minute' => 3]);

        $this->get('/api/best-seller', ['Accept' => 'application/json'])
            ->assertSuccessful();

        $this->get('/api/best-seller', ['Accept' => 'application/json'])
            ->assertSuccessful();

        $this->get('/api/best-seller', ['Accept' => 'application/json'])
            ->assertSuccessful();

        $this->get('/api/best-seller', ['Accept' => 'application/json'])
            ->assertTooManyRequests();

        $this->get('/api/best-seller', ['Accept' => 'application/json'])
            ->assertTooManyRequests();
    }

    public function test_best_seller_api_limits_day()
    {
        RateLimiter::clear(BestSellerInterface::LISTS_BEST_SELLERS_HISTORY_ENDPOINT.':minute');
        RateLimiter::clear(BestSellerInterface::LISTS_BEST_SELLERS_HISTORY_ENDPOINT.':day');
        config(['services.nyt.limits.minute' => 3]);
        config(['services.nyt.limits.day' => 5]);

        $this->get('/api/best-seller', ['Accept' => 'application/json'])
            ->assertSuccessful();

        $this->get('/api/best-seller', ['Accept' => 'application/json'])
            ->assertSuccessful();

        $this->get('/api/best-seller', ['Accept' => 'application/json'])
            ->assertSuccessful();

        RateLimiter::clear(BestSellerInterface::LISTS_BEST_SELLERS_HISTORY_ENDPOINT.':minute');

        $this->get('/api/best-seller', ['Accept' => 'application/json'])
            ->assertSuccessful();

        $this->get('/api/best-seller', ['Accept' => 'application/json'])
            ->assertSuccessful();

        $this->get('/api/best-seller', ['Accept' => 'application/json'])
            ->assertTooManyRequests();
    }

    protected function setUp(): void
    {
        parent::setUp();

        FakeHttpService::fakeNytBestSellerHistory();
    }
}
