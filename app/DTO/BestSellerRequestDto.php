<?php

namespace App\DTO;

use App\Services\BestSellerInterface;
use Illuminate\Support\Facades\Storage;

/**
 * todo: this is more than a Dto now, consider new naming in the future
 */
class BestSellerRequestDto implements CachingAwareDtoInterface, LimitsAwareDtoInterface, HttpAwareDtoInterface
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

    public function getHttpEndpoint(): string
    {
        return BestSellerInterface::LISTS_BEST_SELLERS_HISTORY_ENDPOINT;
    }

    public function getHttpParameters(): array
    {
        return [
            'author' => $this->author,
            'isbn' => $this->normalizeIsbn(),
            'title' => $this->title,
            'offset' => $this->offset,
        ];
    }

    public function getCacheKey(): string
    {
        return BestSellerInterface::LISTS_BEST_SELLERS_HISTORY_ENDPOINT.':'.md5(json_encode($this));
    }

    /**
     * @return array{day: int, minute: int}
     */
    public function getLimits(): array
    {
        return config('services.nyt.limits');
    }

    protected function normalizeIsbn(): ?string
    {
        return $this->isbn ? implode(',', $this->isbn) : null;
    }

    public function getEndpointJsonSchema()
    {
        return json_decode(Storage::disk('resources')->get('schema/best-sellers-history.json'));
    }
}
