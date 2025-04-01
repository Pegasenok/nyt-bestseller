<?php

namespace App\Http\Controllers;

use App\Http\Requests\BestSellerRequest;
use Illuminate\Http\JsonResponse;

class BestSeller extends Controller
{
    public function __invoke(BestSellerRequest $request): JsonResponse
    {
        $offset = $request->integer('offset');

        return response()->json(['success' => true, 'offset' => $offset]);
    }
}
