<?php

namespace App\Pipeline;

use App\DTO\CachingAwareDtoInterface;
use Illuminate\Support\Facades\Cache;

class CachedHttpCall
{
    public function __invoke(CachingAwareDtoInterface $dto, \Closure $next)
    {
        // todo try to extract to configuration, but tests should be able to enable/disable this
        if (!config('services.nyt.cache.enabled')) {
            return $next($dto);
        }
        try {
            $data = Cache::remember(
                $dto->getCacheKey(),
                $oneMinute = 60,
                function () use ($dto, $next) {
                    return $next($dto);
                }
            );
        } catch (\Throwable $throwable) {
            Cache::forget($dto->getCacheKey());
            throw $throwable;
        }

        return $data;
    }
}
