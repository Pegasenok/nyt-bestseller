<?php

namespace App\Services;

use App\DTO\BestSellerRequestDto;
use App\Exceptions\TooManyAttemptsException;
use Illuminate\Support\Facades\RateLimiter;

class LimitedBestSellerDecorator implements BestSellerInterface
{
    public function __construct(
        private BestSellerInterface $service,
    ) {
    }

    public function getBestSellerResults(BestSellerRequestDto $dto): array
    {
        $this->limitPerMinute(self::LISTS_BEST_SELLERS_HISTORY_ENDPOINT);
        $this->limitPerDay(self::LISTS_BEST_SELLERS_HISTORY_ENDPOINT);

        return $this->service->getBestSellerResults($dto);
    }

    /**
     * @throws TooManyAttemptsException
     */
    protected function limitPerMinute(string $who): void
    {
        $key = $who.':minute';
        if (RateLimiter::tooManyAttempts($key, config('services.nyt.limits.minute'))) {
            throw new TooManyAttemptsException('Too many attempts per minute.');
        }
        RateLimiter::hit($key, $perMinute = 60);
    }

    /**
     * @throws TooManyAttemptsException
     */
    protected function limitPerDay($who): void
    {
        $key = $who.':day';
        if (RateLimiter::tooManyAttempts($key, config('services.nyt.limits.day'))) {
            throw new TooManyAttemptsException('Too many attempts per day.');
        }
        RateLimiter::hit($key, $perDay = 86400);
    }
}
