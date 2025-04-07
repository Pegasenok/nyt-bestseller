<?php

namespace App\Pipeline;

use App\DTO\HttpCallPipelineAwareInterface;
use App\DTO\LimitsAwareDtoInterface;
use App\Exceptions\TooManyAttemptsException;
use Illuminate\Support\Facades\RateLimiter;

class LimitedCall
{
    /**
     * @throws TooManyAttemptsException
     */
    public function __invoke(LimitsAwareDtoInterface $dto, \Closure $next)
    {
        $this->limitPerMinute($dto);
        $this->limitPerDay($dto);

        return $next($dto);
    }

    /**
     * @throws TooManyAttemptsException
     */
    protected function limitPerMinute(LimitsAwareDtoInterface $dto): void
    {
        $key = $dto->getHttpEndpoint().':minute';
        if (RateLimiter::tooManyAttempts($key, $dto->getLimits()['minute'])) {
            throw new TooManyAttemptsException('Too many attempts per minute.');
        }
        RateLimiter::hit($key, $perMinute = 60);
    }

    /**
     * @throws TooManyAttemptsException
     */
    protected function limitPerDay(LimitsAwareDtoInterface $dto): void
    {
        $key = $dto->getHttpEndpoint().':day';
        if (RateLimiter::tooManyAttempts($key, $dto->getLimits()['day'])) {
            throw new TooManyAttemptsException('Too many attempts per day.');
        }
        RateLimiter::hit($key, $perDay = 86400);
    }
}
