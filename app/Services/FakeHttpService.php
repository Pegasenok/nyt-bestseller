<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class FakeHttpService
{
    public static function fakeNytBestSellerHistory(): void
    {
        $fakePath = config('services.nyt.base_url').NytBestSellerService::LISTS_BEST_SELLERS_HISTORY_ENDPOINT.'?*';
        Http::fake(
            [
                $fakePath => Http::response(
                    Storage::disk('resources')->get('json/best-sellers-history.json'),
                    200
                )
            ]
        );
    }
}
