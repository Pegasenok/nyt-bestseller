<?php

namespace Tests\Feature\Cache;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\Feature\BestSellerBaseTestCase;

class BestSellerV2Test extends BestSellerBaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['services.nyt.cache.enabled' => true]);
        Cache::flush();
    }

    /**
     * todo add testing of Cache actual hits
     */
    public function test_best_seller_cache_http_requests(): void
    {
        $this->get('/api/v2/best-seller?offset=20', ['Accept' => 'application/json'])
            ->assertSuccessful();
        Http::assertSent(function ($request) {
            return $request->method() === 'GET' && $request['offset'] === 20;
        });

        $this->get('/api/v2/best-seller?offset=20', ['Accept' => 'application/json'])
            ->assertSuccessful();
        // only first http request registered
        Http::assertSentCount(1);

        $this->get('/api/v2/best-seller?offset=40', ['Accept' => 'application/json'])
            ->assertSuccessful();
        Http::assertSent(function ($request) {
            return $request->method() === 'GET' && $request['offset'] === 40;
        });
        // another http request registered
        Http::assertSentCount(2);

        $this->get('/api/v2/best-seller?offset=20', ['Accept' => 'application/json'])
            ->assertSuccessful();
        $this->get('/api/v2/best-seller?offset=40', ['Accept' => 'application/json'])
            ->assertSuccessful();
        // no more http requests registered (2 from previous calls)
        Http::assertSentCount(2);
    }
}
