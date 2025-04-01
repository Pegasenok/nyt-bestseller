<?php

namespace Tests\Controllers;

use Tests\TestCase;

class BestSellerTest extends TestCase
{
    public function testBestSeller()
    {
        $response = $this->get('/api/best-seller?offset=5', ['Accept' => 'application/json']);
        $response->assertSuccessful();

        $response = $this->get('/api/best-seller?offset=a', ['Accept' => 'application/json']);
        $response->assertStatus(422);

        // when not application/json, Laravel defaults to redirect
        $response = $this->get('/api/best-seller?offset=a');
        $response->assertRedirect();
    }
}
