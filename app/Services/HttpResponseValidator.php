<?php

namespace App\Services;

use App\DTO\HttpAwareDtoInterface;
use App\Exceptions\ExternalApiPreconditionException;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Response;
use JsonSchema\Validator;

class HttpResponseValidator
{
    public function __construct(
        private readonly Validator $validator,
    ) {
    }

    public function processHttpResult(PromiseInterface|Response $response, HttpAwareDtoInterface $dto): array
    {
        // Decode without associative array since the validator requires objects to remain as stdClass instances
        $validationObject = json_decode($response->body(), false);
        $this->validator->validate($validationObject, $dto->getEndpointJsonSchema());

        if (!$this->validator->isValid()) {
            $errorString = "Malformed response from external API:\n";
            array_map(function ($error) use (&$errorString) {
                $errorString .= $error['property'].': '.$error['message']."\n";
            }, $this->validator->getErrors());
            throw new ExternalApiPreconditionException($errorString, $response);
        }

        return $response->json();
    }
}
