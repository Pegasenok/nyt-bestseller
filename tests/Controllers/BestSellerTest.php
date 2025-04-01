<?php

namespace Tests\Controllers;

use Tests\TestCase;

class BestSellerTest extends TestCase
{
    public function test_best_seller_offset_validation()
    {
        $response = $this->get('/api/best-seller', ['Accept' => 'application/json']);
        $response->assertSuccessful();

        $response = $this->get('/api/best-seller?offset=0', ['Accept' => 'application/json']);
        $response->assertSuccessful();

        $response = $this->get('/api/best-seller?offset=40', ['Accept' => 'application/json']);
        $response->assertSuccessful();

        $response = $this->get('/api/best-seller?offset=a', ['Accept' => 'application/json']);
        $response->assertStatus(422);

        $response = $this->get('/api/best-seller?offset=35', ['Accept' => 'application/json']);
        $response->assertStatus(422);

        // when not application/json, Laravel defaults to redirect
        $response = $this->get('/api/best-seller?offset=a');
        $response->assertRedirect();
    }
}
