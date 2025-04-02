<?php

namespace App\Services;

use App\Exceptions\ApiPreconditionException;
use App\Exceptions\TooManyAttemptsException;

interface BestSellerInterface
{
    const LISTS_BEST_SELLERS_HISTORY_ENDPOINT = '/lists/best-sellers/history.json';

    /**
     * @throws ApiPreconditionException
     * @throws TooManyAttemptsException
     * todo parameters
     */
    public function getBestSellerResults(): array;
}
