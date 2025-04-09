<?php

namespace App\Services;

use App\DTO\BestSellerRequestDto;
use Illuminate\Support\Facades\Cache;

class CachedBestSellerDecorator implements BestSellerInterface
{
    public function __construct(
        private BestSellerInterface $service,
    ) {
    }

    public function getBestSellerResults(BestSellerRequestDto $dto): array
    {
        // todo try to extract to configuration, but tests should be able to enable/disable this
        if (!config('services.nyt.cache.enabled')) {
            return $this->service->getBestSellerResults($dto);
        }
        try {
            $data = Cache::remember(
                $dto->getCacheKey(),
                $oneMinute = 60,
                function () use ($dto) {
                    return $this->service->getBestSellerResults($dto);
                }
            );
        } catch (\Throwable $throwable) {
            Cache::forget($dto->getCacheKey());
            throw $throwable;
        }

        return $data;
    }
}
