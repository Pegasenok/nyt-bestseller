<?php

namespace App\Http\Controllers\v2;

use App\DTO\BestSellerRequestDto;
use App\Http\Controllers\Controller;
use App\Http\Middleware\ApiVersion;
use App\Http\Requests\BestSellerRequest;
use App\Pipeline\CachedHttpCall;
use App\Pipeline\ErrorHandlingCall;
use App\Pipeline\LimitedCall;
use App\Pipeline\NytHttpCall;
use App\Services\BestSellerInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Pipeline;

class BestSellerController extends Controller
{
    public function __invoke(
        BestSellerRequest $request,
        BestSellerInterface $bestSellerService
    ): JsonResponse {
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
            ->then(function ($data) {
                return response()->json([
                    'success' => true,
                    'version' => ApiVersion::getVersion(),
                    'results' => $data,
                ]);
            });
    }
}
