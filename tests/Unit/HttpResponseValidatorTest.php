<?php

namespace Tests\Unit;

use App\DTO\HttpAwareDtoInterface;
use App\Exceptions\ExternalApiPreconditionException;
use App\Services\HttpResponseValidator;
use Illuminate\Http\Client\Response;
use JsonSchema\Validator;
use PHPUnit\Framework\TestCase;

class HttpResponseValidatorTest extends TestCase
{
    private Validator $jsonValidator;

    private HttpResponseValidator $httpValidator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->jsonValidator = $this->createMock(Validator::class);
        $this->httpValidator = new HttpResponseValidator($this->jsonValidator);
    }

    public function test_process_http_json_result_success(): void
    {
        // Arrange
        $responseData = ['status' => 'OK', 'results' => [['title' => 'Book Title']]];
        $responseJson = json_encode($responseData);

        $response = $this->createMock(Response::class);
        $response->method('body')->willReturn($responseJson);
        $response->method('json')->willReturn($responseData);

        $dto = $this->createMock(HttpAwareDtoInterface::class);
        $schema = ['type' => 'object'];
        $dto->method('getEndpointJsonSchema')->willReturn($schema);

        $this->jsonValidator->method('isValid')->willReturn(true);
        $this->jsonValidator->expects($this->once())
            ->method('validate')
            ->with(
                $this->callback(function ($obj) {
                    return $obj->status === 'OK';
                }),
                $schema
            );

        // Act
        $result = $this->httpValidator->processHttpJsonResult($response, $dto);

        // Assert
        $this->assertEquals($responseData, $result);
    }

    public function test_process_http_json_result_validation_failure(): void
    {
        // Arrange
        $responseData = ['invalid' => 'data'];
        $responseJson = json_encode($responseData);

        $response = $this->createMock(Response::class);
        $response->method('body')->willReturn($responseJson);

        $dto = $this->createMock(HttpAwareDtoInterface::class);
        $schema = ['type' => 'object'];
        $dto->method('getEndpointJsonSchema')->willReturn($schema);

        $this->jsonValidator->method('isValid')->willReturn(false);
        $this->jsonValidator->method('getErrors')->willReturn([
            ['property' => 'status', 'message' => 'The property status is required'],
        ]);

        // Assert
        $this->expectException(ExternalApiPreconditionException::class);
        $this->expectExceptionMessage('Malformed response from external API');
        $this->expectExceptionMessage('status');
        $this->expectExceptionMessage('The property status is required');

        // Act
        $this->httpValidator->processHttpJsonResult($response, $dto);
    }
}
