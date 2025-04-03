<?php

namespace Tests\Feature;

use App\Services\BestSellerInterface;
use App\Services\FakeHttpService;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class BestSellerBaseTestCase extends TestCase
{
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        RateLimiter::clear(BestSellerInterface::LISTS_BEST_SELLERS_HISTORY_ENDPOINT.':minute');
        RateLimiter::clear(BestSellerInterface::LISTS_BEST_SELLERS_HISTORY_ENDPOINT.':day');
    }

    protected function setUp(): void
    {
        parent::setUp();

        FakeHttpService::fakeNytBestSellerHistory();
    }
}
