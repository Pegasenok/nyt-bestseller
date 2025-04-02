<?php

namespace App\Services;

use App\DTO\BookResult;
use App\Exceptions\ApiPreconditionException;
use App\Exceptions\TooManyAttemptsException;
use Exception;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;

class NytBestSellerService implements BestSellerInterface
{
    const LISTS_BEST_SELLERS_HISTORY_ENDPOINT = '/lists/best-sellers/history.json';

    /**
     * @throws ApiPreconditionException
     * @throws TooManyAttemptsException
     */
    public function getBestSellerResults(): array
    {
        // todo limit decorator
        $this->limitPerMinute();
        $this->limitPerDay();

        try {
            $response = $this->getNytHttp()->get(self::LISTS_BEST_SELLERS_HISTORY_ENDPOINT)->throw();
        } catch (Exception $e) {
            throw new ApiPreconditionException("Failed to fetch bestseller data: ".$e->getMessage(), $e->getCode(), $e);
        }

        $results = collect($this->processHttpResult($response))->map(function ($data) {
            return BookResult::fromJson($data);
        });

        return $results->toArray();
    }

    /**
     * @throws TooManyAttemptsException
     */
    public function limitPerMinute(): void
    {
        $key = self::LISTS_BEST_SELLERS_HISTORY_ENDPOINT;
        if (RateLimiter::tooManyAttempts($key, config('services.nyt.limits.minute'))) {
            throw new TooManyAttemptsException('Too many attempts per minute.');
        }
        RateLimiter::hit($key, $perMinute = 60);
    }

    /**
     * @throws TooManyAttemptsException
     */
    public function limitPerDay(): void
    {
        $key = self::LISTS_BEST_SELLERS_HISTORY_ENDPOINT.'day';
        if (RateLimiter::tooManyAttempts($key, config('services.nyt.limits.day'))) {
            throw new TooManyAttemptsException('Too many attempts per day.');
        }
        RateLimiter::hit($key, $perDay = 86400);
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
}
