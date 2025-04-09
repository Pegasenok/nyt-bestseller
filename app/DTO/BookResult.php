<?php

namespace App\DTO;

use Illuminate\Support\Facades\Log;

class BookResult
{
    // todo: sample
    public function __construct(
        public string $title = '',
        public string $description = '',
        public string $contributor = '',
        public string $author = '',
        public string $contributorNote = '',
        public int $price = 0,
        public string $ageGroup = '',
        public string $publisher = '',
        public string $primaryIsbn13 = '',
        public string $primaryIsbn10 = '',
    ) {
    }

    public static function fromJson(array $json): self
    {
        // todo may be refactored into business layer
        if (count($json['isbns']) > 1) {
            Log::notice('multiple isbns found', ['json' => $json]);
        }

        return new self(
            title: $json['title'] ?? '',
            description: $json['description'] ?? '',
            contributor: $json['contributor'] ?? '',
            author: $json['author'] ?? '',
            contributorNote: $json['contributor_note'] ?? '',
            price: $json['price'] ?? 0,
            ageGroup: $json['age_group'] ?? '',
            publisher: $json['publisher'] ?? '',
            primaryIsbn13: $json['isbns'][0]['isbn13'] ?? '',
            primaryIsbn10: $json['isbns'][0]['isbn10'] ?? '',
        );
    }
}
