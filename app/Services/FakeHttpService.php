<?php

namespace App\Services;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Promise\RejectedPromise;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class FakeHttpService
{
    public static function getFakePath(): string
    {
        return config('services.nyt.base_url').BestSellerInterface::LISTS_BEST_SELLERS_HISTORY_ENDPOINT.'?*';
    }

    public static function fakeNytBestSellerHistory(): void
    {
        Http::fake(
            [
                /** @see resources/json/best-sellers-history-offset120.json */
                FakeHttpService::getFakePath().'offset=120' => Http::response(
                    Storage::disk('resources')->get('json/best-sellers-history-offset120.json'),
                    200
                ),
                /** @see resources/json/best-sellers-history.json */
                FakeHttpService::getFakePath() => Http::response(
                    Storage::disk('resources')->get('json/best-sellers-history.json'),
                    200
                ),
            ]
        );
    }

    public static function fakeNytBestSellerBrokenStructure(): void
    {
        Http::fake(
            [
                /** @see resources/json/wrong-structure.json */
                FakeHttpService::getFakePath() => Http::response(
                    Storage::disk('resources')->get('json/wrong-structure.json'),
                    200
                ),
            ]
        );
    }

    public static function fakeNytBestSellerBrokenData(): void
    {
        Http::fake(
            [
                /** @see resources/json/wrong-data.json */
                FakeHttpService::getFakePath() => Http::response(
                    Storage::disk('resources')->get('json/wrong-data.json'),
                    200
                ),
            ]
        );
    }

    public static function fakeNytBestSellerBrokenConnection(): void
    {
        Http::fake(
            [
                FakeHttpService::getFakePath() => fn ($request) => new RejectedPromise(new ConnectException('Timeout', $request->toPsrRequest($request))),
            ]
        );
    }

    public static function fakeNytBestSellerHistoryCustom($name): void
    {
        Http::fake(
            [
                /** @see resources/json */
                FakeHttpService::getFakePath() => Http::response(
                    Storage::disk('resources')->get("json/$name"),
                    200
                ),
            ]
        );
    }
}
