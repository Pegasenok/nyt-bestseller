<?php

namespace Tests\Feature\Validation;

use Illuminate\Support\Facades\Http;
use Tests\Feature\BestSellerBaseTestCase;

class BestSellerTest extends BestSellerBaseTestCase
{
    /**
     * A basic test example.
     *
     * @dataProvider versionDataProvider
     */
    public function test_validation_isbn(string $version): void
    {
        $this->getBestSellerApi('best-seller?isbn[]=1234567890', $version)
            ->assertUnprocessable()
            ->assertSeeText('must be a valid International Standard Book Number (ISBN)');
        Http::assertNothingSent();

        $this->getBestSellerApi('best-seller?isbn[]=123', $version)
            ->assertUnprocessable()
            ->assertSeeText('must be a valid International Standard Book Number (ISBN)');
        Http::assertNothingSent();

        $this->getBestSellerApi('best-seller?isbn[]=abcdefghij', $version)
            ->assertUnprocessable()
            ->assertSeeText('must be a valid International Standard Book Number (ISBN)');
        Http::assertNothingSent();

        $this->getBestSellerApi('best-seller?isbn[]=9781524763138&isbn[]=1234567890&isbn[]=9781442444928', $version)
            ->assertUnprocessable()
            ->assertSeeText('must be a valid International Standard Book Number (ISBN)');
        Http::assertNothingSent();
    }

    /**
     * @dataProvider versionDataProvider
     */
    public function test_validation_offset(string $version)
    {
        $this->getBestSellerApi('best-seller', $version)
            ->assertHeader('Content-Type', 'application/json')
            ->assertSuccessful();

        $this->getBestSellerApi('best-seller?offset=0', $version)
            ->assertSuccessful();

        $this->getBestSellerApi('best-seller?offset=40', $version)
            ->assertSuccessful();

        $this->getBestSellerApi('best-seller?offset=a', $version)
            ->assertUnprocessable();

        $this->getBestSellerApi('best-seller?offset=35', $version)
            ->assertUnprocessable();

        // when not application/json, Laravel defaults to redirect
        $this->get("/api/$version/best-seller?offset=a")
            ->assertRedirect();
    }
}
