<?php

namespace App\Services;

use App\DTO\BestSellerRequestDto;
use App\DTO\BookResult;
use App\Exceptions\ApiPreconditionException;
use Exception;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class NytBestSellerService implements BestSellerInterface
{
    /**
     * @throws ApiPreconditionException
     */
    public function getBestSellerResults(BestSellerRequestDto $dto): array
    {
        try {
            $response = $this->getNytHttp()
                ->get(self::LISTS_BEST_SELLERS_HISTORY_ENDPOINT, [
                    'author' => $dto->author,
                    'isbn' => $this->normalizeIsbn($dto),
                    'title' => $dto->title,
                    'offset' => $dto->offset,
                ])
                ->throw();
        } catch (Exception $e) {
            throw new ApiPreconditionException("Failed to fetch bestseller data: ".$e->getMessage(), $e->getCode(), $e);
        }

        $results = collect($this->processHttpResult($response))->map(function ($data) {
            return BookResult::fromJson($data);
        });

        return $results->toArray();
    }


    /** @see \App\Providers\AppServiceProvider::register for macros configuration */
    private function getNytHttp(): PendingRequest
    {
        return Http::nyt();
    }

    /**
     * @throws ApiPreconditionException
     */
    private function processHttpResult(PromiseInterface|Response $response): array
    {
        $json = $response->json();

        if (!$json) {
            throw new ApiPreconditionException('Data is not a json');
        }

        if ($json['status'] !== 'OK') {
            throw new ApiPreconditionException('status is not OK.');
        }

        if (!isset($json['results'])) {
            throw new ApiPreconditionException('No results found.');
        }

        return $json['results'];
    }

    public function normalizeIsbn(BestSellerRequestDto $dto): ?string
    {
        return $dto->isbn ? implode(',', $dto->isbn) : null;
    }
}
