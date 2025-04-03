<?php

namespace App\DTO;

class BestSellerRequestDto
{
    public function __construct(
        public ?int $offset,
        /**
         * @var array<string>
         */
        public ?array $isbn,
        public ?string $title,
        public ?string $author
    ) {
    }

    public function cacheKey(string $prefix): string
    {
        return $prefix . ':' . md5(json_encode($this));
    }
}
