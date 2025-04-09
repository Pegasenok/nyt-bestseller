<?php

namespace App\Services\v2;

use App\DTO\BestSellerRequestDto;
use App\DTO\BookResult;
use App\Http\Requests\BestSellerFormRequest;
use App\Pipeline\CachedHttpCall;
use App\Pipeline\ErrorHandlingCall;
use App\Pipeline\LimitedCall;
use App\Pipeline\NytHttpCall;
use Illuminate\Support\Facades\Pipeline;

class NytBestSellerService
{
    public function getBestSellerBooks(BestSellerFormRequest $request)
    {
        return Pipeline::send(
            new BestSellerRequestDto(
                offset: $request->validated('offset'),
                isbn: $request->validated('isbn'),
                title: $request->validated('title'),
                author: $request->validated('author'),
            ))
            ->through([
                CachedHttpCall::class,
                LimitedCall::class,
                ErrorHandlingCall::class,
                NytHttpCall::class,
            ])
            ->then(function ($response) {
                return collect($response['results'])->map(function ($resultRow) {
                    return BookResult::fromJson($resultRow);
                });
            });
    }
}
