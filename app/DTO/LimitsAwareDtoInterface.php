<?php

namespace App\DTO;

interface LimitsAwareDtoInterface
{
    /**
     * @return array{day: int, minute: int}
     */
    public function getLimits(): array;
}
