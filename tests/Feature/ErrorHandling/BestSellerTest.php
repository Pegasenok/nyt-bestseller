<?php

namespace Tests\Feature\ErrorHandling;

use App\Services\FakeHttpService;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * Avoid extending @see \Tests\Feature\BestSellerBaseTestCase::setUp
 * due to the use of a custom HTTP fake in this test.
 */
class BestSellerTest extends TestCase
{
    /**
     * Ensure a critical log is generated and a 400 HTTP status code is returned for an invalid data structure.
     */
    public function test_broken_structure(): void
    {
        FakeHttpService::fakeNytBestSellerBrokenStructure();
        $logSpy = Log::spy();
        $result = $this->get('/api/v1/best-seller?isbn=9781524763138', ['Accept' => 'application/json']);
        $result->assertBadRequest();
        $logSpy->shouldHaveReceived('critical')->once();
    }

    /**
     * Ensure a critical log is generated and a 400 HTTP status code is returned for a non-json response.
     */
    public function test_broken_data(): void
    {
        FakeHttpService::fakeNytBestSellerBrokenData();
        $logSpy = Log::spy();
        $result = $this->get('/api/v1/best-seller?isbn=9781524763138', ['Accept' => 'application/json']);
        $result->assertBadRequest();
        $logSpy->shouldHaveReceived('critical')->once();
    }
}
