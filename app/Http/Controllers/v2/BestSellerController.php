<?php

namespace App\Http\Controllers\v2;

use App\Http\Controllers\Controller;
use App\Http\Middleware\ApiVersion;
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

        return response()->json(['success' => false, 'version' => ApiVersion::getVersion()]);
    }
}
