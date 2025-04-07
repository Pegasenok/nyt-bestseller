<?php

namespace Tests\Feature\ErrorHandling;

use App\Services\FakeHttpService;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * Avoid extending @see \Tests\Feature\BestSellerBaseTestCase::setUp
 * due to the use of a custom HTTP fake in this test.
 */
class BestSellerV2Test extends TestCase
{
    /**
     * Ensure a critical log is generated and a 400 HTTP status code is returned for an invalid data structure.
     */
    public function test_broken_structure(): void
    {
        FakeHttpService::fakeNytBestSellerBrokenStructure();
        $logSpy = Log::spy();
        $result = $this->get('/api/v2/best-seller?isbn=9781524763138', ['Accept' => 'application/json']);
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
        $result = $this->get('/api/v2/best-seller?isbn=9781524763138', ['Accept' => 'application/json']);
        $result->assertBadRequest();
        $logSpy->shouldHaveReceived('critical')->once();
    }

    /**
     * Test JSON schema validation for various malformed HTTP responses from an external API.
     *
     * @dataProvider brokenSchemaDataProvider
     */
    public function test_broken_schema_with_data_provider(string $jsonFile, array $expectedTexts): void
    {
        FakeHttpService::fakeNytBestSellerHistoryCustom($jsonFile);
        $logSpy = Log::spy();
        $result = $this->get('/api/v2/best-seller', ['Accept' => 'application/json']);
        $result->assertBadRequest();

        foreach ($expectedTexts as $text) {
            $result->assertSeeText($text);
        }

        $logSpy->shouldHaveReceived('critical')->once();
    }

    public static function brokenSchemaDataProvider(): array
    {
        return [
            'invalid_status_value' => [
                'jsonFile' => 'best-sellers-history-bug1.json',
                'expectedTexts' => [
                    'Malformed response',
                    'status:'
                ],
            ],
            'missing_required_fields' => [
                'jsonFile' => 'best-sellers-history-bug2.json',
                'expectedTexts' => [
                    'Malformed response',
                    'is required',
                    'num_results'
                ],
            ],
            'missing_isbns_in_result' => [
                'jsonFile' => 'best-sellers-history-bug3.json',
                'expectedTexts' => [
                    'Malformed response',
                    'results[3].isbns: The property isbns is required'
                ],
            ],
            'incorrect_data_types' => [
                'jsonFile' => 'best-sellers-history-bug4.json',
                'expectedTexts' => [
                    'Malformed response',
                    'description: Integer value found, but a string or a null is required',
                    'contributor: Boolean value found, but a string or a null is required',
                ],
            ],
        ];
    }
}
