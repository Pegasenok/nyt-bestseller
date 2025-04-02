<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::prefix('/v1')->middleware('version:v1')->group(function () {
    Route::get('/best-seller', App\Http\Controllers\v1\BestSellerController::class);
});
Route::prefix('/v2')->middleware('version:v2')->group(function () {
    Route::get('/best-seller', App\Http\Controllers\v2\BestSellerController::class);
});
