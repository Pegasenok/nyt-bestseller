<?php

namespace App\Http\Controllers\v1;

use App\DTO\BestSellerRequestDto;
use App\Http\Controllers\Controller;
use App\Http\Middleware\ApiVersion;
use App\Http\Requests\BestSellerFormRequest;
use App\Services\BestSellerInterface;
use Illuminate\Http\JsonResponse;

class BestSellerController extends Controller
{
    public function __invoke(
        BestSellerFormRequest $request,
        BestSellerInterface $bestSellerService
    ): JsonResponse {
        $results = $bestSellerService->getBestSellerResults(
            new BestSellerRequestDto(
                offset: $request->validated('offset'),
                isbn: $request->validated('isbn'),
                title: $request->validated('title'),
                author: $request->validated('author'),
            )
        );

        return response()->json(
            [
                'success' => true,
                'results' => $results,
                'version' => ApiVersion::getVersion(),
            ]
        );
    }
}
