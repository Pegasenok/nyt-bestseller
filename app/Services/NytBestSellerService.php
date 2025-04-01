<?php

namespace App\Services;

use App\DTO\BookResult;

class NytBestSellerService implements BestSellerInterface
{
    public function getBestSellerResults(): array
    {
        return [
            new BookResult(
                title: "Sample Title",
                description: "Sample Description",
                contributor: "Sample Contributor",
                author: "Sample Author",
                contributorNote: "Sample Note",
                price: 0,
                ageGroup: "Sample Age Group",
                publisher: "Sample Publisher",
                primaryIsbn13: "1234567890123",
                primaryIsbn10: "1234567890"
            )
        ];
    }
}
