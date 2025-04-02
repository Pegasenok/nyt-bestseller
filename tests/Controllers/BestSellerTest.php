<?php

namespace Tests\Controllers;

use App\Services\FakeHttpService;
use Tests\TestCase;

class BestSellerTest extends TestCase
{
    public function test_best_seller_offset_validation()
    {
        $this->get('/api/best-seller', ['Accept' => 'application/json'])
            ->assertHeader('Content-Type', 'application/json')
            ->assertSuccessful();

        $this->get('/api/best-seller?offset=0', ['Accept' => 'application/json'])
            ->assertSuccessful();

        $this->get('/api/best-seller?offset=40', ['Accept' => 'application/json'])
            ->assertSuccessful();

        $this->get('/api/best-seller?offset=a', ['Accept' => 'application/json'])
            ->assertUnprocessable();

        $this->get('/api/best-seller?offset=35', ['Accept' => 'application/json'])
            ->assertUnprocessable();

        // when not application/json, Laravel defaults to redirect
        $this->get('/api/best-seller?offset=a')
            ->assertRedirect();
    }

    protected function setUp(): void
    {
        parent::setUp();

        FakeHttpService::fakeNytBestSellerHistory();
    }
}
