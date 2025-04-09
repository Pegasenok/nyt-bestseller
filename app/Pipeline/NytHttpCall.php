<?php

namespace App\Pipeline;

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

        $validatedResponse = $this->validator->processHttpJsonResult($response, $dto);

        return $next($validatedResponse);
    }

    /** @see \App\Providers\AppServiceProvider::register for macros configuration */
    private function getNytHttp(): PendingRequest
    {
        return Http::nyt();
    }
}
