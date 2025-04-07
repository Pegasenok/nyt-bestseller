<?php

namespace App\Pipeline;

use App\DTO\BookResult;
use App\DTO\HttpAwareDtoInterface;
use App\Services\HttpResponseValidator;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class NytHttpCall
{
    public function __construct(
        private readonly HttpResponseValidator $validator,
    ) {
    }

    public function __invoke(HttpAwareDtoInterface $dto, \Closure $next)
    {
        $response = $this->getNytHttp()
            ->get(
                $dto->getHttpEndpoint(),
                $dto->getHttpParameters()
            )->throw();

        // todo this is specific to BestSellers history endpoint
        $validatedResponse = $this->validator->processHttpResult($response, $dto);
        $results = collect($validatedResponse['results'])->map(function ($data) {
            return BookResult::fromJson($data);
        });

        return $next($results->toArray());
    }

    /** @see \App\Providers\AppServiceProvider::register for macros configuration */
    private function getNytHttp(): PendingRequest
    {
        return Http::nyt();
    }
}
