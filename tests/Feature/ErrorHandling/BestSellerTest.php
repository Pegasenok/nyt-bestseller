<?php

namespace Tests\Feature\ErrorHandling;

use App\Services\FakeHttpService;
use Illuminate\Support\Facades\Log;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * Avoid extending @see \Tests\Feature\BestSellerBaseTestCase::setUp
 * due to the use of a custom HTTP fake in this test.
 */
class BestSellerTest extends TestCase
{
    protected function getBestSellerApi(
        $uri,
        string $version = 'v1',
        $headers = ['Accept' => 'application/json'],
    ): TestResponse {
        return $this->get("/api/$version/$uri", $headers);
    }

    public static function versionDataProvider(): array
    {
        return [
            'v1' => ['version' => 'v1'],
            'v2' => ['version' => 'v2'],
        ];
    }

    /**
     * Ensure a critical log is generated and a 400 HTTP status code is returned for an invalid data structure.
     *
     * @dataProvider versionDataProvider
     */
    public function test_broken_structure(string $version): void
    {
        FakeHttpService::fakeNytBestSellerBrokenStructure();
        $logSpy = Log::spy();
        $result = $this->getBestSellerApi('best-seller?isbn=9781524763138', $version);
        $result->assertBadRequest();
        $logSpy->shouldHaveReceived('critical')->once();
    }

    /**
     * Ensure a critical log is generated and a 400 HTTP status code is returned for a non-json response.
     *
     * @dataProvider versionDataProvider
     */
    public function test_broken_data(string $version): void
    {
        FakeHttpService::fakeNytBestSellerBrokenData();
        $logSpy = Log::spy();
        $result = $this->getBestSellerApi('best-seller?isbn=9781524763138', $version);
        $result->assertBadRequest();
        $logSpy->shouldHaveReceived('critical')->once();
    }

    /**
     * Ensure a critical log is generated and a 400 HTTP status code is returned for a non-json response.
     *
     * @dataProvider versionDataProvider
     */
    public function test_broken_connection(string $version): void
    {
        FakeHttpService::fakeNytBestSellerBrokenConnection();
        $logSpy = Log::spy();
        $result = $this->getBestSellerApi('best-seller?isbn=9781524763138', $version);
        $result->assertBadRequest()->assertSeeText('retry in an hour');
        $logSpy->shouldHaveReceived('warning')->once();
    }

    /**
     * Test JSON schema validation for various malformed HTTP responses from an external API.
     *
     * @dataProvider brokenSchemaDataProvider
     */
    public function test_broken_schema_with_data_provider(string $jsonFile, array $expectedTexts, string $version): void
    {
        FakeHttpService::fakeNytBestSellerHistoryCustom($jsonFile);
        $logSpy = Log::spy();
        $result = $this->getBestSellerApi('best-seller', $version);
        $result->assertBadRequest();

        foreach ($expectedTexts as $text) {
            $result->assertSeeText($text);
        }

        $logSpy->shouldHaveReceived('critical')->once();
    }

    public static function brokenSchemaDataProvider(): array
    {
        $versions = ['v1', 'v2'];
        $data = [];

        foreach ($versions as $version) {
            $data["invalid_status_value_$version"] = [
                'jsonFile' => 'best-sellers-history-bug1.json',
                'expectedTexts' => [
                    'Malformed response',
                    'status:',
                ],
                'version' => $version,
            ];
            $data["missing_required_fields_$version"] = [
                'jsonFile' => 'best-sellers-history-bug2.json',
                'expectedTexts' => [
                    'Malformed response',
                    'is required',
                    'num_results',
                ],
                'version' => $version,
            ];
            $data["missing_isbns_in_result_$version"] = [
                'jsonFile' => 'best-sellers-history-bug3.json',
                'expectedTexts' => [
                    'Malformed response',
                    'results[3].isbns: The property isbns is required',
                ],
                'version' => $version,
            ];
            $data["incorrect_data_types_$version"] = [
                'jsonFile' => 'best-sellers-history-bug4.json',
                'expectedTexts' => [
                    'Malformed response',
                    'description: Integer value found, but a string or a null is required',
                    'contributor: Boolean value found, but a string or a null is required',
                ],
                'version' => $version,
            ];
        }

        return $data;
    }
}
