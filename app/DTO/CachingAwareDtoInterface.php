<?php

namespace App\DTO;

interface CachingAwareDtoInterface
{
    public function getCacheKey(): string;
}
