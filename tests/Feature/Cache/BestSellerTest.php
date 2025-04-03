<?php

namespace Tests\Feature\Cache;

use Illuminate\Support\Facades\Http;
use Tests\Feature\BestSellerBaseTestCase;

class BestSellerTest extends BestSellerBaseTestCase
{
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
}
