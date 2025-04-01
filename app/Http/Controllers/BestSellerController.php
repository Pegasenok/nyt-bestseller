<?php

namespace App\Http\Controllers;

use App\Http\Requests\BestSellerRequest;
use App\Services\BestSellerInterface;
use Illuminate\Http\JsonResponse;

class BestSellerController extends Controller
{
    public function __invoke(
        BestSellerRequest $request,
        BestSellerInterface $bestSellerService
    ): JsonResponse {
        $offset = $request->integer('offset');
        $results = $bestSellerService->getBestSellerResults();

        return response()->json(['success' => true, 'offset' => $offset, 'results' => $results]);
    }
}
