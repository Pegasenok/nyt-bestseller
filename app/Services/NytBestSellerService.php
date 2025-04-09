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
use Illuminate\Support\Facades\Storage;
use JsonSchema\Validator;

class NytBestSellerService implements BestSellerInterface
{
    public function __construct(
        private readonly Validator $validator,
    ) {
    }

    /**
     * todo error handling to separate service and redact sensitive api-key
     *
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
                throw new ExternalApiViolationException('External error while fetching bestseller data.', $e);
            }
            throw new ExternalApiTemporaryException('Bestseller data unavailable, retry in 5 minutes.', $e);
        } catch (ConnectionException $e) {
            throw new ExternalApiTemporaryException('Bestseller data unavailable, retry in an hour.', $e);
        } catch (Exception $e) {
            throw new SomethingWrongException('Failed to fetch bestseller data.', $e->getCode(), $e);
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

    public function normalizeIsbn(BestSellerRequestDto $dto): ?string
    {
        return $dto->isbn ? implode(',', $dto->isbn) : null;
    }

    public function getEndpointJsonSchema(): object
    {
        return json_decode(Storage::disk('resources')->get('schema/best-sellers-history.json'));
    }
}
