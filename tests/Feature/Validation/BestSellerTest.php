<?php

namespace Tests\Feature\Validation;

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
    public function test_validation_isbn(): void
    {
        $this->get('/api/v1/best-seller?isbn[]=1234567890', ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertSeeText('must be a valid International Standard Book Number (ISBN)');
        Http::assertNothingSent();

        $this->get('/api/v1/best-seller?isbn[]=123', ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertSeeText('must be a valid International Standard Book Number (ISBN)');
        Http::assertNothingSent();

        $this->get('/api/v1/best-seller?isbn[]=abcdefghij', ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertSeeText('must be a valid International Standard Book Number (ISBN)');
        Http::assertNothingSent();

        $this->get('/api/v1/best-seller?isbn[]=9781524763138&isbn[]=1234567890&isbn[]=9781442444928', ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertSeeText('must be a valid International Standard Book Number (ISBN)');
        Http::assertNothingSent();
    }

    protected function setUp(): void
    {
        parent::setUp();

        FakeHttpService::fakeNytBestSellerHistory();
    }
}
