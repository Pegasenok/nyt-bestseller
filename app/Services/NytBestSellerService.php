<?php

namespace App\Services;

use App\DTO\BestSellerRequestDto;
use App\DTO\BookResult;
use App\Exceptions\ExternalApiPreconditionException;
use App\Exceptions\ExternalApiTemporaryException;
use App\Exceptions\ExternalApiViolationException;
use App\Exceptions\SomethingWrongException;
use Exception;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class NytBestSellerService implements BestSellerInterface
{
    /**
     * todo error handling to separate service and redact sensitive api-key
     * @throws ExternalApiPreconditionException
     * @throws ExternalApiTemporaryException
     * @throws ExternalApiViolationException
     * @throws SomethingWrongException
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
        } catch (RequestException $e) {
            if ($e->response->clientError()) {
                throw new ExternalApiViolationException("External error while fetching bestseller data.", $e);
            }
            throw new ExternalApiTemporaryException("Bestseller data unavailable.", $e);
        } catch (ConnectionException $e) {
            throw new ExternalApiTemporaryException("Bestseller data unavailable.", $e);
        } catch (Exception $e) {
            throw new SomethingWrongException("Failed to fetch bestseller data.", $e->getCode(), $e);
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
     * @throws ExternalApiPreconditionException
     */
    private function processHttpResult(PromiseInterface|Response $response): array
    {
        $json = $response->json();

        if (!$json) {
            throw new ExternalApiPreconditionException('Data is not a json', $response);
        }

        if (!isset($json['status'])) {
            throw new ExternalApiPreconditionException('No status found.', $response);
        }

        if ($json['status'] !== 'OK') {
            throw new ExternalApiPreconditionException('status is not OK.', $response);
        }

        if (!isset($json['results'])) {
            throw new ExternalApiPreconditionException('No results found.', $response);
        }

        return $json['results'];
    }

    public function normalizeIsbn(BestSellerRequestDto $dto): ?string
    {
        return $dto->isbn ? implode(',', $dto->isbn) : null;
    }
}
