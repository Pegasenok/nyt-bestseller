<?php

namespace Tests\Feature\Isbn;

use Illuminate\Support\Facades\Http;
use Tests\Feature\BestSellerBaseTestCase;

class BestSellerTest extends BestSellerBaseTestCase
{
    /**
     * A basic test example.
     *
     * @dataProvider versionDataProvider
     */
    public function test_isbn_string(string $version): void
    {
        $this->getBestSellerApi('best-seller?isbn=9781524763138', $version)
            ->assertSuccessful();

        Http::assertSent(function ($request) {
            return $request->method() === 'GET' &&
                $request['isbn'] === '9781524763138';
        });
    }

    /**
     * @dataProvider versionDataProvider
     */
    public function test_isbn_array(string $version)
    {
        $this->getBestSellerApi('best-seller?isbn[]=9781442444928', $version)
            ->assertSuccessful();
        Http::assertSent(function ($request) {
            return $request->method() === 'GET' &&
                $request['isbn'] === '9781442444928';
        });
    }

    /**
     * @dataProvider versionDataProvider
     */
    public function test_isbn_array_conversion(string $version)
    {
        $this->getBestSellerApi('best-seller?isbn[]=9781524763138&isbn[]=9781442444928', $version)
            ->assertSuccessful();
        Http::assertSent(function ($request) {
            return $request->method() === 'GET' &&
                $request['isbn'] === '9781524763138,9781442444928';
        });
    }
}
