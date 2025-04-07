<?php

namespace Tests\Feature;

use App\Services\BestSellerInterface;
use App\Services\FakeHttpService;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Testing\TestResponse;
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

    protected function getBestSellerApi(
        $uri,
        string $version = 'v1',
        $headers = ['Accept' => 'application/json'],
    ): TestResponse {
        return $this->get("/api/$version/$uri", $headers);
    }

    public static function versionDataProvider(): array
    {
        return [
            'v1' => ['version' => 'v1'],
            'v2' => ['version' => 'v2'],
        ];
    }
}
