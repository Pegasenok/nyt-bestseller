<?php

namespace Tests\Feature\Cache;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\Feature\BestSellerBaseTestCase;

class BestSellerTest extends BestSellerBaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['services.nyt.cache.enabled' => true]);
        Cache::flush();
    }

    /**
     * todo add testing of Cache actual hits
     *
     * @dataProvider versionDataProvider
     */
    public function test_best_seller_cache_http_requests(string $version): void
    {
        $this->getBestSellerApi('best-seller?offset=20', $version)
            ->assertSuccessful();
        Http::assertSent(function ($request) {
            return $request->method() === 'GET' && $request['offset'] === 20;
        });

        $this->getBestSellerApi('best-seller?offset=20', $version)
            ->assertSuccessful();
        // only first http request registered
        Http::assertSentCount(1);

        $this->getBestSellerApi('best-seller?offset=40', $version)
            ->assertSuccessful();
        Http::assertSent(function ($request) {
            return $request->method() === 'GET' && $request['offset'] === 40;
        });
        // another http request registered
        Http::assertSentCount(2);

        $this->getBestSellerApi('best-seller?offset=20', $version)
            ->assertSuccessful();
        $this->getBestSellerApi('best-seller?offset=40', $version)
            ->assertSuccessful();
        // no more http requests registered (2 from previous calls)
        Http::assertSentCount(2);
    }
}
