<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CreditController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('credits', CreditController::class);
Route::get('users/{user}/credits', [CreditController::class, 'getUserCredits']);
Route::get('reasons/{reason}/credits', [CreditController::class, 'getByReason']);
Route::get('credits/creditable/{type}/{id}', [CreditController::class, 'getByCreditable']);
