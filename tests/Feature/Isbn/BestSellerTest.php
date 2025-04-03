<?php

namespace Tests\Feature\Isbn;

use App\Services\BestSellerInterface;
use App\Services\FakeHttpService;
use Illuminate\Support\Facades\Http;
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

    /**
     * A basic test example.
     */
    public function test_isbn_string(): void
    {
        $this->get('/api/v1/best-seller?isbn=9781524763138', ['Accept' => 'application/json'])
            ->assertSuccessful();

        Http::assertSent(function ($request) {
            return $request->method() === 'GET' &&
                $request['isbn'] === '9781524763138';
        });
    }

    public function test_isbn_array()
    {
        $this->get('/api/v1/best-seller?isbn[]=9781442444928', ['Accept' => 'application/json'])
            ->assertSuccessful();
        Http::assertSent(function ($request) {
            return $request->method() === 'GET' &&
                $request['isbn'] === '9781442444928';
        });
    }

    public function test_isbn_array_conversion()
    {
        $this->get('/api/v1/best-seller?isbn[]=9781524763138&isbn[]=9781442444928', ['Accept' => 'application/json'])
            ->assertSuccessful();
        Http::assertSent(function ($request) {
            return $request->method() === 'GET' &&
                $request['isbn'] === '9781524763138,9781442444928';
        });
    }

    protected function setUp(): void
    {
        parent::setUp();

        FakeHttpService::fakeNytBestSellerHistory();
    }
}
