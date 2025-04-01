<?php

namespace App\DTO;

class BookResult
{
    // todo: sample
    public function __construct(
        public string $title = "",
        public string $description = "",
        public string $contributor = "",
        public string $author = "",
        public string $contributorNote = "",
        public int $price = 0,
        public string $ageGroup = "",
        public string $publisher = "",
        public string $primaryIsbn13 = "",
        public string $primaryIsbn10 = "",
    ) {
    }
}
