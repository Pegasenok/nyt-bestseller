<?php

namespace Tests\Feature\Validation;

use Illuminate\Support\Facades\Http;
use Tests\Feature\BestSellerBaseTestCase;

class BestSellerV2Test extends BestSellerBaseTestCase
{
    /**
     * A basic test example.
     */
    public function test_validation_isbn(): void
    {
        $this->get('/api/v2/best-seller?isbn[]=1234567890', ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertSeeText('must be a valid International Standard Book Number (ISBN)');
        Http::assertNothingSent();

        $this->get('/api/v2/best-seller?isbn[]=123', ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertSeeText('must be a valid International Standard Book Number (ISBN)');
        Http::assertNothingSent();

        $this->get('/api/v2/best-seller?isbn[]=abcdefghij', ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertSeeText('must be a valid International Standard Book Number (ISBN)');
        Http::assertNothingSent();

        $this->get('/api/v2/best-seller?isbn[]=9781524763138&isbn[]=1234567890&isbn[]=9781442444928',
            ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertSeeText('must be a valid International Standard Book Number (ISBN)');
        Http::assertNothingSent();
    }


    public function test_validation_offset()
    {
        $this->get('/api/v2/best-seller', ['Accept' => 'application/json'])
            ->assertHeader('Content-Type', 'application/json')
            ->assertSuccessful();

        $this->get('/api/v2/best-seller?offset=0', ['Accept' => 'application/json'])
            ->assertSuccessful();

        $this->get('/api/v2/best-seller?offset=40', ['Accept' => 'application/json'])
            ->assertSuccessful();

        $this->get('/api/v2/best-seller?offset=a', ['Accept' => 'application/json'])
            ->assertUnprocessable();

        $this->get('/api/v2/best-seller?offset=35', ['Accept' => 'application/json'])
            ->assertUnprocessable();

        // when not application/json, Laravel defaults to redirect
        $this->get('/api/v2/best-seller?offset=a')
            ->assertRedirect();
    }
}
