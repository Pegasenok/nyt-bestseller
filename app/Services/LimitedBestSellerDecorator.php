<?php

namespace App\Services;

use App\Exceptions\TooManyAttemptsException;
use Illuminate\Support\Facades\RateLimiter;

class LimitedBestSellerDecorator implements BestSellerInterface
{
    public function __construct(
        private BestSellerInterface $service,
    ) {
    }

    public function getBestSellerResults(): array
    {
        $this->limitPerMinute();
        $this->limitPerDay();

        return $this->service->getBestSellerResults();
    }

    /**
     * @throws TooManyAttemptsException
     */
    public function limitPerMinute(): void
    {
        $key = self::LISTS_BEST_SELLERS_HISTORY_ENDPOINT.':minute';
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
        $key = self::LISTS_BEST_SELLERS_HISTORY_ENDPOINT.':day';
        if (RateLimiter::tooManyAttempts($key, config('services.nyt.limits.day'))) {
            throw new TooManyAttemptsException('Too many attempts per day.');
        }
        RateLimiter::hit($key, $perDay = 86400);
    }
}
