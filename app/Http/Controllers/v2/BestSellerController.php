<?php

namespace App\Http\Controllers\v2;

use App\Http\Controllers\Controller;
use App\Http\Middleware\ApiVersion;
use App\Http\Requests\BestSellerFormRequest;
use App\Services\v2\NytBestSellerService;
use Illuminate\Http\JsonResponse;

class BestSellerController extends Controller
{
    public function __invoke(
        BestSellerFormRequest $request,
        NytBestSellerService $bestSellerService,
    ): JsonResponse {
        $responseData = $bestSellerService->getBestSellerBooks($request);

        return response()->json([
            'success' => true,
            'version' => ApiVersion::getVersion(),
            'results' => $responseData,
        ]);
    }
}
