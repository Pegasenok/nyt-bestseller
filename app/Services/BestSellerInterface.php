<?php

namespace App\Services;

use App\DTO\BestSellerRequestDto;
use App\Exceptions\ApiPreconditionException;
use App\Exceptions\TooManyAttemptsException;

interface BestSellerInterface
{
    const LISTS_BEST_SELLERS_HISTORY_ENDPOINT = '/lists/best-sellers/history.json';

    /**
     * @throws ApiPreconditionException
     * @throws TooManyAttemptsException
     */
    public function getBestSellerResults(BestSellerRequestDto $dto): array;
}
