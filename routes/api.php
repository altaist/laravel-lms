<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\UserController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {
    // Addresses routes
    Route::apiResource('addresses', AddressController::class);
    
    // Companies routes
    Route::apiResource('companies', CompanyController::class);
    Route::get('companies/{company}/addresses', [CompanyController::class, 'addresses']);
    Route::get('users/{user}/addresses', [UserController::class, 'addresses']);
});
