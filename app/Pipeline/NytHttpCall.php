<?php

namespace App\Pipeline;

use App\DTO\BookResult;
use App\DTO\HttpAwareDtoInterface;
use App\Exceptions\ExternalApiPreconditionException;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use JsonSchema\Validator;

class NytHttpCall
{
    public function __construct(
        private readonly Validator $validator,
    ) {
    }

    public function __invoke(HttpAwareDtoInterface $dto, \Closure $next)
    {
        $response = $this->getNytHttp()
            ->get(
                $dto->getHttpEndpoint(),
                $dto->getHttpParameters()
            )->throw();

        $results = collect($this->processHttpResult($response))->map(function ($data) {
            return BookResult::fromJson($data);
        });

        return $next($results->toArray());
    }

    /** @see \App\Providers\AppServiceProvider::register for macros configuration */
    private function getNytHttp(): PendingRequest
    {
        return Http::nyt();
    }

    /**
     * @throws ExternalApiPreconditionException
     */
    private function processHttpResult(PromiseInterface|Response $response): array
    {
        // Decode without associative array since the validator requires objects to remain as stdClass instances
        $validationObject = json_decode($response->body(), false);
        $this->validator->validate($validationObject, $this->getEndpointJsonSchema());

        if (!$this->validator->isValid()) {
            $errorString = "Malformed response from external API:\n";
            array_map(function ($error) use (&$errorString) {
                $errorString .= $error['property'].': '.$error['message']."\n";
            }, $this->validator->getErrors());
            throw new ExternalApiPreconditionException($errorString, $response);
        }

        $json = $response->json();

        return $json['results'];
    }

    public function getEndpointJsonSchema(): object
    {
        return json_decode(Storage::disk('resources')->get('schema/best-sellers-history.json'));
    }
}
